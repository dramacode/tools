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

    <div id="bar" style="position: absolute; z-index: 2; ">
      <form name="net">
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
        <button id="reload" type="submit">⟳</button>
        <button id="mix" type="button">♻</button>
        <button id="grav" type="button">►</button>
      </form>
    </div>
    <div id="container">
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
    drawLabels: true,
    defaultLabelSize: 18,
    // font: 'arial',    
    /* marche mais trop grand avec les commentaires
    labelSize: "proportional",
    labelSizeRatio: 1,
    */
    // labelAlignment: 'center', // linkurous only and not compatible avec drag node

    sideMargin: 2,
    maxNodeSize: 40,
    maxEdgeSize: 25,
    minArrowSize: 10,
    minNodeSize: 10,
    borderSize: 2,
    outerBorderSize: 3, // stroke size of active nodes
    defaultNodeBorderColor: '#FFF',
    defaultNodeOuterBorderColor: 'rgb(236, 81, 72)', // stroke color of active nodes
    enableEdgeHovering: true,
    edgeHoverColor: 'edge',
    defaultEdgeHoverColor: '#000',
    edgeHoverSizeRatio: 1,
    edgeHoverExtremities: true,
  }
});

// Start the ForceAtlas2 algorithm:
var startForce = function() {
  document.getElementById('grav').innerHTML = '◼';
  s.startForceAtlas2({
    // slowDown: 1,
    // adjustSizes: true, // non, ralentit tout
    // outboundAttractionDistribution: true, // non, éloigne Phèdre d’Œnone
    // edgeWeightInfluence: 1, // ralentit tout, impossible de ramener Phèdre à Œnone
    // barnesHutOptimize: false, // ?
    // barnesHutTheta: 0.1,  // pas d’effet apparent sur si petit graphe
    gravity: 0.7, // instable si > 2
    // linLogMode: true, // non, ralentit trop
    worker: false, // ne marche pas dans Opera
  });
};
// stop it after a few seconds
var stopForce = function() {
  setTimeout(function() { s.killForceAtlas2(); document.getElementById('grav').innerHTML = '►'; }, 5000);
  
};
startForce();
stopForce();
// Initialize the dragNodes plugin:
var dragListener = sigma.plugins.dragNodes(s, s.renderers[0]);
// button gravity
document.getElementById('grav').addEventListener('click', function() {
  if ((s.supervisor || {}).running) {
    s.killForceAtlas2();
    this.innerHTML = '►';
  } 
  else {
    startForce();
    this.innerHTML = '◼';
    stopForce();
    
  }
  return false;
});
document.getElementById('mix').addEventListener('click', function() {
  s.killForceAtlas2();
  document.getElementById('grav').innerHTML = '►';
  for (var i=0; i < s.graph.nodes().length; i++) {
    s.graph.nodes()[i].x = Math.random()*10;
    s.graph.nodes()[i].y = Math.random()*10;
  }
  s.refresh();
  startForce();
  stopForce();
  return false;
});
s.bind('overNode', function(e) {
  // attention, n’écrire qu’une fois
  if (!e.data.node._label) {
    console.log(e.data);
    e.data.node["renderer1:size"]=e.data.node["renderer1:size"]/2;
    e.data.node._label = e.data.node.label;
    e.data.node.label = e.data.node.label + ', ' + e.data.node.title;
    s.refresh();
  }
});
s.bind('outNode', function(e) {
  if (e.data.node._label) {
    e.data.node.label = e.data.node._label;
    e.data.node._label =null;
    s.refresh();
  }
});
    </script>
  </body>
</html>