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
    <title>Dramagraphie, liseuse</title>
    <link rel="stylesheet" charset="utf-8" type="text/css" href="//dramacode.github.io/Teinte/tei2html.css"/>
    <script src="../sigma/sigma.min.js">//</script>
    <script src="../sigma/sigma.layout.forceAtlas2.min.js">//</script>
    <script src="../sigma/sigma.plugins.dragNodes.min.js">//</script>
    <script src="../sigma/sigma.exporters.image.min.js">//</script>
    <script src="Dramanet.js">//</script>
    <style>
html, body { height: 100%; margin-top:0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
body { font-size: 12px; }
/* couleur des rôles par importance */
.charline .role { background-color: rgba(192, 192, 192, 0.7); color: rgba(0, 0, 0, 0.5);}
.charline .role1 { background-color: rgba(255, 0, 0, 0.4); color: rgba(255, 255, 255, 1);}
.charline .role2 { background-color: rgba(128, 0, 128, 0.4); color: rgba(255, 255, 255, 1);}
.charline .role3 { background-color: rgba(0, 0, 255, 0.4); color:  rgba(255, 255, 255, 1);}
.charline .role4 { background-color: rgba(0, 0, 128, 0.4); color:  rgba(255, 255, 255, 1);}
.charline .role5 { background-color: rgba(128, 128, 128, 0.4); color: rgba(255, 255, 255, 1); }


    </style>
  </head>
  <body>
    <div style="margin-left: auto; margin-right: auto; max-width: 120ex; ">
      <form name="net" style="position: fixed; top:0; padding-left: 2em;  background: #FFFFFF; z-index: 5; text-align: right;  " action="#">
            <?php

echo '<select name="play" onchange="this.form.submit()">'."\n";
foreach ($base->pdo->query("SELECT * FROM play ORDER BY author, year") as $play) {
  if ($play['code'] == $playcode) $selected=' selected="selected"';
  else $selected = "";
  echo '<option value="'.$play['code'].'"'.$selected.'>'.$base->bibl($play)."</option>\n";
}
echo "</select>\n";


          ?>
            <a href="#" class="but">▲</a>
          </form>      
      <div class="pannel" style="position:fixed; height: 100%; overflow-y: auto; overflow-x: hidden ; width: 230px;">
      <p> </p>
    <?php
  $base->charline($playcode, 230, 800);
        ?>
        <p> </p>
      </div>
      <div style=" margin-left: 230px; background: #FFFFFF; padding: 1em 3em 3em 3em; position: relative;">
    
        <div id="graph" style="height: 450px; position: relative;">

          <div style="position: absolute; bottom: 0; right: 0; z-index: 2; ">
            <button class="mix but" type="button" title="Mélanger le graphe">♻</button>
            <button class="grav but" type="button" title="Démarrer ou arrêter la gravité">►</button>
          </div>
        </div>
      <?php 
include('../plays/'.$playcode.'.html');
       ?>
      </div>
      <script>
var data = <?php $base->sigma($playcode); ?>;
var graph1 = new Dramanet("graph", data, "../sigma/worker.js"); // 
      </script>
    </div>
  </body>
</html>