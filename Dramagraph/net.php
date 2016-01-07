<?php
if (isset($_REQUEST['play'])) $play = $_REQUEST['play'];
else $play = 'moliere_1669_tartuffe';
include('Dramabase.php');
$base = new Dramabase('basedrama.sqlite');

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramagraph</title>
    <script src="../sigma/sigma.min.js">//</script>
    <script src="../sigma/sigma.layout.forceAtlas2.min.js">//</script>
    <script src="../sigma/sigma.plugins.dragNodes.min.js">//</script>
    <script src="../sigma/sigma.exporters.image.min.js">//</script>
    <script src="Dramanet.js">//</script>

    <style type="text/css">
html, body { height: 100%; margin: 0;}
#container {
  background: #FFFFFF;
  width: 100%;
  height: 100%;
  min-height: 400px;
  margin: auto;
}
button { cursor: pointer; border: none; padding: 0; font-size: 20px; font-family: sans-serif; }
    </style>
  </head>
  <body>

    <div id="container" style="position: relative">
      <form name="net" style="position: absolute; z-index: 2; ">
        <select name="play" onchange="this.form.submit()">
          <?php
echo '<option></option>';
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
          ?>
        </select>
        <button class="reload" type="submit">⟳</button>
        <button class="mix" type="button">♻</button>
        <button class="grav" type="button">►</button>
      </form>
      <!--
      <form id="graphExport" style="position: absolute; bottom: 0; z-index: 2;>
        Image appareil photo ?
        <input name="name" type="text"/>.png
        <input name="width" type="text"/> pixels
        <button onclick="return sigmashot(this)">tout</button>
        <button onclick="return sigmashot(this, true)">visible</button>
      </form>
      -->
    </div>
    <script>
var data = <?php $base->sigma($play); ?>;
var graph1 = new Dramanet("container", data);
    </script>
  </body>
</html>