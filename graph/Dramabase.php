<?php
// declare(encoding = 'utf-8');
setlocale(LC_ALL, 'fr_FR.utf8');
mb_internal_encoding("UTF-8");

if (realpath($_SERVER['SCRIPT_FILENAME']) != realpath(__FILE__)); // file is include do nothing
else if (php_sapi_name() == "cli") {
  Dramagraph::cli();
}
class Dramagraph {
  /** Lien à une base SQLite, unique */
  static $pdo;
  /** Nom du fichier chargé */
  private $_filename;
  /** Document XML */
  private $_dom;
  /** Processeur xpath */
  private $_xpath;
  /** Processeur xslt */
  private $_xslt;
  /** Liste des identifiants @who */
  private $_castlist = array();

  /**
   * Charger un fichier XML
   */
  public function __construct($sqlitefile) {
    self::connect($sqlitefile);
  }
  /** Charger un fichier XML */
  public function load($xmlfile) {
    $this->_filename = pathinfo($xmlfile, PATHINFO_FILENAME);
    $this->_dom = new DOMDocument();
    $this->_dom->preserveWhiteSpace = false;
    $this->_dom->formatOutput=true;
    $this->_dom->substituteEntities=true;
    $this->_dom->load($xmlfile, LIBXML_NOENT | LIBXML_NONET | LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING);
    $this->_xpath = new DOMXpath($this->_dom);
    $this->_xpath->registerNamespace('tei', "http://www.tei-c.org/ns/1.0");
    $this->_xslt = new XSLTProcessor();

  }
  /** Connexion à la base */
  private static function connect($sqlite='basedrama.sqlite') {
    $sql = 'dramagraph.sql';
    $dsn = "sqlite:" . $sqlite;
    // si la base n’existe pas, la créer
    if (!file_exists($sqlite)) { 
      if (!file_exists($dir = dirname($sqlite))) {
        mkdir($dir, 0775, true);
        @chmod($dir, 0775);  // let @, if www-data is not owner but allowed to write
      }
      self::$pdo = new PDO($dsn);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
      @chmod($sqlite, 0775);
      self::$pdo->exec(file_get_contents(dirname(__FILE__).'/'.$sql));
    }
    else {
      self::$pdo = new PDO($dsn);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    }
    // table temporaire en mémoire
    self::$pdo->exec("PRAGMA temp_store = 2;");
  }
  public function csv($filename) {
    $data = $this->nodes($filename);
    $w = fopen($filename.'-nodes.csv', 'w');
    for ($i=0; $i<count($data); $i++) {
      fwrite($w, implode("\t", $data[$i])."\n");
    }
    fclose($w);
    $data = $this->edges($filename);
    $w = fopen($filename.'-edges.csv', 'w');
    for ($i=0; $i<count($data); $i++) {
      fwrite($w, implode("\t", $data[$i])."\n");
    }
    fclose($w);
  }
  public function json($filename) {
    echo "var g={ nodes: [\n";
    $data = $this->nodes($filename);
    for ($i=1; $i<count($data); $i++) {
      echo "  {id:'".$data[$i][0]."', label:'".$data[$i][1]."', size:".$data[$i][2]."}";
      if ($i+1<count($data)) echo ',';
      echo "\n";
    }
    echo "], edges: [\n";
    $data = $this->edges($filename);
    for ($i=1; $i<count($data); $i++) {
      echo "  {id:'e".$i."', source:'".$data[$i][0]."', target:'".$data[$i][1]."', size:".$data[$i][2]."}";
      if ($i+1<count($data)) echo ',';
      echo "\n";
    }
    echo "]};\n";
  }
  /**
   * proprotion de parole par personnage
   */
  public function nodes($filename) {
    // impossible to prepare query with TEMP table, use the internal quote funtion to avoid SQL injection
    $filename = self::$pdo->quote($filename);
    $data = array();
    $data[] = array('Id', 'Label', 'said', 'heard', 'presence', 'targets');
    // total des caractères pour la pièce
    list($total) = self::$pdo->query("SELECT sum(chars) FROM sp WHERE filename = $filename ")->fetch();
    // self::$pdo->beginTransaction();
    self::$pdo->exec("CREATE TEMP TABLE node (Id, Label, said, heard, presence, degree, sources, targets)");
    self::$pdo->exec("INSERT INTO node(Id, Label, said) SELECT source, role, sum(chars) FROM sp WHERE filename = $filename GROUP BY source");
    self::$pdo->exec("UPDATE node SET heard = (SELECT sum(chars) FROM sp WHERE filename = $filename AND target = node.Id)");
    self::$pdo->exec("UPDATE node SET presence = (SELECT sum(chars) FROM sp WHERE filename = $filename AND scene IN (SELECT DISTINCT scene FROM sp WHERE filename = $filename AND source = node.Id))");
    self::$pdo->exec("UPDATE node SET targets = (SELECT COUNT(DISTINCT target) FROM sp WHERE filename = $filename AND source = node.Id)");
    self::$pdo->exec("UPDATE node SET sources = (SELECT COUNT(DISTINCT source) FROM sp WHERE filename = $filename AND target = node.Id)");
    self::$pdo->exec("UPDATE node SET degree = targets + sources");
    foreach (self::$pdo->query("SELECT * FROM node ORDER BY degree DESC") as $row) {
      $data[] = array(
        $row['Id'],
        $row['Label'],
        $row['said'],
        $row['heard'],
        $row['presence'],
        $row['targets'],
        // $row['sources'],
        // $row['degree'],
      );
    }
    self::$pdo->exec("DROP TABLE node");
    // self::$pdo->commit();
    return $data;
  }
  /**
   * Évolution de la parole selon les personnages
   */
  public function edges($filename) {
    $q = self::$pdo->prepare("SELECT source, target, sum(chars) AS ord FROM sp WHERE filename = ? GROUP BY source, target ORDER BY ord DESC");
    $q->execute(array($filename));
    $data = array();
    $data[] = array('Source', 'Target', 'Weight');
    while ($row = $q->fetch()) {
      $data[] = array(
        $row['source'],
        $row['target'],
        $row['ord'],
      );
    }
    return $data;
  }
  /**
   * Charger un csv en base
   */
  public function insert($file) {
    $this->load($file);
    $xsl = new DOMDocument("1.0", "UTF-8");
    $xsl->load(dirname(__FILE__).'/drama2csv.xsl');
    $this->_xslt->importStyleSheet($xsl);
    $this->_xslt->setParameter('', 'filename', $this->_filename);
    // paramètres ?
    $csv = $this->_xslt->transformToXML($this->_dom);
    // echo $csv;
    // placer la chaîne dans un stream pour profiter du parseur fgetscsv
    $stream = fopen('php://memory', 'w+');
    fwrite($stream, $csv);
    rewind($stream);
    // supprimer dans la base ce qui concerne ce fichier
    $q = self::$pdo->prepare("DELETE FROM sp WHERE filename = ?");
    $q->execute(array($this->_filename));
    // filename	act	scene	sp	who	role	verses	words	chars	text
    $q = self::$pdo->prepare("
    INSERT INTO sp (filename, act, scene, sp, role, source, target, verses, words, chars, text)
            VALUES (?,        ?,   ?,     ?,  ?,    ?,      ?,      ?,      ?,     ?,     ?);
    ");
    // première ligne 
    $data = fgetcsv($stream, 0, "\t");
    self::$pdo->beginTransaction();
    // boucler pour charger la base
    while (($data = fgetcsv($stream, 0, "\t")) !== FALSE) {
      echo $data[3]."\n";
      $q->execute(array(
        $this->_filename,
        $data[1],
        $data[2],
        $data[3],
        $data[4],
        $data[5],
        $data[6],
        $data[7],
        $data[8],
        $data[9],
        $data[10],
      ));
    }
    self::$pdo->commit();
  }
  /**
   * Collecter les identifiants dans les <role>
   * Alerter sur les iddentifiants inconnus
   */
  public function valid($file) {
    $this->load($file);
    $nodes = $this->_xpath->query("//tei:role/@xml:id");
    foreach ($nodes as $n) {
      $_castlist[$n->nodeValue] = true;
    }
    $nodes = $this->_xpath->query("//@who");
    foreach ($nodes as $n) {
      $who = $n->nodeValue;
      if (isset($_castlist[$who])) continue;
      if (STDERR) fwrite(STDERR, $who.' l. '.$n->getLineNo()."\n");
    }
  }
  /**
   * Command line API 
   */
  static function cli() {
    $timeStart = microtime(true);
    array_shift($_SERVER['argv']); // shift first arg, the script filepath
    if (!count($_SERVER['argv'])) exit('
    usage    : php -f Dramapase.php *.xml  destdir/?
');
     // $drama = new Dramagraph(dirname(__FILE__).'/basedrama.sqlite');
// vérifier la validité des @who
// $drama->insert("racine_1677_phedre.xml");
// $drama->csv("racine_1677_phedre");
// $drama->insert();
// $drama->json("moliere_1664_tartuffe");
   }
}
?>