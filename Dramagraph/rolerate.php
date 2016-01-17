<?php
if (isset($_REQUEST['play'])) $playcode = $_REQUEST['play'];
else $playcode = 'racine_phedre';
include('Dramabase.php');
$base = new Dramabase('basedrama.sqlite');
$width = @$_REQUEST['width'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramagraphie, proporion des rôles</title>
    <style>
html, body { height: 100%; margin-top:0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
    </style>
  </head>
  <body>
      <form name="net">
          <?php

echo '<select name="play" onchange="this.form.submit()">'."\n";
foreach ($base->pdo->query("SELECT * FROM play ORDER BY author, year") as $play) {
  if ($play['code'] == $playcode) $selected=' selected="selected"';
  else $selected = "";
  echo '<option value="'.$play['code'].'"'.$selected.'>'.$base->bibl($play)."</option>\n";
}
echo "</select>\n";

echo '<select title="Largeur de référence de la bande temporelle" name="width" onchange="this.form.submit()">'."\n";
foreach (array(200, 300, 400, 500, 600, 800, 1000, 1100, 1200, 1400) as $key=>$value) {
  echo '<option';
  if ($value == $width) echo ' selected="selected"';
  // if (!is_numeric($key)) echo ' value="'.$key.'"';
  echo ">$value</option>\n";
}
echo "</select>\n";

          ?>  
      </form>
    <p/>
    <?php
    $base->rolerate($playcode, $width); ?>
  </body>
</html>