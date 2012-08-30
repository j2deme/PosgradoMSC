
// Chart design based on the recommendations of Stephen Few. Implementation
// based on the work of Clint Ivy, Jamie Love, and Jason Davies.
// http://projects.instantcognition.com/protovis/bulletchart/
nv.models.bulletChart = function() {
  var orient = 'left', // TODO top & bottom
      reverse = false,
      margin = {top: 5, right: 40, bottom: 20, left: 120},
      ranges = function(d) { return d.ranges },
      markers = function(d) { return d.markers },
      measures = function(d) { return d.measures },
      width = null,
      height = 55,
      tickFormat = null,
      tooltips = true,
      tooltip = function(key, x, y, e, graph) { 
        return '<h3>' + e.label + '</h3>' +
               '<p>' +  e.value + '</p>'
      };


  var dispatch = d3.dispatch('tooltipShow', 'tooltipHide'),
      bullet = nv.models.bullet();


  var showTooltip = function(e, offsetElement) {
    var offsetElement = document.getElementById("chart"),
        left = e.pos[0] + offsetElement.offsetLeft + margin.left,
        top = e.pos[1] + offsetElement.offsetTop + margin.top;

    var content = '<h3>' + e.label + '</h3>' +
            '<p>' + e.value + '</p>';

    nv.tooltip.show([left, top], content, e.value < 0 ? 'e' : 'w');
  };


  // For each small multipleâ€¦
  function chart(g) {
    g.each(function(d, i) {
      var container = d3.select(this);

      var availableWidth = (width  || parseInt(container.style('width')) || 960)
                             - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          that = this;

      var rangez = ranges.call(this, d, i).slice().sort(d3.descending),
          markerz = markers.call(this, d, i).slice().sort(d3.descending),
          measurez = measures.call(this, d, i).slice().sort(d3.descending);

      var wrap = container.selectAll('g.wrap.bulletChart').data([d]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'wrap nvd3 bulletChart');
      var gEnter = wrapEnter.append('g');

      gEnter.append('g').attr('class', 'bulletWrap');
      gEnter.append('g').attr('class', 'titles');

      var g = wrap.select('g')
      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      // Compute the new x-scale.
      var x1 = d3.scale.linear()
          .domain([0, Math.max(rangez[0], markerz[0], measurez[0])])  // TODO: need to allow forceX and forceY, and xDomain, yDomain
          .range(reverse ? [availableWidth, 0] : [0, availableWidth]);

      // Retrieve the old x-scale, if this is an update.
      var x0 = this.__chart__ || d3.scale.linear()
          .domain([0, Infinity])
          .range(x1.range());

      // Stash the new scale.
      this.__chart__ = x1;

      /*
      // Derive width-scales from the x-scales.
      var w0 = bulletWidth(x0),
          w1 = bulletWidth(x1);

      function bulletWidth(x) {
        var x0 = x(0);
        return function(d) {
          return Math.abs(x(d) - x(0));
        };
      }

      function bulletTranslate(x) {
        return function(d) {
          return 'translate(' + x(d) + ',0)';
        };
      }
      */

      var w0 = function(d) { return Math.abs(x0(d) - x0(0)) }, // TODO: could optimize by precalculating x0(0) and x1(0)
          w1 = function(d) { return Math.abs(x1(d) - x1(0)) };


      var title = g.select('.titles').append("g")
          .attr("text-anchor", "end")
          .attr("transform", "translate(-6," + (height - margin.top - margin.bottom) / 2 + ")");
      title.append("text")
          .attr("class", "title")
          .text(function(d) { return d.title; });

      title.append("text")
          .attr("class", "subtitle")
          .attr("dy", "1em")
          .text(function(d) { return d.subtitle; });



      bullet
        .width(availableWidth)
        .height(availableHeight)

      var bulletWrap = g.select('.bulletWrap')
          //.datum(data);

      d3.transition(bulletWrap).call(bullet);



      // Compute the tick format.
      var format = tickFormat || x1.tickFormat(8);

      // Update the tick groups.
      var tick = g.selectAll('g.tick')
          .data(x1.ticks(8), function(d) {
            return this.textContent || format(d);
          });

      // Initialize the ticks with the old scale, x0.
      var tickEnter = tick.enter().append('g')
          .attr('class', 'tick')
          .attr('transform', function(d) { return 'translate(' + x0(d) + ',0)' })
          .style('opacity', 1e-6);

      tickEnter.append('line')
          .attr('y1', availableHeight)
          .attr('y2', availableHeight * 7 / 6);

      tickEnter.append('text')
          .attr('text-anchor', 'middle')
          .attr('dy', '1em')
          .attr('y', availableHeight * 7 / 6)
          .text(format);

      // Transition the entering ticks to the new scale, x1.
      d3.transition(tickEnter)
          .attr('transform', function(d) { return 'translate(' + x1(d) + ',0)' })
          .style('opacity', 1);

      // Transition the updating ticks to the new scale, x1.
      var tickUpdate = d3.transition(tick)
          .attr('transform', function(d) { return 'translate(' + x1(d) + ',0)' })
          .style('opacity', 1);

      tickUpdate.select('line')
          .attr('y1', availableHeight)
          .attr('y2', availableHeight * 7 / 6);

      tickUpdate.select('text')
          .attr('y', availableHeight * 7 / 6);

      // Transition the exiting ticks to the new scale, x1.
      d3.transition(tick.exit())
          .attr('transform', function(d) { return 'translate(' + x1(d) + ',0)' })
          .style('opacity', 1e-6)
          .remove();

/*
      bullet.dispatch.on('elementMouseover', function(e) {
          var offsetElement = document.getElementById("chart"),
              left = e.pos[0] + offsetElement.offsetLeft + margin.left,
              top = e.pos[1] + offsetElement.offsetTop + margin.top;

          var content = '<h3>' + e.label + '</h3>' +
                  '<p>' +
                  e.value +
                  '</p>';

          nv.tooltip.show([left, top], content, e.value < 0 ? 'e' : 'w');
      });


      bullet.dispatch.on('elementMouseout', function(e) {
          nv.tooltip.cleanup();
      });
*/

      bullet.dispatch.on('elementMouseover.tooltip', function(e) {
        //e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
        dispatch.tooltipShow(e);
      });
      if (tooltips) dispatch.on('tooltipShow', function(e) { showTooltip(e, that.parentNode) } ); // TODO: maybe merge with above?

      bullet.dispatch.on('elementMouseout.tooltip', function(e) {
        dispatch.tooltipHide(e);
      });
      if (tooltips) dispatch.on('tooltipHide', nv.tooltip.cleanup);



    });
    d3.timer.flush();
  }


  chart.dispatch = dispatch;
  chart.bullet = bullet;

  // left, right, top, bottom
  chart.orient = function(x) {
    if (!arguments.length) return orient;
    orient = x;
    reverse = orient == 'right' || orient == 'bottom';
    return chart;
  };

  // ranges (bad, satisfactory, good)
  chart.ranges = function(x) {
    if (!arguments.length) return ranges;
    ranges = x;
    return chart;
  };

  // markers (previous, goal)
  chart.markers = function(x) {
    if (!arguments.length) return markers;
    markers = x;
    return chart;
  };

  // measures (actual, forecast)
  chart.measures = function(x) {
    if (!arguments.length) return measures;
    measures = x;
    return chart;
  };

  chart.width = function(x) {
    if (!arguments.length) return width;
    width = x;
    return chart;
  };

  chart.height = function(x) {
    if (!arguments.length) return height;
    height = x;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.tickFormat = function(x) {
    if (!arguments.length) return tickFormat;
    tickFormat = x;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) return tooltips;
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };


  return chart;
};


