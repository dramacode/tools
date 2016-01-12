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
    "#FF4C4C",
    "#A64CA6",
    "#4C4CFF",
     "#4c4ca6",
    "#A6A6A6",
  );


  /** Couleurs de liens */
  public $ecols = array(
    "rgba(255, 0, 0, 0.5)",
    "rgba(128, 0, 128, 0.5)",
    "rgba(0, 0, 255, 0.5)",
    "rgba(0, 0, 128, 0.5)",
    "rgba(128, 128, 128, 0.5)",
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
    echo "{ nodes: [\n";
    $data = $this->nodes($play);
    // 'Id', 'Label', 'c', 'title', 'targets', 'color'
    $count = count($data) - 1;
    for ($i=1; $i<count($data); $i++) {
      // ne pas mettre les personnages non liés, écarte topr le réseau
      if (!$data[$i][4]) continue;
      $col = "";
      // position initiale en cercle
      $angle =  -M_PI/2+(2*M_PI/$count) * ($i -1); // cercle rempli tous les quart
      // $angle =  2*M_PI/$count * ($i -1);
      $x =  number_format(cos($angle)*6, 4);
      $y =  number_format(sin($angle)*6, 4);
      /*
      // position initiale en ligne
      // $x = $i ; 
      $y = 1;
      // $x = -$i*(1-2*($i%2));
      $x=$i;
      */
      if (isset($this->ncols[$i-1])) {
        $color[$data[$i][0]] = $this->ecols[$i-1];
        $col = ", color: '".$this->ncols[$i-1]."'";
      }
      $json_options = JSON_UNESCAPED_UNICODE;
      echo "  {id:'".$data[$i][0]."', label:".json_encode($data[$i][1],  $json_options).", size:".(0+$data[$i][2]).", x: $x, y: $y".$col.", title: ".json_encode($data[$i][3],  $json_options)."}";
      if ($i+1<count($data)) echo ',';
      echo "\n";
    }
    echo "], edges: [\n";
    $data = $this->edges($play);
    for ($i=count($data)-1; $i>0; $i--) {
      $col = "";
      if (isset($color[$data[$i][0]])) $col = ", color: '".$color[$data[$i][0]]."'";
      // bigger less opacity ?
      // number_format(0.5+(1-$data[$i][3])*0.5, 1)
      // $col=", color: 'rgba(128, 128, 128, 0.5)'";
      echo "  {id:'e".$i."', source:'".$data[$i][0]."', target:'".$data[$i][1]."', size:".$data[$i][2].$col.", type:'drama'}";
      echo ',';
      echo "\n";
    }
    echo "]};\n";
  }
  /**
   * Ligne bibliographique pour une pièce
   */
  public function bibl($play) {
    if (is_string($play)) {
      $playcode = $this->pdo->quote($playcode);
      $play = $this->pdo->query("SELECT * FROM play WHERE code = $playcode")->fetch();
    }
    $bibl = $play['author'].', '.$play['title'].' ('.$play['year'];
    if ($play['genre'] == 'tragedy') $bibl .= ', tragédie';
    else if ($play['genre'] == 'comedy') $bibl .= ', comédie';
    $bibl .= ', '.$play['acts'].(($play['acts']>2)?" actes":" acte");
    $bibl .= ', '.(($play['verse'])?"vers":"prose");
    $bibl .= ')';
    return $bibl;
  }
  /**
   * Propotion des rôles
   */
  public function rolerate($playcode, $max=1200) {
    echo '
<style>
.rolerate { font-family: sans-serif; font-size: 15px; border-spacing: 0; border-collapse: collapse; }
.rolerate, .rolerate * { box-sizing: border-box; }
.rolerate th { text-align: right; }
.rolerate .role { overflow: hidden; background-color: rgba(192, 192, 192, 0.7); color: rgba(0, 0, 0, 0.5); font-stretch: ultra-condensed; float: left; height: 2em; }
.rolerate .role1 { background-color: rgba(255, 0, 0, 0.5); border-bottom: none; color: rgba(255, 255, 255, 1);}
.rolerate .role2 { background-color: rgba(128, 0, 128, 0.5); border-bottom: none; color: rgba(255, 255, 255, 1);}
.rolerate .role3 { background-color: rgba(0, 0, 255, 0.5); border-bottom: none; color:  rgba(255, 255, 255, 1);}
.rolerate .role4 { background-color: rgba(0, 0, 128, 0.5); border-bottom: none; color:  rgba(255, 255, 255, 1);}
.rolerate .role5 { background-color: rgba(128, 128, 128, 0.5); border-bottom: none; color: rgba(255, 255, 255, 1); }
</style>
    ';
    if(!$max) $max=1000;
    $playcode = $this->pdo->quote($playcode);
    $play = $this->pdo->query("SELECT * FROM play WHERE code = $playcode")->fetch();
    $playwidth = $play['c'] / (100000/$max);
    echo  '<table class="rolerate">'."\n";
    $dist = array();
    foreach ($this->pdo->query("SELECT * FROM role WHERE play = $playcode ORDER BY c DESC") as $role) {
      $dist[$role['code']] = array(
        'label'=>$role['label'], 
        'sp' => $role['sp'], 
        'w' => $role['w'], 
        'c' => $role['c']
      );
    }
    foreach (array('c', 'w', 'sp') as $unit) {
      echo "<tr><th>";
      if ($unit=='c') echo "Caractères";
      else if ($unit=='w') echo "Mots";
      if ($unit=='sp') echo "Répliques";
      echo "</th><td>";
      $i=1;
      foreach ($dist as $code=>$stats) {
        $width = round($playwidth*$stats[$unit]/$play[$unit]);
        echo '<span class="role role'.$i.'" title="'.$stats['label'].' " style="width: '.$width.'px"> '.$stats['label'].' </span>';
        $i++;
      }
      echo "</td></tr>";
    }
    echo '</table>';
  }
  /**
   * Chiffres par rôle
   */
  /**
   * Panneau vertical de pièce
   */
  public function timepanel($playcode, $max=800) {
    
    echo '
<style>
.timepanel { width: 100%; font-family: sans-serif; font-size: 13px; line-height: 1.2em; zoom:1; }
.timepanel, .timepanel * { box-sizing: border-box; }
.timepanel .acthead { display: block; text-align: right; padding: 1ex 1em 2px 1em;  }
.timepanel:after, .timepanel:after { content:""; display:table; }
.timepanel:after { clear:both; }
.timepanel a  { display: block; border-bottom: none; }
.timepanel a:hover  { opacity: 1; }
.timepanel .role { float: left; height: 100%; background-color: rgba(192, 192, 192, 0.7); border-radius: 3px/0.5em; border-bottom: 1px solid #FFFFFF; color: rgba(0, 0, 0, 0.5); font-stretch: ultra-condensed; }
.timepanel .role span { overflow: hidden; padding-left: 1ex; padding-top: 2px;}
.timepanel .role1 { background-color: rgba(255, 0, 0, 0.5); border-bottom: none; color: rgba(255, 255, 255, 1);}
.timepanel .role2 { background-color: rgba(128, 0, 128, 0.5); border-bottom: none; color: rgba(255, 255, 255, 1);}
.timepanel .role3 { background-color: rgba(0, 0, 255, 0.5); border-bottom: none; color:  rgba(255, 255, 255, 1);}
.timepanel .role4 { background-color: rgba(0, 0, 128, 0.5); border-bottom: none; color:  rgba(255, 255, 255, 1);}
.timepanel .role5 { background-color: rgba(128, 128, 128, 0.5); border-bottom: none; color: rgba(255, 255, 255, 1); }
</style>
    ';
    $playcode = $this->pdo->quote($playcode);
    $play = $this->pdo->query("SELECT * FROM play WHERE code = $playcode")->fetch();
    // 1 pixel = 1000 caractères
    if (!$max) $playheight = '800';
    else if (is_numeric($max) && $max > 50) $playheight = round($play['c'] / (100000/$max));
    else $playheight = '800';
    
    
    // requête sur le nombre de caractère d’un rôle dans une scène
    $qsp = $this->pdo->prepare("SELECT sum(c) FROM sp WHERE play = $playcode AND scene = ? AND source = ?");
    echo '<div class="timepanel">'."\n";
    foreach ($this->pdo->query("SELECT * FROM act WHERE play = $playcode") as $act) {
      echo '    <a href="#'.$act['code'].'" class="acthead">Acte '.$act['code']."</a>\n";
      echo '    <div class="act" style="height: '.(ceil($playheight * $act['c']/$play['c'])).'px">'."\n";
      foreach ($this->pdo->query("SELECT * FROM scene WHERE play = $playcode AND act = ".$this->pdo->quote($act['code'])) as $scene) {
        $sceneheight = number_format(99*($scene['c']/$act['c']), 1);
        if (!isset($scene['n'])) $scene['n'] = 0+ preg_replace('/\D/', '', $scene['code']);
        echo '  <a href="#'.$scene['code'].'" class="scene" style="height: '.$sceneheight.'%;" title="Acte '.$scene['act'].', scène '.$scene['n'].'">'."\n";
        $i = 0;
        foreach ($this->pdo->query("SELECT * FROM role WHERE play = $playcode ORDER BY c DESC") as $role) {
          $qsp->execute(array($scene['code'], $role['code']));
          list($c) = $qsp->fetch();
          $i++;
          if (!$c) continue;
          $width = number_format(99*$c / $scene['c']);
          echo '<span class="role role'.$i.'"';
          echo ' style="width: '.$width.'%"';
          echo ' title="'.$role['label'].', acte '.$scene['act'].', scène '.$scene['n'].', '.round(100*$c / $scene['c']).'%"';
          echo '>';
          if ($width > 30 && ($playheight * $scene['c']/$play['c']) > 15 ) { // && !isset($list[$role['code']])
            echo '<span>'.$role['label'].'</span>';
            $list[$role['code']] = true;
          }
          else echo ' ';
          echo '</span>';
        }
        echo "  </a>\n";
      }
      echo '    </div>';
    }
    
    /*

    */
    echo "\n</div>\n";
  }

  public function timebars($playcode, $max=null) {
    
    echo '
<style>
.timebars, .timebars * { box-sizing: border-box; }
.timebars:after, .timebars:after { content:""; display:table; }
.timebars:after { clear:both; }
.timebars { zoom:1; margin-top: 1.5em; }
.timebars { height: 200px; font-family: sans-serif;  }
.timebars .act { position: absolute; margin-top: -1.1em;  border-left: 3px #000 solid; padding-left: 1ex; margin-bottom: 1em; white-space: nowrap;}
.timebars .scene { float: left; height: 100%;}
.timebars .scene1 {  }
.timebars .role { background-color: rgba(192, 192, 192, 0.7); border-radius: 3px/0.5em; border-bottom: 1px solid #FFFFFF; color: rgba(0, 0, 0, 0.5); font-stretch: ultra-condensed; font-size: 13px; }
.timebars .role1 { background-color: rgba(255, 0, 0, 0.7); border-bottom: none; color: rgba(255, 255, 255, 0.7);}
.timebars .role2 { background-color: rgba(128, 0, 128, 0.7); border-bottom: none; color: rgba(255, 255, 255, 0.7);}
.timebars .role3 { background-color: rgba(0, 0, 255, 0.7); border-bottom: none; color: rgba(255, 255, 255, 0.7);}
.timebars .role4 { background-color: rgba(0, 0, 128, 0.7); border-bottom: none; color: rgba(255, 255, 255, 0.7);}
.timebars .role5 { background-color: rgba(128, 128, 128, 0.7); border-bottom: none; color: rgba(255, 255, 255, 0.7); }
</style>
    ';
    $playcode = $this->pdo->quote($playcode);
    $play = $this->pdo->query("SELECT * FROM play WHERE code = $playcode")->fetch();
    // 1 pixel = 1000 caractères
    if (!$max) $width = 'auto';
    else if (is_numeric($max) && $max > 50) $width = round($play['c'] / (100000/$max)).'px';
    else $width = "auto";
    
    
    // requête sur le nombre de caractère d’un rôle dans une scène
    $qsp = $this->pdo->prepare("SELECT sum(c) FROM sp WHERE play = $playcode AND scene = ? AND source = ?");
    
    /*
    echo '
<div class="timebars" style="width: '.$width.'">
    ';   
    foreach ($this->pdo->query("SELECT * FROM scene WHERE play = $playcode") as $scene) {
      
    }
    echo '
</div>
    ';
    */
    
    echo '
<div class="timebars" style="width: '.$width.'">
    ';
    $actlast = null;
    $list = array();
    foreach ($this->pdo->query("SELECT * FROM scene WHERE play = $playcode") as $scene) {
      $class = '';
      if ($actlast != $scene['act']) $class .= " scene1";
      
      $width = number_format(99*($scene['c']/$play['c']), 1);
      if (!isset($scene['n'])) $scene['n'] = 0+ preg_replace('/\D/', '', $scene['code']);
      echo '  <div class="scene'.$class.'" style="width: '.$width.'%;" title="Acte '.$scene['act'].', scène '.$scene['n'].'">'."\n";
      if ($actlast != $scene['act']) echo '    <b class="act">Acte '.$scene['act'].'</b>';
      $actlast = $scene['act'];
      // boucle sur les rôles en ordre d’importance
      $i = 0;
      foreach ($this->pdo->query("SELECT * FROM role WHERE play = $playcode ORDER BY c DESC") as $role) {
        $qsp->execute(array($scene['code'], $role['code']));
        list($c) = $qsp->fetch();
        $i++;
        if (!$c) continue;
        $height = number_format(100*$c / $scene['c']);
        echo '<div class="role role'.$i.'"';
        echo ' style="height: '.$height.'%"';
        echo ' title="'.$role['label'].', acte '.$scene['act'].', scène '.$scene['n'].', '.round(100*$c / $scene['c']).'%"';
        echo '>';
        if ($width > 5.5 && $height > 15 ) { // && !isset($list[$role['code']])
          echo '<span> '.$role['label'].'</span>';
          $list[$role['code']] = true;
        }
        else echo ' ';
        echo '</div>';
      }
      echo "  </div>\n";
    }
    echo '
</div>
    ';
  }

  /**
   * Table 
   */
  public function timetable($playcode, $max=null) {
    $playcode = $this->pdo->quote($playcode);
    $play = $this->pdo->query("SELECT * FROM play WHERE code = $playcode")->fetch();
    // 1 pixel = 1000 caractères
    if (!$max) $width = '';
    if (is_numeric($max) && $max > 50) $width = ' width="'.round($play['c'] / (100000/$max)).'"';
    else $width = ' width="'.$max.'"';
    
    echo '
<table class="timetable" '.$width.'>
  <caption>'.($this->bibl($play)).'</caption>
';
    // timeline des scènes
    $actlast = null;
    echo '<thead>
  <tr class="scenes">
';
    // attention les pourcentages de la largeur sont comptés sans les noms de personnages
    foreach ($this->pdo->query("SELECT * FROM scene WHERE play = $playcode") as $scene) {
      $class = ' scene';
      if ($actlast != $scene['act']) $class .= " scene1";
      $actlast = $scene['act'];
      $width = number_format(100*($scene['c']/$play['c']), 1);
      $n = 0+ preg_replace('/\D/', '', $scene['code']);
      echo '    <td class="'.$class.'" style="width: '.$width.'%;" title="Acte '.$scene['act'].', scène '.$n.'"/>'."\n";
    }
    echo "  </tr>
  </thead>
";
    
    // requête sur le nombre de caractère d’un rôle dans une scène
    $qsp = $this->pdo->prepare("SELECT sum(c) FROM sp WHERE play = $playcode AND scene = ? AND source = ?");
    // Boucler sur les personnages, un par ligne
    foreach ($this->pdo->query("SELECT * FROM role WHERE play = $playcode ORDER BY c DESC") as $role) {
      echo '  <tr class="'.$role['code'].'">'."\n";
      // boucler sur les scènes
      $label = $role['label'];
      foreach ($this->pdo->query("SELECT * FROM scene WHERE play = $playcode") as $scene) {
        $class = "";
        if ($actlast != $scene['act']) $class .= " scene1";
        $actlast = $scene['act'];
        $qsp->execute(array($scene['code'], $role['code']));
        list($c) = $qsp->fetch();
        $opacity = number_format($c / $scene['c'], 1);
        if (trim($class)) $class = ' class="'.trim($class).'"';
        $n = 0+ preg_replace('/\D/', '', $scene['code']);
        echo '<td'.$class.' style="opacity: '.$opacity.'" title="'.$label.', acte '.$scene['act'].', scène '.$n.', '.round(100*$c / $scene['c']).'%"';
        if (!$c) echo "/>\n";
        else echo "> </td>\n";
      }
      $title='';
      if ($role['title']) $title .= $role['title'].', '.round(100*$role['c'] / $play['c']).'%';
      echo '<th style="position: absolute; " title="'.$title.'">'.$label.'</td>';
      echo '  </tr>'."\n";
    }
    echo '
</table>
';
  }
  /**
   * proprotion de parole par personnage
   */
  public function nodes($play) {
    $play = $this->pdo->quote($play);
    $data = array();
    $data[] = array('Id', 'Label', 'c', 'title', 'targets', 'color');
    foreach ($this->pdo->query("SELECT * FROM role WHERE play = $play ORDER BY c DESC") as $role) {
      if (!$role['love']) $color = '#CCCCCC';
      else if ($role['sex'] == 1 && $role['age'] == 'junior') $color = "#00FFFF";
      else if ($role['sex'] == 1 && $role['age'] == 'veteran') $color = "#0000FF";
      else if ($role['sex'] == 1) $color = "#4080FF";
      else if ($role['sex'] == 2 && $role['age'] == 'junior') $color = "#FFAAFF";
      else if ($role['sex'] == 2 && $role['age'] == 'veteran') $color = "#800000";
      else if ($role['sex'] == 2) $color = "#FF0000";
      else $color = '#CCCCCC';
      $data[] = array(
        $role['code'],
        $role['label'],
        $role['c'],
        ($role['title'])?$role['title']:'',
        $role['targets'],
        $color,
      );
    }
    return $data;
  }
  /**
   * Évolution de la parole selon les personnages
   */
  public function edges($play) {
    $threshold = 0.01;
    list($playc) = $this->pdo->query("SELECT c FROM play where code = ".$this->pdo->quote($play))->fetch();
    $q = $this->pdo->prepare("SELECT source, target, sum(c) AS ord FROM sp WHERE play = ? GROUP BY source, target ORDER BY ord DESC");
    $q->execute(array($play));
    $data = array();
    $data[] = array('Source', 'Target', 'Weight', 'max%');
    $max = false;
    while ($sp = $q->fetch()) {
      if(!$max) $max = $sp['ord'];
      // a threshold do not make the graph more readable
      // if ( ($sp['ord']/$playc) < $threshold) break;
      $data[] = array(
        $sp['source'],
        $sp['target'],
        $sp['ord'],
        number_format($sp['ord']/$max, 2),
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
    $nl = $this->_xpath->query("/*/tei:teiHeader/tei:profileDesc/tei:creation/tei:date");
    if ($nl->length) {
      $n = $nl->item(0);
      $year = $n->getAttribute ('when');
      if(!$year) $year = $n->nodeValue;
    }
    else $year = null;
    $title = $this->_xpath->query("/*/tei:teiHeader//tei:title");
    if ($title->length) $title = $title->item(0)->textContent;
    else $title = null;
    $genre = $this->_xpath->evaluate("/*/tei:teiHeader//tei:term[@type='genre']/@subtype");
    if ($genre->length) $genre = $genre->item(0)->nodeValue;
    else $genre = null;
    preg_match('@^([^_]+)_([0-9]+)?@i', $playcode, $matches);

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
    // roles
    $this->pdo->beginTransaction();
    $q = $this->pdo->prepare("
    INSERT INTO role (play, code, label, title, note, rend, sex, age, love)
              VALUES (?,    ?,      ?,   ?,     ?,    ?,    ?,   ?,   ?);
    ");
    $nodes = $this->_xpath->query("//tei:role");
    $castlist = array();
    foreach ($nodes as $n) {
      $note = null;
      $code = $n->getAttribute ('xml:id');
      if (!$code) continue;
      $castlist[$code] = true;
      $label = $n->getAttribute ('n');
      if (!$label) $label = $n->nodeValue;
      $nl = @$n->parentNode->getElementsByTagName("roleDesc");
      if ($nl->length) $title = trim($nl->item(0)->nodeValue);
      else {
        $title = '';
        $nl = $n->parentNode->firstChild;
        while($nl) {
          if ($nl->nodeType == XML_TEXT_NODE ) $title .= $nl->nodeValue;
          $nl = $nl->nextSibling;
        }
        $title = preg_replace(array("/^[\s :;,\.]+/u", "/[\s :,;\.]+$/u"), array('', ''), $title);
        if (!$title) $title = null;
      }
      $rend = ' '.$n->getAttribute ('rend').' '; // espace séparateur
      if (preg_match('@ female @i', $rend)) $sex = 2;
      else if (preg_match('@ male @i', $rend)) $sex = 1;
      else $sex = null;
      preg_match('@ (cadet|junior|senior|veteran) @i', $rend, $matches);
      $age = @$matches[1];
      $love = preg_match('@ love @i', $rend);
      // si pas de nom, garder tout de même, risque d’erreur réseau
      if (!$label) $label = $code;
      $q->execute(array(
        $playcode,
        $code,
        $label,
        $title,
        $note,
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
    INSERT INTO act (play, code, sp) SELECT play, act, count(*) FROM sp WHERE play = $play GROUP BY act;
    UPDATE act SET l = (SELECT SUM(l) FROM sp WHERE play = $play AND sp.act = act.code) WHERE play = $play;
    UPDATE act SET w = (SELECT SUM(w) FROM sp WHERE play = $play AND sp.act = act.code) WHERE play = $play;
    UPDATE act SET c = (SELECT SUM(c) FROM sp WHERE play = $play AND sp.act = act.code) WHERE play = $play;
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
      else {
        $glob = $file;
        foreach(glob($glob) as $file) {
          $dramabase->insert($file);
        }
      }
      // TODO, glob
    }
    if ($action == 'gephi') {
      $dramabase->gephi(array_shift($_SERVER['argv']));
    }
  }
}
?>