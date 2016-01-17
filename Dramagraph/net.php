<?php
if (isset($_REQUEST['play'])) $playcode = $_REQUEST['play'];
else $playcode = 'moliere_tartuffe';
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
foreach ($base->pdo->query("SELECT * FROM play ORDER BY author, year") as $play) {
  if ($play['code'] == $playcode) $selected=' selected="selected"';
  else $selected = "";
  echo '<option value="'.$play['code'].'"'.$selected.'>'.$base->bibl($play)."</option>\n";
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
var data = <?php $base->sigma($playcode); ?>;
var graph1 = new Dramanet("container", data, "../sigma/worker.js"); // 
    </script>
  </body>
</html>