{literal}
<style>
 #channel g.row text {fill: grey;};
.countries {stroke:grey;stroke-width:1;}

.panel .panel-heading .nav-tabs {
            margin:-10px -15px -12px -15px;
            border-bottom-width:0;
}

.panel .panel-heading .nav-tabs li a {
                    padding:15px;
                    margin-bottom:1px;
                    border:solid 0 transparent;
}
.panel .panel-heading .nav-tabs li a:hover {
                    border-color: transparent;
                    }


.panel .panel-heading .nav-tabs li.active a,.panel .panel-heading .nav-tabs li.active a:hover {
                        border:solid 0 transparent;                         
                    }
</style>
<div class="container" role="main">
<div class="page-header"></div>
<div class="row">
<div id="date" class="col-md-12"><div class="panel panel-default"><div class="panel-heading">Date

<div class="btn-group" id="btn-date"></div>

</div>
<div class="panel-body"> <div class="graph"></div></div></div>
</div>
</div>
<div class="row">
	<div class="col-md-3 col-xs-6">
		<div id="overview">
			<ul class="list-group">
				<li class="list-group-item"><span class="summary_total"></span> total</button></li>
				<li class="list-group-item list-group-item-success"><span class="badge total_percent"></span><span class="total"></span> signatures</button></li>
			</ul>
</div>
	</div>
<div id="channel" class="col-md-3 col-xs-6"><div class="panel panel-default">
  <div class="panel-heading" title="click to select campaigns">
<th><input id="input-filter" placeholder="Channel" title="search on channel"/>
</div>
<div class="panel-body"> <div class="graph"></div></div></div></div>
<div id="country" class="col-md-6 col-xs-12"><div class="panel panel-default">
<div class="panel-heading">

 <ul class="nav nav-tabs" role="tablist" >
<li class="disabled"><a href=""><b>Country</b></a></li>
    <li role="presentation" class="active"><a href="#map" aria-controls="map" role="tab" data-toggle="tab">Map</a></li>
    <li role="presentation"><a href="#pie" aria-controls="pie" role="tab" data-toggle="tab">Pie</a></li>
  </ul>

</div><div class="panel-body">


  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="graph tab-pane active fade in" id="map"></div>
    <div role="tabpanel" class="tab-pane pie fade in" id="pie"></div>
  </div>
</div></div></div>

</div>


    <div class="row">
      <div class="col-md-12">
<div id="map"></div>
      <table class="table table-striped hidden" id="table">
        <thead>
          <tr>
            <th>Channel</th>
            <th>Country</th>
            <th>Total</th>
          </tr>
        </thead>
      </table>
      </div>
    </div>
  </div>

    <script>
"use strict";
   var $=jQuery;

  function setUrl(){
   //var country=graphs.country.filters();
   var country=null; // need to take it from the map
   var channel=jQuery("#input-filter").val();
   var url="?";
   if (country && country.length > 0) url +="country="+country+"&";
   if (channel) url +="channel="+channel+"&";
   window.history.pushState(null, country + " " + channel, url);

  };

  function hasFilter() {
    return jQuery.urlParam("country") || jQuery.urlParam("channel");
  };

  jQuery.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return decodeURI(results[1]) || 0;
    }
};
    var graphs = [];
    var summary= {};
  var europe=null;
{/literal}
  var data = {crmSQL json="Trending" group_id=42};
{literal}
/*
  var q=d3.queue()
    .defer (function(callback) {
      d3.json("data/europe50b.json", function(error, json) {
        europe=json;
        callback();
      });
    })
    .defer (function(callback) {
      d3.csv("data/openeci.csv", function(error, csv) {
        data=csv;
        callback();
      });
    })
    .await(function (){
      draw();
    });
*/
    (function($){
    draw();
    })(CRM.$);
    
    function draw () {
      
      //var dateFormat = d3.time.format.utc("%Y-%m-%d");
      var dateFormat = d3.time.format.utc("%Y%m%d%H");
      var formatNumber = function (d){ return d3.format(",")(d).replace(",","'")};
      var formatPercent =d3.format(".2%");
 
      data.values.forEach(function(d) {
        d.total = + d.total;
        d.date = dateFormat.parse(d.ts.toString());
      }); 
      var ndx = crossfilter(data.values);

//      graphs.pie = drawPie("#test");
      drawNumbers(graphs); //needs to be the first one
//      graphs.channel = drawChannel("#channel .graph");
//      graphs.map = drawMap ("#country .graph");
//      graphs.country = drawCountry("#country .pie");
      graphs.date = drawDate("#date .graph");
      graphs.btn_date = drawDateButton("#date .btn-group",graphs.date);
//      graphs.table = drawTable("#table");
      graphs.total.on("postRedraw", function(){setUrl()});

      summary.total = graphs.total.data();


      jQuery (function($) {
        graphs.search = drawTextSearch('#input-filter',jQuery);
        $(".summary_total").text(formatNumber(summary.total));
        $("#input-filter").val($.urlParam("channel")).keyup();//if channel param, filter
        if ($.urlParam("country")){
          $.urlParam("country").split(',').forEach(function(d){
            graphs.country.filter(d);//if channel param, filter
          });
        }
        if (!hasFilter())
          $("#date button")[0].click(); //select today
        dc.renderAll();
      });


function drawNumbers (graphs){

  
  var group = ndx.groupAll().reduce(
    function (p, v) {
	p.total += +v.total;
	return p;
    },
    function (p, v) {
	p.total -= +v.total;
	return p;
    },
    function () { return {
       total:0,
      };
    });
  
  graphs.total=dc.numberDisplay(".total") 
    .valueAccessor(function(d){ 
       summary.filtered=d.total;
       return d.total
    })
    .formatNumber(formatNumber)
    .group(group);

  graphs.total_percent=dc.numberDisplay(".total_percent") 
    .valueAccessor(function(d){
       if (d.total == summary.total){
         $(".summary_total").parent().slideUp();
         return summary.total/1000000;
       }
       $(".summary_total").parent().slideDown();
       return d.total/summary.total})
    .formatNumber(formatPercent)
    .group(group);
}

function drawMap (dom) {
  var width=450;
  var dim = ndx.dimension(function(d) {return d.country.toUpperCase();});
  //var group = dim.group().reduceSum(function(d) {return d.total;})
  var group = dim.group().reduce(
    function (p, v) {
      p.total += +v.total;
      if (p.iso == "") {
        p.iso = v.country;
        p.threshold =country[country.findIndex(function(c) {return v.country==c.country})].threshold;
      }
      return p;
    },
    function (p, v) {
      p.total -= +v.total;
      return p;
    },
    function () { return {
       total:0,threshold:0,iso:""
      };
    });
  
    var _colorsI = d3.scale.linear().range(["red","orange","lightgreen","green"])
     .domain([0,0.5,1,1.1])
     .clamp(true);

    var _colorsR = d3.scale.linear().range(["#C6E2FF","#1D7CF2"])
     .domain([0,1])
     .clamp(true);

   var _colors = function (value) {
     if (!value)
       return "#F3F3F3";
     if (summary.total != summary.filtered) {
       if (value.total == 0)
         return "#ECF0F3";
       return _colorsR(value.total/summary.filtered);
     }
     return _colorsI(value.total/value.threshold);
   }
     

  var projection = d3.geo.equirectangular()
    .center([37,47]) //theorically, 50°7′2.23″N 9°14′51.97″E but this works
    .scale(width*1.3);
  var geojson=topojson.feature(europe,europe.objects.countries);
  //fix iso countries
  geojson.features.forEach(function (d) {
    if (d.id == "GB")
      d.id = "UK";
    if (d.id == "GR")
      d.id = "EL";
  });
 
  var map = dc.geoChoroplethChart(dom)
	.height(width)
	.width(width)
	.dimension(dim)
	.projection(projection)
  .colorAccessor(function(d){
    return d;
    if (!d || !d.iso) 
      return d; // or do something
    if (d.threshold > 0) {
      
      return d.total/d.threshold
    }
    return 0;
  })
	.group(group)
  .colors(_colors)
//	.colorCalculator(_colorCalculator)
	.overlayGeoJson(geojson.features,"countries",function(d){
	   return d.id;
	})
	.title(function (v) {
    var d=v.value;
    if (!d || !d.threshold) {
      return v.key;// + " (not in the EU)";
    }
    return d.iso + " "+formatPercent(d.total / d.threshold)+ "\n- signatures: " + d.total + "\n- threshold: " + d.threshold;
  });
		
		
	map.on("renderlet.a",function (chart){
	  chart.selectAll(".countries").on("mouseover", function (d) {
	    //console.log(d3.select(this));
	    $("#explanation").html ("<h3>"+d.properties.name+"</h3>");
	  });
	
	  chart.selectAll(".countries").on("click", function (d) {
								    //$('.bar.'+'1').addClass('selectedbar');
										//barchart.filterAll();
										//barchart.redraw();
	  })
       }); 

};

function drawDateButton(dom, graph) {
var data = [
    { key: "today", label: "Today" },
    { key: "yesterday", label: "Yesterday" },
    { key: "week", label: "This week" },
    { key: "month", label: "This month" },
    { key: "Infinity", label: "All" }
];
  d3.select(dom)
    .selectAll("button")
    .data(data)
    .enter()
    .append ("button")
    .text(function (d) {return d.label})
    .classed("btn",true)
    .classed("btn-default",true)
    .on("click", function () {
       var btn=d3.select(this);
       d3.selectAll(dom +" .active").classed("active", false);
       btn.classed("active",true);
	    var s = new Date(), e = new Date();
	    switch (btn.data()[0].key) {
	      case "today":
		s = d3.time.day.utc(e);
		break;
	      case "yesterday":
		e = d3.time.day.utc(s);
		s = d3.time.day.offset(e, -1);
		break;
	      case "week":
		s = d3.time.monday.utc(e);
		break;
	      case "month":
		s = d3.time.month.utc(e);
		break;
	      default:
		s = d3.time.day.offset(e, - + btn.data()[0].key);
	    }

	    graph.filterAll(); //reset filter
	    graph.filter(dc.filters.RangedFilter(s,e));
	    graph.redrawGroup();
    });


}

function drawDate (dom) {
  var dim = ndx.dimension(function(d){return d.date;});//already grouped by day
  var group = dim.group().reduceSum(function(d){return d.total;});
  var graph=dc.lineChart(dom)
   .margins({top: 0, right: 20, bottom: 20, left:30})
    .height(150)
    .width(650)
    .dimension(dim)
    .renderArea(true)
    .group(group)
    .brushOn(false)
    .title (function(d) {return dateFormat(d.key)+": "+d.value+" signatures"})
    .x(d3.time.scale.utc().domain([dim.bottom(1)[0].date,dim.top(1)[0].date]))
    .round(d3.time.day.utc.round)
    .elasticY(true)
    .xUnits(d3.time.days.utc);

   graph.yAxis().ticks(5).tickFormat(d3.format(".2s"));
   graph.xAxis().ticks(7);

  return graph;
}

      function drawCountry (dom) {
        var dim  = ndx.dimension(function(d) {return d.country;});
        var group = dim.group().reduceSum(function(d) {return d.total;});
        var chart = dc.pieChart(dom)
          .width(200)
          .height(200)
          .innerRadius(10)
          .dimension(dim)
          .group(group);
   
          return chart;
      }
    
function drawTextSearch (dom,$,val) {

  var dim = ndx.dimension(function(d) { return d.channel});

  var throttleTimer;

  $(dom).keyup (function () {

    var s = jQuery(this ).val().toLowerCase();
    $(".resetall").attr("disabled",false);
    throttle();

    function throttle() {
      window.clearTimeout(throttleTimer);
      throttleTimer = window.setTimeout(function() {
        dim.filterAll();
        dim.filterFunction(function (d) { return d.indexOf (s) !== -1;} );
	dc.redrawAll();
      }, 250);
    }
  });

  return dim;

}

function filter_bins(source_group, f) {
    f = f || function(d) {return d.value != 0;};

    return {
        all:function () {
            return source_group.all().filter(function(d) {
                return f(d.value);
            });
        }
    };
}
 function drawChannel (dom) {
	function remove_empty_bins(source_group) {
	    return {
		all:function () {
		    return source_group.all().filter(function(d) {
			return d.value != 0;
		    });
		}
	    };
	}


       var dim = ndx.dimension(
         function(d){
         return d.channel;
       });

       var allGroup = dim.group()
	.reduceSum(function(d){return d.total;});
       var group = allGroup;//remove_empty_bins(allGroup);

	  var graph  = dc.rowChart(dom)
	    .width(200)
	    .height(275)
	    .gap(0)
	    .rowsCap(18)
	    .ordering(function(d) { return -d.value })
	//    .ordering(function(d) { return -d.value.count })
	//    .valueAccessor( function(d) { return d.value.count })
	//    .label (function (d) {return d.key;})
	//    .title (function (d) {return d.key + ":" + d.value.count + "\nsignatures:" + d.value.sign + "\nnew:"+d.value.sign_new;})
	    .dimension(dim)
	    .elasticX(true)
	.labelOffsetY(10)
	.fixedBarHeight(14)
	.labelOffsetX(2)
        .colorCalculator(function(d){return 'lightblue';})
	.group(filter_bins(group));

    graph.xAxis().ticks(4);
    graph.margins().left = 5;
    graph.margins().top = 0;
    graph.margins().bottom = 0;
	  return graph;
	}

      function drawTable (dom) {
        var dim  = ndx.dimension(function(d) {return null;});
        var chart= dc.dataTable(dom)
              .dimension(dim)
              .size(1000)
              .group(function (d) { return "<b>"+dateFormat(d.date)+"</b>";})
              .columns([
                  function (d) {
                      return d.channel;
                  },
                  function (d) {
                      return d.country;
                  },
                  function (d) {
                      return d.total;
                  }
              ])
              .sortBy(function (d) {
                  return d.total;
              })
              .order(d3.descending);
        return chart;

      }

    };






    </script>
{/literal}
