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
    <title>Dramacode, pièce</title>
    <link rel="stylesheet" charset="utf-8" type="text/css" href="//dramacode.github.io/Teinte/tei2html.css"/>
    <script src="../sigma/sigma.min.js">//</script>
    <script src="../sigma/sigma.layout.forceAtlas2.min.js">//</script>
    <script src="../sigma/sigma.plugins.dragNodes.min.js">//</script>
    <script src="../sigma/sigma.exporters.image.min.js">//</script>
    <script src="Dramanet.js">//</script>
    <style>
html, body { height: 100%; margin-top:0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
    </style>
  </head>
  <body>
    <div style="margin-left: auto; margin-right: auto; max-width: 120ex; position: relative; ">
      <div class="pannel" style="position:fixed; height: 100%; overflow-y: auto; overflow-x: hidden ; width: 270px;">
    <?php
  $base->panel($play, 270, 800);
        ?>
        <p> </p>
      </div>
      <div style=" margin-left: 270px; background: #FFFFFF; padding: 1em 3em 3em 3em; position: relative; ">
          <form name="net" style="position: fixed; top:0; background: #FFFFFF; z-index: 5;" action="#">
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
            <a href="#" class="but">▲</a>
          </form>
        <div id="graph" style="height: 600px; position: relative;">

          <div style="position: absolute; bottom: 0; right: 0; z-index: 2; ">
            <button class="mix but" type="button" title="Mélanger le graphe">♻</button>
            <button class="grav but" type="button" title="Démarrer ou arrêter la gravité">►</button>
          </div>
        </div>
      <?php 
include('../plays/'.$play.'.html');
       ?>
      </div>
      <script>
var data = <?php $base->sigma($play); ?>;
var graph1 = new Dramanet("graph", data, "../sigma/worker.js"); // 
      </script>
    </div>
  </body>
</html>