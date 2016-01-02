<?php
if (isset($_REQUEST['play'])) $play = $_REQUEST['play'];
else $play = 'moliere_1664_tartuffe';
include('Dramabase.php');
$base = new Dramabase('basedrama.sqlite');

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramagraph</title>
    <script src="../sigma/sigma.min.js">//</script>
    <script src="../sigma/sigma.layout.forceAtlas2.min.js">//</script>
    <style type="text/css">
html, body { height: 100%; margin: 0;}
#container {
  background: #FFFFFF;
  width: 100%;
  height: 100%;
  min-height: 400px;
  margin: auto;
}
    </style>
  </head>
  <body>
    <div id="bar">
      <form name="net">
        <select name="play" onchange="this.form.submit()">
          <?php
echo '<option></option>';
foreach ($base->pdo->query("SELECT * FROM play ORDER BY code") as $row) {
  if ($row['code'] == $play) $selected=' selected="selected"';
  else $selected = "";
  echo '<option value="'.$row['code'].'"'.$selected.'>'.$row['author'].', '.$row['title'];
  echo ' ('.$row['acts'].(($row['acts']>2)?" actes":" acte").(($row['verse'])?" en vers":" en prose").")";
  echo "</option>\n";
}
          ?>
        </select>
      </form>
    </div>
    <div id="container">
    <!-- <button id="mix">Remélanger</button> -->
    </div>
    <script>
     <?php
$base->sigma($play);
      ?>


// Instantiate sigma:
var s = new sigma({
  graph: g,
  renderer: {
    container: document.getElementById('container'),
    type: 'canvas'
  },
  settings: {
    defaultEdgeColor: "#DDD",
    defaultNodeColor: "#DDD",
    edgeColor: "default",
    labelSize: "proportional",
    labelSizeRatio: 1,
    drawLabels: true,
    sideMargin: 0.1,
    maxNodeSize: 40,
    maxEdgeSize: 20,
    minArrowSize: 10,
    minNodeSize: 8
  }
});

// Start the ForceAtlas2 algorithm:
var slow = 1;
var startForce = function() {
  s.startForceAtlas2({
    slowDown: slow,
    // adjustSizes: true, // non, sauf avec gravité
    // outboundAttractionDistribution: true, // ?
    // edgeWeightInfluence: 1, // ??
    barnesHutOptimize: false, // ?
    gravity: 1.2, // instable si > 2
    linLogMode: true, // stabilise masi cache des choses
    worker: true, // on dit que c’est bien
  });
};
// stop it after a few seconds
var stopForce = function() {
  setTimeout(function() { s.stopForceAtlas2(); }, 5000*slow);
};
startForce();
stopForce();
/* pas nécessaire
document.getElementById('mix').addEventListener('click', function() {
  if ((s.supervisor || {}).running) {
    s.killForceAtlas2();
    this.innerHTML = 'Remélanger';
  } 
  else {
    startForce();
    this.innerHTML = 'Arrêter';
  }
});
*/
    </script>
  </body>
</html>