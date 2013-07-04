(function(){ 
  /* SETUP VARIABLES  */   
  // console.log('something');      
  var rootdir = "map-archive/";
  var routeLines;  
  var config = {
      tileUrl : 'http://{s}.tiles.mapbox.com/v3/openplans.map-g4j0dszr/{z}/{x}/{y}.png',
      tileAttrib : 'Map tiles: OpenStreetMap ',
      initLatLng : new L.LatLng(51.54335, 0.71033),    // SOUTHEND  
      initZoom : 15,
      minZoom : 12,
      maxZoom :17
  };
  var linestyle = {
      color: 'red', 
      weight: 2, 
      opacity: 0.5,
      smoothFactor: 1
  };  
  var dotIcon = L.icon({
      iconUrl: rootdir+'dot.png',
      iconSize: [14, 14],
      iconAnchor: [7, 7],
      shadowUrl: null
  });
  
  /* LOAD INITIAL MAP LAYERS */
  var map = L.map('map', {minZoom: config.minZoom, maxZoom: config.maxZoom});
  map.addLayer(new L.TileLayer(config.tileUrl, {attribution: config.tileAttrib}));
  map.setView(config.initLatLng, config.initZoom);
  
  /* INITIALISE MAP FUNCTONALITY */    
  map.on('click', onMapClick);   
  
  /* LOAD GEOJSON VIA AJAX */
  var jsonpath = rootdir+"tracks/track1/geojson.json";   
  var geojason = {};
  $.getJSON(jsonpath, function(data) {
   	 var gps = data.track.points.gps;      
     var line = addline(gps, linestyle);
     var len = gps.length-1; 
     addmarker(gps[0], 10, linestyle);   
     addmarker(gps[len], 10, linestyle);  
     routeLines = [ L.polyline(gps) ];   
     moveme();  
  }); 
   

  /* LOOP AN ANIMATION ALONG SOME POLYLINES 
  *  Example usage:   
  *     var firstroute = [ [51.477757,-0.025247],[51.477749] ];  
  *     var secondroute = [ [51.477757,-0.025247],[51.477749] ]; 
  *     var routeLines = [ L.polyline(firstroute), L.polyline(secondroute) ];      
  *     moveme();
  */
  function moveme(){
    $.each(routeLines, function(i, routeLine) {
      var marker = L.animatedMarker(routeLine.getLatLngs(), {
        icon: dotIcon,
        autoStart: false,
        onEnd: function() {
          $(this._shadow).fadeOut();
          $(this._icon).fadeOut(100, function(){    
            map.removeLayer(this);     
            if(i==0) moveme(); 
          });
        }
      });
      map.addLayer(marker);
      $(marker._icon).hide().fadeIn(100, function(){
        marker.start();   
      });
    });   
  }
  
  /* PERFORM A FUNCTION WHEN THE MAP IS CLICKED 
  *  Example usage: 
  *     map.on('click', onMapClick);   
  */   
  function onMapClick(e) {  
  	return addpopup(e.latlng, "You clicked the map at " + e.latlng.toString())     
  }
  
  /* ADD A POPUP 
  *  Example usage: 
  *     var content = "A message <strong>to</strong> display.";
  *     addpopup([51.54193, 0.71995], content);
  */
  function addpopup(latlng, content) { 
    thispopup = L.popup();   
    return thispopup.setLatLng(latlng).setContent(content).openOn(map);
  } 
    
  /* ADD A DOT TO THE CURRENT MAP 
  *  Example usage: 
  *     var style = {color: 'red',fillColor: '#f03',fillOpacity: 0.5}
  *     var size = 5;  
  *     addmarker([51.54193, 0.71995], size, style);
  */
  function addmarker(latlng, size, style){
    return L.circle(latlng, size, style).addTo(map); 
  }  
  
  /* ADD A LINE TO THE CURRENT MAP 
  *  Example usage:
  *     var pointList = [[51.54193, 0.71995], [51.5454, 0.70081], [51.539, 0.71154]];
  *     var style = {color: 'red', weight: 3, opacity: 0.5,smoothFactor: 1};
  *     addline(pointList, style);
  */
  function addline(pointList, style){
    return new L.Polyline(pointList, style).addTo(map);  
  }  
  
})();
