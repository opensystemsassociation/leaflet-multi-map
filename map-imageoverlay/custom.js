(function(){ 
  
  /* SETUP VARIABLES  */  
  var config = {
      tileUrl : 'http://{s}.tile.osm.org/{z}/{x}/{y}.png',   
      tileAttrib : 'Map tiles &copy; Development Seed and OpenStreetMap ',
      initLatLng : new L.LatLng(51.44713, -0.11153),    // LON   
      initZoom : 16,
      minZoom : 10,
      maxZoom :18
  };
  var map = L.map('map', {minZoom: config.minZoom, maxZoom: config.maxZoom});
  map.addLayer(new L.TileLayer(config.tileUrl, {attribution: config.tileAttrib}));
  map.setView(config.initLatLng, config.initZoom);
  
  /* PERFORM A FUNCTION WHEN THE MAP IS CLICKED */  
  var popup = L.popup();     
  function onMapClick(e) {  
  	popup
  		.setLatLng(e.latlng)
  		.setContent("You clicked the map at " + e.latlng.toString())
  		.openOn(map);     
  }
  map.on('click', onMapClick);
  
  /* OVERLAY AN IMAGE  */   
  var imageUrl = 'map-imageoverlay/cress.png',
      imageBounds = [[51.44948, -0.11431], [51.44452, -0.10823]],
      opacity = 0.8;
  L.imageOverlay(imageUrl, imageBounds, opacity).addTo(map).setOpacity( opacity );  
  
  /* ADD A DOT WITH POPUP  */    
  var circle = L.circle([51.44667, -0.1119], 5, {
      color: 'red',
      fillColor: '#f03',
      fillOpacity: 0.5
  }).addTo(map);
  circle.bindPopup('<img src="http://cressinghamgardens.org.uk/sites/cressinghamgardens.org.uk/files/styles/medium/public/page-image/walkway.jpg">');

  
})();