<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8"/>
    <title>Dramagraph</title>
    <script src="../sigma/sigma.min.js">//</script>
    <script src="../sigma/sigma.layout.forceAtlas2.min.js">//</script>
    <script>
   
    
var g={ nodes: [
  {id:'dorine', label:'Dorine', size:16747, x:1, y:1},
  {id:'orgon', label:'Orgon', size:15903, x:0, y:2},
  {id:'tartuffe', label:'Tartuffe', size:13745, x:1, y:3},
  {id:'cleante', label:'Cléante', size:12454, x:0, y:4},
  {id:'elmire', label:'Elmire', size:10656, x:1, y:5},
  {id:'madame-pernelle', label:'Madame Pernelle', size:5575, x:0, y:6},
  {id:'mariane', label:'Mariane', size:4788, x:1, y:7},
  {id:'damis', label:'Damis', size:4070, x:0, y:8},
  {id:'valere', label:'Valère', size:4042, x:1, y:9},
  {id:'loyal', label:'Monsieur Loyal', size:2973, x:0, y:10},
  {id:'exempt', label:'Un exempt', size:2140, x:1, y:11}
], edges: [
  {id:'e1', source:'cleante', target:'orgon', size:7826},
  {id:'e2', source:'tartuffe', target:'elmire', size:7555},
  {id:'e3', source:'elmire', target:'tartuffe', size:5971},
  {id:'e4', source:'dorine', target:'mariane', size:4741},
  {id:'e5', source:'orgon', target:'cleante', size:4541},
  {id:'e6', source:'dorine', target:'orgon', size:4434},
  {id:'e7', source:'tartuffe', target:'orgon', size:3790},
  {id:'e8', source:'orgon', target:'dorine', size:3229},
  {id:'e9', source:'elmire', target:'orgon', size:3226},
  {id:'e10', source:'madame-pernelle', target:'dorine', size:2811},
  {id:'e11', source:'cleante', target:'tartuffe', size:2474},
  {id:'e12', source:'dorine', target:'cleante', size:2412},
  {id:'e13', source:'valere', target:'mariane', size:2237},
  {id:'e14', source:'exempt', target:'tartuffe', size:2140},
  {id:'e15', source:'orgon', target:'tartuffe', size:2004},
  {id:'e16', source:'mariane', target:'dorine', size:1927},
  {id:'e17', source:'damis', target:'elmire', size:1830},
  {id:'e18', source:'loyal', target:'orgon', size:1804},
  {id:'e19', source:'dorine', target:'madame-pernelle', size:1707},
  {id:'e20', source:'tartuffe', target:'cleante', size:1626},
  {id:'e21', source:'orgon', target:'madame-pernelle', size:1596},
  {id:'e22', source:'dorine', target:'damis', size:1565},
  {id:'e23', source:'mariane', target:'orgon', size:1457},
  {id:'e24', source:'orgon', target:'mariane', size:1417},
  {id:'e25', source:'mariane', target:'valere', size:1274},
  {id:'e26', source:'orgon', target:'elmire', size:1184},
  {id:'e27', source:'orgon', target:'damis', size:1166},
  {id:'e28', source:'madame-pernelle', target:'orgon', size:997},
  {id:'e29', source:'cleante', target:'dorine', size:898},
  {id:'e30', source:'valere', target:'cleante', size:837},
  {id:'e31', source:'madame-pernelle', target:'elmire', size:785},
  {id:'e32', source:'damis', target:'orgon', size:692},
  {id:'e33', source:'dorine', target:'tartuffe', size:687},
  {id:'e34', source:'loyal', target:'dorine', size:687},
  {id:'e35', source:'elmire', target:'damis', size:666},
  {id:'e36', source:'valere', target:'dorine', size:632},
  {id:'e37', source:'tartuffe', target:'dorine', size:546},
  {id:'e38', source:'damis', target:'madame-pernelle', size:538},
  {id:'e39', source:'orgon', target:'loyal', size:534},
  {id:'e40', source:'cleante', target:'madame-pernelle', size:496},
  {id:'e41', source:'damis', target:'cleante', size:468},
  {id:'e42', source:'elmire', target:'cleante', size:460},
  {id:'e43', source:'dorine', target:'valere', size:434},
  {id:'e44', source:'damis', target:'dorine', size:399},
  {id:'e45', source:'madame-pernelle', target:'damis', size:392},
  {id:'e46', source:'madame-pernelle', target:'cleante', size:381},
  {id:'e47', source:'cleante', target:'elmire', size:355},
  {id:'e48', source:'loyal', target:'damis', size:336},
  {id:'e49', source:'valere', target:'orgon', size:336},
  {id:'e50', source:'dorine', target:'dorine', size:334},
  {id:'e51', source:'dorine', target:'loyal', size:257},
  {id:'e52', source:'orgon', target:'valere', size:232},
  {id:'e53', source:'madame-pernelle', target:'mariane', size:209},
  {id:'e54', source:'cleante', target:'damis', size:202},
  {id:'e55', source:'elmire', target:'madame-pernelle', size:191},
  {id:'e56', source:'dorine', target:'elmire', size:153},
  {id:'e57', source:'loyal', target:'cleante', size:146},
  {id:'e58', source:'damis', target:'loyal', size:143},
  {id:'e59', source:'elmire', target:'dorine', size:142},
  {id:'e60', source:'cleante', target:'valere', size:107},
  {id:'e61', source:'mariane', target:'tartuffe', size:98},
  {id:'e62', source:'cleante', target:'loyal', size:96},
  {id:'e63', source:'tartuffe', target:'damis', size:92},
  {id:'e64', source:'tartuffe', target:'mariane', size:91},
  {id:'e65', source:'tartuffe', target:'exempt', size:45},
  {id:'e66', source:'dorine', target:'exempt', size:23},
  {id:'e67', source:'mariane', target:'elmire', size:23},
  {id:'e68', source:'mariane', target:'madame-pernelle', size:9}
]};

    </script>
    <style type="text/css">
html, body { height: 100%; margin: 0;}
#container {
  background: #EEEEEE;
  width: 100%;
  height: 100%;
  min-height: 400px;
  margin: auto;
}
    </style>
  </head>
  <body>
    <div id="container"></div>
    <script>
// Instantiate sigma:
var s = new sigma({
  graph: g,
  renderer: {
    container: document.getElementById('container'),
    type: 'canvas'
  },
  settings: {
    defaultEdgeColor: "#FFF",
    defaultNodeColor: "rgba(255, 0, 0, 0.3)",
    edgeColor: "default",
    labelSize: "proportional",
    labelSizeRatio: 1,
    drawLabels: true,
    sideMargin: 0.1,
    maxNodeSize: 40,
    maxEdgeSize: 50,
    minArrowSize: 100,
    minNodeSize: 8
  }
});
// Start the ForceAtlas2 algorithm:
s.startForceAtlas2({
  // slowDown: 2,
  linLogMode: true,
  worker: true
});
// stop it after a few seconds
setTimeout(function() {console.log('stop '+s); s.stopForceAtlas2(); }, 10000)
    </script>
  </body>
</html>