<?php
if (isset($_REQUEST['play'])) $playcode = $_REQUEST['play'];
else $playcode = 'moliere_tartuffe';
include('Dramabase.php');
$base = new Dramabase('basedrama.sqlite');
$width = @$_REQUEST['width'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramabars</title>
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
foreach (array('100%', 200, 300, 400, 500, 600, 800, 1000, ''=>'auto') as $key=>$value) {
  echo '<option';
  if ($value == $width) echo ' selected="selected"';
  if (!is_numeric($key)) echo ' value="'.$key.'"';
  echo ">$value</option>\n";
}
echo "</select>\n";

          ?>  
      </form>
    <p/>
      <?php
$base->timebars($playcode, $width);
      ?>

  </body>
</html>