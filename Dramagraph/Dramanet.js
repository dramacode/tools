;(function() {
  'use strict';
  
   /**
   * Return the coordinates of the two control points for a self loop (i.e.
   * where the start point is also the end point) computed as a cubic bezier
   * curve.
   *
   * @param  {number} x    The X coordinate of the node.
   * @param  {number} y    The Y coordinate of the node.
   * @param  {number} size The node size.
   * @return {x1,y1,x2,y2} The coordinates of the two control points.
   */
  sigma.utils.dramaSelf = function(x , y, size) {
    return {
      x1: x - size * 3,
      y1: y,
      x2: x,
      y2: y + size * 3
    };
  };
 /**
   * Return the control point coordinates for a quadratic bezier curve.
   *
   * @param  {number} x1  The X coordinate of the start point.
   * @param  {number} y1  The Y coordinate of the start point.
   * @param  {number} x2  The X coordinate of the end point.
   * @param  {number} y2  The Y coordinate of the end point.
   * @return {x,y}        The control point coordinates.
   */
  sigma.utils.dramaTarget = function(x1, y1, x2, y2) {
    return {
      x: (x1 + x2) / 2 + (y2 - y1) / 20,
      y: (y1 + y2) / 2 + (x1 - x2) / 20
    };
  };
  sigma.utils.pkg('sigma.canvas.edges');

  /**
   * This edge renderer will display edges as arrows going from the source node
   *
   * @param  {object}                   edge         The edge object.
   * @param  {object}                   source node  The edge source node.
   * @param  {object}                   target node  The edge target node.
   * @param  {CanvasRenderingContext2D} context      The canvas context.
   * @param  {configurable}             settings     The settings function.
   */
  sigma.canvas.edges.drama = function(edge, source, target, context, settings) {
    var color = edge.color,
        prefix = settings('prefix') || '',
        edgeColor = settings('edgeColor'),
        defaultNodeColor = settings('defaultNodeColor'),
        defaultEdgeColor = settings('defaultEdgeColor'),
        cp = {},
        size = edge[prefix + 'size'] || 1,
        tSize = target[prefix + 'size'],
        sX = source[prefix + 'x'],
        sY = source[prefix + 'y'],
        tX = target[prefix + 'x'],
        tY = target[prefix + 'y'],
        aSize = Math.max(size * 2.5, settings('minArrowSize')),
        d,
        oX,
        oY,
        aX,
        aY,
        vX,
        vY;

    if (!color)
      switch (edgeColor) {
        case 'source':
          color = source.color || defaultNodeColor;
          break;
        case 'target':
          color = target.color || defaultNodeColor;
          break;
        default:
          color = defaultEdgeColor;
          break;
      }

    // self loop, no arrow needed
    if (source.id === target.id) {
      context.strokeStyle = color;
      context.lineWidth = size;
      context.beginPath();
      context.moveTo(sX, sY);
      cp = sigma.utils.dramaSelf(sX, sY, tSize);
      context.bezierCurveTo(cp.x1, cp.y1, cp.x2, cp.y2, tX, tY);
      context.stroke();   
    }
    // target edge, arrow
    else {
      aSize = Math.max(size * 2.5, settings('minArrowSize')),
      d = Math.sqrt(Math.pow(tX - sX, 2) + Math.pow(tY - sY, 2)),
      // start line from outside the source node
      oX = sX + (tX - sX) * (0.9*source[prefix + 'size'] / d),
      oY =  sY + (tY - sY) * (0.9*source[prefix + 'size'] / d),
      aX = sX + (tX - sX) * (d - aSize - tSize) / d,
      aY = sY + (tY - sY) * (d - aSize - tSize) / d,
      vX = (tX - sX) * aSize / d,
      vY = (tY - sY) * aSize / d;
        
      context.strokeStyle = color;
      context.lineWidth = size;
      context.beginPath();
      context.moveTo(oX, oY);
      context.lineTo(aX , aY );
      context.stroke();
      
      context.lineWidth = 5;
      // context.strokeStyle = '#000000';
      context.fillStyle = color;
      context.beginPath();
      context.moveTo(aX + vY * 0.6, aY - vX * 0.6);
      context.lineTo(aX + vX, aY + vY);
      context.lineTo(aX - vY * 0.6, aY + vX * 0.6);
      // context.stroke();
      context.lineTo(aX + vY * 0.6, aY - vX * 0.6);
      context.closePath();
      context.fill();
    }
  };
  
  window.Dramanet = function (canvas, graph, workerUrl) {
    this.canvas = document.getElementById(canvas);
    this.workerUrl = workerUrl;
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
        defaultEdgeColor: "rgba(240, 240, 240, 0.5)",
        defaultNodeColor: "rgba(240, 240, 240, 0.7)",
        edgeColor: "default",
        drawLabels: true,
        defaultLabelSize: 18,
        // font: 'arial',    
        /* marche mais trop grand avec les commentaires
        labelSize: "proportional",
        labelSizeRatio: 1,
        */
        // labelAlignment: 'center', // linkurous only and not compatible with drag node
        sideMargin: 2,
        maxNodeSize: 40,
        maxEdgeSize: 30,
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
      if (!e.data.node._label && e.data.node.title) {
        e.data.node._label = e.data.node.label;
        e.data.node.label = e.data.node.label + ', ' + e.data.node.title;
        e.target.render();
      }
    });
    this.s.bind('outNode', function(e) {
      if (e.data.node._label) {
        e.data.node.label = e.data.node._label;
        e.data.node._label = null;
        e.target.render();
      }
    });
    // Initialize the dragNodes plugin:
    sigma.plugins.dragNodes(this.s, this.s.renderers[0]);
    this.start();
  }
  Dramanet.prototype.start = function() {
    if (this.gravBut) this.gravBut.innerHTML = '◼';
    var pars = {
      // slowDown: 1,
      // adjustSizes: true, // non, ralentit tout
      // outboundAttractionDistribution: true, // non, éloigne Phèdre d’Œnone
      // edgeWeightInfluence: 1, // ralentit tout, impossible de ramener Phèdre à Œnone
      // barnesHutOptimize: false, // ?
      // barnesHutTheta: 0.1,  // pas d’effet apparent sur si petit graphe
      gravity: 0.7, // instable si > 2
      // linLogMode: true, // non, ralentit trop
    };
    if (window.Worker) {
      pars.worker = true;
    }
    if (this.workerUrl) {
      pars.workerUrl = this.workerUrl;
    }
    this.s.startForceAtlas2(pars);
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
})();
