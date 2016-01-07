
(function() {
  'use strict';
  window.Dramanet = function (canvas, graph) {
    this.canvas = document.getElementById(canvas);
    var els = this.canvas.getElementsByClassName('grav');
    if (els.length) {
      this.gravBut = els[0];
      this.gravBut.dramanet = this;
      this.gravBut.onclick = this.grav;
    }
    var els = this.canvas.getElementsByClassName('mix');
    if (els.length) {
      this.mixBut = els[0];
      this.mixBut.dramanet = this;
      this.mixBut.onclick = this.mix;
    }
    var els = this.canvas.getElementsByClassName('shot');
    if (els.length) this.shotBut = els[0];
    this.s = new sigma({
      graph: graph,
      renderer: {
        container: this.canvas,
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
    this.s.bind('overNode', function(e) {
      // attention, n’écrire qu’une fois
      if (!e.data.node._label) {
        e.data.node._label = e.data.node.label;
        e.data.node.label = e.data.node.label + ', ' + e.data.node.title;
        e.target.render();
      }
    });
    this.s.bind('outNode', function(e) {
      if (e.data.node._label) {
        e.data.node.label = e.data.node._label;
        e.data.node._label =null;
        e.target.render();
      }
    });
    // Initialize the dragNodes plugin:
    sigma.plugins.dragNodes(this.s, this.s.renderers[0]);
    this.start();
  }
  Dramanet.prototype.start = function() {
    if (this.gravBut) this.gravBut.innerHTML = '◼';
    this.s.startForceAtlas2({
      // slowDown: 1,
      // adjustSizes: true, // non, ralentit tout
      // outboundAttractionDistribution: true, // non, éloigne Phèdre d’Œnone
      // edgeWeightInfluence: 1, // ralentit tout, impossible de ramener Phèdre à Œnone
      // barnesHutOptimize: false, // ?
      // barnesHutTheta: 0.1,  // pas d’effet apparent sur si petit graphe
      gravity: 0.7, // instable si > 2
      // linLogMode: true, // non, ralentit trop
      worker: true, // ne marche pas dans Opera
    });
    var dramanet = this;
    setTimeout(function() { dramanet.stop();}, 5000)
  };
  Dramanet.prototype.stop = function() {
    this.s.killForceAtlas2(); 
    if (this.gravBut) this.gravBut.innerHTML = '►'; 
  };
  Dramanet.prototype.shot = function (button, clip) {
    sigma.plugins.image(this.s, this.s.renderers[0], {
      download: true,
      size: size,
      margin: 50,
      background: color,
      clip: clip,
      zoomRatio: 1,
    });
    return false;
  };
  Dramanet.prototype.grav = function() {
    if ((this.dramanet.s.supervisor || {}).running) {
      this.dramanet.s.killForceAtlas2();
      this.innerHTML = '►';
    }
    else {
      this.innerHTML = '◼';
      this.dramanet.start();
    }
    return false;
  };
  Dramanet.prototype.mix = function() {
    this.dramanet.s.killForceAtlas2();
    if (this.dramanet.s.gravBut) this.dramanet.s.gravBut.innerHTML = '►';
    for (var i=0; i < this.dramanet.s.graph.nodes().length; i++) {
      this.dramanet.s.graph.nodes()[i].x = Math.random()*10;
      this.dramanet.s.graph.nodes()[i].y = Math.random()*10;
    }
    this.dramanet.s.refresh();
    this.dramanet.start();
    return false;
  };
}).call(window)
