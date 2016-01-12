<?php
if (isset($_REQUEST['play'])) $play = $_REQUEST['play'];
else $play = 'moliere_tartuffe';
include('Dramabase.php');
$base = new Dramabase('basedrama.sqlite');
$width = @$_REQUEST['width'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramabrowse</title>
    <link rel="stylesheet" charset="utf-8" type="text/css" href="../../Teinte/tei2html.css"/>
    <style>
html, body { height: 100%; margin-top:0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
    </style>
  </head>
  <body>
    <div style="margin-left: auto; margin-right: auto; max-width: 120ex; ">
      <div style="position:fixed; height: 100%; overflow-y: auto; width: 13em;">
    <?php
  $base->timepanel($play, 800);
        ?>
        <p> </p>
      </div>
      <div style=" margin-left: 15em; ">
        <form name="net" style="position: fixed; top:0;">
          <?php

echo '<select name="play" onchange="this.form.submit()">'."\n";
foreach ($base->pdo->query("SELECT * FROM play ORDER BY code") as $row) {
  if ($row['code'] == $play) $selected=' selected="selected"';
  else $selected = "";
  echo '<option value="'.$row['code'].'"'.$selected.'>'.$row['author'].', '.$row['title'].' (';
  if ($row['year']) echo $row['year'].', ';
  if ($row['genre'] == 'tragedy') echo 'tragédie, ';
  if ($row['genre'] == 'comedy') echo 'comédie, ';
  echo $row['acts'].(($row['acts']>2)?" actes":" acte").(($row['verse'])?", vers":", prose").")";
  echo "</option>\n";
}
echo "</select>\n";


          ?>
          <a href="#">▲</a>
      </form>
      <?php 
$dom = new DOMDocument();
$dom->load('../../dramacode.github.io/html/'.$play.'.html');
$xpath = new DOMXPath($dom);
$article = $xpath->query('//*[@id="article"]');
$article = $article->item(0);
echo $dom->saveXML($article);
       ?>
      </div>
    </div>
  </body>
</html>