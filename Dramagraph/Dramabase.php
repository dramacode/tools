<?php
// declare(encoding = 'utf-8');
setlocale(LC_ALL, 'fr_FR.utf8');
mb_internal_encoding("UTF-8");

if (realpath($_SERVER['SCRIPT_FILENAME']) != realpath(__FILE__)); // file is include do nothing
else if (php_sapi_name() == "cli") {
  Dramabase::cli();
}
class Dramabase {
  /** Lien à une base SQLite, unique */
  public $pdo;
  /** Document XML */
  private $_dom;
  /** Processeur xpath */
  private $_xpath;
  /** Processeur xslt */
  private $_xslt;
  /** Couleurs de nœuds */
  public $ncols = array(
    "rgba(255, 0, 0, 0.7)",
    "rgba(0, 0, 255, 0.7)",
    "rgba(128, 0, 0, 0.7)",
    "rgba(0, 0, 128, 0.7)",
    "rgba(128, 0, 128, 0.7)",
  );
  /** Couleurs de liens */
  public $ecols = array(
    "rgba(255, 0, 0, 0.5)",
    "rgba(0, 0, 255, 0.5)",
    "rgba(128, 0, 0, 0.5)",
    "rgba(0, 0, 128, 0.5)",
    "rgba(128, 0, 128, 0.5)",
  );

  /**
   * Charger un fichier XML
   */
  public function __construct($sqlitefile) {
    $this->connect($sqlitefile);
  }
  /** Charger un fichier XML */
  public function load($xmlfile) {
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
  private function connect($sqlite='basedrama.sqlite') {
    $sql = 'dramabase.sql';
    $dsn = "sqlite:" . $sqlite;
    // si la base n’existe pas, la créer
    if (!file_exists($sqlite)) { 
      if (!file_exists($dir = dirname($sqlite))) {
        mkdir($dir, 0775, true);
        @chmod($dir, 0775);  // let @, if www-data is not owner but allowed to write
      }
      $this->pdo = new PDO($dsn);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      @chmod($sqlite, 0775);
      $this->pdo->exec(file_get_contents(dirname(__FILE__).'/'.$sql));
    }
    else {
      $this->pdo = new PDO($dsn);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
    // table temporaire en mémoire
    $this->pdo->exec("PRAGMA temp_store = 2;");
  }
  public function gephi($filename) {
    $data = $this->nodes($filename);
    $f = $filename.'-nodes.csv';
    $w = fopen($f, 'w');
    for ($i=0; $i<count($data); $i++) {
      fwrite($w, implode("\t", $data[$i])."\n");
    }
    fclose($w);
    echo $f.'  ';
    $data = $this->edges($filename);
    $f = $filename.'-edges.csv';
    $w = fopen($f, 'w');
    for ($i=0; $i<count($data); $i++) {
      fwrite($w, implode("\t", $data[$i])."\n");
    }
    fclose($w);
    echo $f."\n";
  }
  public function sigma($play) {
    $color = array();
    echo "var g={ nodes: [\n";
    $data = $this->nodes($play);
    $count = count($data) - 1;
    for ($i=1; $i<count($data); $i++) {
      // ne pas mettre les personnages non liés, écarte topr le réseau
      if (!$data[$i][2]) continue;
      $col = "";
      /*
      // position initiale en cercle
      $angle =  (2*M_PI/$count) * (($i -1)*($count/3.8)); // cercle rempli tous les quart
      // $angle =  2*M_PI/$count * ($i -1);
      $x = cos($angle);
      $y = sin($angle);
      */
      // position initiale en ligne
      // $x = $i ; 
      $y = 1;
      // $x = -$i*(1-2*($i%2));
      $x=$i;
      if (isset($this->ncols[$i-1])) {
        $color[$data[$i][0]] = $this->ecols[$i-1];
        $col = ", color: '".$this->ncols[$i-1]."'";
      }
      echo "  {id:'".$data[$i][0]."', label:".json_encode($data[$i][1]).", size:".(0+$data[$i][2]).", x: $x, y: $y".$col."}";
      if ($i+1<count($data)) echo ',';
      echo "\n";
    }
    echo "], edges: [\n";
    $data = $this->edges($play);
    for ($i=count($data)-1; $i>0; $i--) {
      $col = "";
      if (isset($color[$data[$i][0]])) $col = ", color: '".$color[$data[$i][0]]."'";
      echo "  {id:'e".$i."', source:'".$data[$i][0]."', target:'".$data[$i][1]."', size:".$data[$i][2].$col.", type:'curvedArrow'}";
      echo ',';
      echo "\n";
    }
    echo "]};\n";
  }
  /**
   * proprotion de parole par personnage
   */
  public function nodes($play) {
    $play = $this->pdo->quote($play);
    $data = array();
    $data[] = array('Id', 'Label', 'c', 'targets', 'color');
    foreach ($this->pdo->query("SELECT * FROM role WHERE play = $play ORDER BY c DESC") as $row) {
      if (!$row['love']) $color = '#CCCCCC';
      else if ($row['sex'] == 1 && $row['age'] == 'junior') $color = "#00FFFF";
      else if ($row['sex'] == 1 && $row['age'] == 'veteran') $color = "#0000FF";
      else if ($row['sex'] == 1) $color = "#4080FF";
      else if ($row['sex'] == 2 && $row['age'] == 'junior') $color = "#FFAAFF";
      else if ($row['sex'] == 2 && $row['age'] == 'veteran') $color = "#800000";
      else if ($row['sex'] == 2) $color = "#FF0000";
      else $color = '#CCCCCC';
      $data[] = array(
        $row['code'],
        $row['label'],
        $row['c'],
        $row['targets'],
        $color,
      );
    }
    return $data;
  }
  /**
   * Évolution de la parole selon les personnages
   */
  public function edges($play) {
    $q = $this->pdo->prepare("SELECT source, target, sum(c) AS ord FROM sp WHERE play = ? GROUP BY source, target ORDER BY ord DESC");
    $q->execute(array($play));
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
    $time = microtime(true);
    echo $file;
    $this->load($file);
    $playcode = pathinfo($file, PATHINFO_FILENAME);
    // supprimer la pièce, des triggers doivent normalement supprimer la cascade.
    $this->pdo->exec("DELETE FROM play WHERE code = ".$this->pdo->quote($playcode));
    // métadonnées de pièces
    $year = null;
    $verse = null;
    $author = $this->_xpath->query("/*/tei:teiHeader//tei:author");
    if ($author->length) $author = $author->item(0)->textContent;
    else $author = null;
    $title = $this->_xpath->query("/*/tei:teiHeader//tei:title");
    if ($title->length) $title = $title->item(0)->textContent;
    else $title = null;
    $genre = $this->_xpath->evaluate("/*/tei:teiHeader//tei:term[@type='genre']/@subtype");
    if ($genre->length) $genre = $genre->item(0)->nodeValue;
    else $genre = null;
    preg_match('@^([^_]+)_([0-9]+)?@i', $playcode, $matches);
    $year = @$matches[2];
    $acts = $this->_xpath->evaluate("count(/*/tei:text/tei:body//tei:*[@type='act'])");
    if (!$acts) $acts = $this->_xpath->evaluate("count(/*/tei:text/tei:body/*[tei:div|tei:div2])");
    if (!$acts) $acts = 1;
    $l = $this->_xpath->evaluate("count(//tei:sp/tei:l)");
    $p = $this->_xpath->evaluate("count(//tei:sp/tei:p)");
    if ($l > 2*$p) $verse = true;
    else if ($p > 2*$l) $verse = false;
    $q = $this->pdo->prepare("
    INSERT INTO play (code, author, title, year, acts, verse, genre)
              VALUES (?,    ?,      ?,     ?,    ?,    ?,     ?);
    ");
    $q->execute(array(
      $playcode,
      $author,
      $title,
      $year,
      $acts,
      $verse,
      $genre,
    ));
    // ditribution
    $this->pdo->beginTransaction();
    $q = $this->pdo->prepare("
    INSERT INTO role (play, code, label, rend, sex, age, love)
              VALUES (?,    ?,      ?,   ?,    ?,   ?,   ?);
    ");
    $nodes = $this->_xpath->query("//tei:role");
    $castlist = array();
    foreach ($nodes as $n) {
      $code = $n->getAttribute ('xml:id');
      $castlist[$code] = true;
      $label = $n->nodeValue;
      $rend = ' '.$n->getAttribute ('rend').' '; // espace séparateur
      if (preg_match('@ female @i', $rend)) $sex = 2;
      else if (preg_match('@ male @i', $rend)) $sex = 1;
      else $sex = null;
      preg_match('@ (cadet|junior|senior|veteran) @i', $rend, $matches);
      $age = @$matches[1];
      $love = preg_match('@ love @i', $rend);
      $q->execute(array(
        $playcode,
        $code,
        $label,
        $rend,
        $sex,
        $age,
        $love,
      ));
    }
    $this->pdo->commit();
    echo " play+role: ".number_format(microtime(true) - $time, 3)."s. ";
    $time = microtime(true);
    $xsl = new DOMDocument("1.0", "UTF-8");
    $xsl->load(dirname(__FILE__).'/drama2csv.xsl');
    $this->_xslt->importStyleSheet($xsl);
    $this->_xslt->setParameter('', 'filename', $playcode);
    // paramètres ?
    $csv = $this->_xslt->transformToXML($this->_dom);
    // placer la chaîne dans un stream pour profiter du parseur fgetscsv
    $stream = fopen('php://memory', 'w+');
    fwrite($stream, $csv);
    rewind($stream);
    $q = $this->pdo->prepare("
    INSERT INTO sp (play, act, scene, code, source, target, l, w, c, text)
            VALUES (?,    ?,   ?,     ?,    ?,      ?,      ?, ?, ?, ?);
    ");
    // première ligne 
    $data = fgetcsv($stream, 0, "\t");
    $this->pdo->beginTransaction();
    // boucler pour charger la base
    while (($data = fgetcsv($stream, 0, "\t")) !== FALSE) {
      if (!isset($castlist[$data[4]]) && STDERR) fwrite(STDERR, "@who ERROR ".$data[4]. " [".$data[3]."]\n");
      try {
        $q->execute(array(
          $playcode,
          $data[1], // act
          $data[2], // scene
          $data[3], // code
          $data[4], // source
          $data[5], // target
          $data[6], // l
          $data[7], // w
          $data[8], // c
          $data[9], // text
        ));
      }
      catch (Exception $e) {
        echo "\n\n      NOT UNIQUE ? ".$data[3]."\n".$e;
      }
    }
    $this->pdo->commit();
    // différentes stats prédef
    $play = $this->pdo->quote($playcode);
    echo " sp: ".number_format(microtime(true) - $time, 3)."s. ";
    $time = microtime(true);
    $this->pdo->beginTransaction();
    $this->pdo->exec("
    UPDATE play SET sp = (SELECT COUNT(*) FROM sp WHERE play = $play) WHERE code = $play;
    UPDATE play SET l = (SELECT SUM(l) FROM sp WHERE play = $play) WHERE code = $play;
    UPDATE play SET w = (SELECT SUM(w) FROM sp WHERE play = $play) WHERE code = $play;
    UPDATE play SET c = (SELECT SUM(c) FROM sp WHERE play = $play) WHERE code = $play;
    INSERT INTO scene (play, act, code, sp) SELECT play, act, scene, count(*) FROM sp WHERE play = $play GROUP BY scene;
    UPDATE scene SET l = (SELECT SUM(l) FROM sp WHERE play = $play AND sp.scene = scene.code) WHERE play = $play;
    UPDATE scene SET w = (SELECT SUM(w) FROM sp WHERE play = $play AND sp.scene = scene.code) WHERE play = $play;
    UPDATE scene SET c = (SELECT SUM(c) FROM sp WHERE play = $play AND sp.scene = scene.code) WHERE play = $play;
    UPDATE role SET targets = (SELECT COUNT(DISTINCT target) FROM sp WHERE play = $play AND source = role.code) WHERE play = $play;
    UPDATE role SET sp = (SELECT COUNT(*) FROM sp WHERE play = $play AND sp.source = role.code) WHERE play = $play;
    UPDATE role SET l = (SELECT SUM(l) FROM sp WHERE play = $play AND sp.source = role.code) WHERE play = $play;
    UPDATE role SET w = (SELECT SUM(w) FROM sp WHERE play = $play AND sp.source = role.code) WHERE play = $play;
    UPDATE role SET c = (SELECT SUM(c) FROM sp WHERE play = $play AND sp.source = role.code) WHERE play = $play;
    ");
    $this->pdo->commit();
    echo " stats: ".number_format(microtime(true) - $time, 3)."s.\n";
  }
  /**
   * Collecter les identifiants dans les <role>
   * Alerter sur les identifiants inconnus
   */
  public function valid($file) {
    $this->load($file);
    $nodes = $this->_xpath->query("//tei:role/@xml:id");
    $castlist = array();
    foreach ($nodes as $n) {
      $castlist[$n->nodeValue] = true;
    }
    $nodes = $this->_xpath->query("//@who");
    foreach ($nodes as $n) {
      $who = $n->nodeValue;
      if (isset($castlist[$who])) continue;
      if (STDERR) fwrite(STDERR, $who.' l. '.$n->getLineNo()."\n");
    }
  }
  /**
   * Command line API 
   */
  static function cli() {
    $timeStart = microtime(true);
    $usage = '
    usage    : php -f '.basename(__FILE__).' base.sqlite {action} {arguments}
    where action can be
    valid  ../*.xml
    insert ../*.xml
    gephi playcode 
';
    $timeStart = microtime(true);
    array_shift($_SERVER['argv']); // shift first arg, the script filepath
    if (!count($_SERVER['argv'])) exit($usage);
    $sqlite = array_shift($_SERVER['argv']);
    $dramabase = new Dramabase($sqlite);
    if (!count($_SERVER['argv'])) exit('
    action  ? (valid|insert|gephi)
');
    $action = array_shift($_SERVER['argv']);
    if ($action == 'insert') {
      if (!count($_SERVER['argv'])) exit('
    insert requires a file or a glob expression to insert XML/TEI play file
');
      $file = array_shift($_SERVER['argv']);
      if (file_exists($file)) {
        $dramabase->insert($file);
      }
      // TODO, glob
    }
    if ($action == 'gephi') {
      $dramabase->gephi(array_shift($_SERVER['argv']));
    }
  }
}
?>