<?php
if (isset($_REQUEST['play'])) $play = $_REQUEST['play'];
else $play = 'moliere_1669_tartuffe';
include('Dramabase.php');
$base = new Dramabase('basedrama.sqlite');
$width = @$_REQUEST['width'];

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramatable</title>
    <style type="text/css">
html, body { height: 100%; margin: 0;}
button { cursor: pointer; border: none; padding: none; font-size: 20px; font-family: sans-serif; }
table.timetable { font-family: sans-serif; border-collapse: collapse; overflow: hidden;}
table.timetable tr { border-bottom: 1px solid #fff; height: 1.25em; }
table.timetable td { border-spacing: 0px 1px; }
table.timetable caption { text-align: left; }
table.timetable th { padding: 0 1ex; font-weight: normal; white-space: nowrap; }
table.timetable td.scene { background: transparent; padding: 0; border: none; }
table.timetable td { background: #000000; border-left: 1px solid #CCCCCC; }
table.timetable td.scene1 { border-left: 2px solid #ff0000; }
table.timetable tr.scenes { border-bottom: 2px #F00 solid; }
table.timetable tr:hover { border-bottom-color: #f00; }
table.timetable tr:hover th { border-bottom: 1px solid #f00; color: #f00; }
table.timetable caption { display: none; }

    </style>
  </head>
  <body>
    <div id="bar">
      <form name="net">
          <?php

echo '<select name="play" onchange="this.form.submit()">'."\n";
foreach ($base->pdo->query("SELECT * FROM play ORDER BY code") as $row) {
  if ($row['code'] == $play) $selected=' selected="selected"';
  else $selected = "";
  echo '<option value="'.$row['code'].'"'.$selected.'>'.$row['author'].', '.$row['title'].' (';
  echo $row['year'].', ';
  if ($row['genre'] == 'tragedy') echo 'tragédie, ';
  if ($row['genre'] == 'comedy') echo 'comédie, ';
  echo $row['acts'].(($row['acts']>2)?" actes":" acte").(($row['verse'])?", vers":", prose").")";
  echo "</option>\n";
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
        </select>
      </form>
    </div>

      <?php
$base->timetable($play, $width);
      ?>
  </body>
</html>