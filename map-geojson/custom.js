// This demo is based off of cibi.me by OpenPlans and my earlier visualization
// at http://github.com/openplans/cibi_animation

(function(){ 
  /* SETUP VARIABLES  */  
  var dotIcon = L.icon({
      iconUrl: 'map-animatedpaths/dot.png',
      iconSize: [19, 19],
      iconAnchor: [19, 19],
      shadowUrl: null
  });

  var config = {
      tileUrl : 'http://{s}.tiles.mapbox.com/v3/openplans.map-g4j0dszr/{z}/{x}/{y}.png',
      tileAttrib : 'Map tiles: OpenStreetMap ',
      initLatLng : new L.LatLng(51.54335, 0.71033),    // SOUTHEND  
      initZoom : 15,
      minZoom : 12,
      maxZoom :17
  };

  var map = L.map('map', {minZoom: config.minZoom, maxZoom: config.maxZoom});
     // routeLines = [  
     // L.polyline([[51.477766,-0.025259],[51.477757,-0.025247],[51.477749,-0.025233],[51.47775,-0.025266],[51.477744,-0.02531],[51.477744,-0.025358],[51.477745,-0.025398],[51.477748,-0.025419],[51.477746,-0.025437],[51.477746,-0.025452],[51.477745,-0.025476],[51.477742,-0.025505],[51.477733,-0.025529],[51.477728,-0.025558],[51.477727,-0.025578],[51.477721,-0.02561],[51.477721,-0.025645],[51.477722,-0.025691],[51.477718,-0.025743],[51.47772,-0.025779],[51.477717,-0.025811],[51.477712,-0.02589],[51.477713,-0.025916],[51.477719,-0.025943],[51.477728,-0.025987],[51.477739,-0.026006],[51.477772,-0.026031],[51.477784,-0.026043],[51.4778,-0.026048],[51.477817,-0.026046],[51.477839,-0.026039],[51.47786,-0.026022],[51.477876,-0.02601],[51.477895,-0.026004],[51.477933,-0.026],[51.477958,-0.026002],[51.477985,-0.026001],[51.478018,-0.026001],[51.478023,-0.025994],[51.478031,-0.025988],[51.478043,-0.025983],[51.478052,-0.025982],[51.478058,-0.025985],[51.478061,-0.025998],[51.478064,-0.026003],[51.478037,-0.026021],[51.47803,-0.026019],[51.478012,-0.026016],[51.477989,-0.02601],[51.477965,-0.025999],[51.477939,-0.025994],[51.47792,-0.025993],[51.4779,-0.025968],[51.477883,-0.025956],[51.477868,-0.025943],[51.477849,-0.025931],[51.477829,-0.025942],[51.477807,-0.025955],[51.477787,-0.025969],[51.47777,-0.025987],[51.47772,-0.026008],[51.477706,-0.025999],[51.477681,-0.025948],[51.477677,-0.025937],[51.477688,-0.025919],[51.477702,-0.025899],[51.477716,-0.025878],[51.477731,-0.025859],[51.477738,-0.025826],[51.477738,-0.025794],[51.477736,-0.025763],[51.477735,-0.025737],[51.477735,-0.025722],[51.477737,-0.025697],[51.477739,-0.025673],[51.477744,-0.025615],[51.477775,-0.025823],[51.47775,-0.025448],[51.477833,-0.025814],[51.477794,-0.02582]]),
       
       // L.polyline([[51.4786, -0.02287],[51.477983,-0.024849],[51.477655,-0.024967],[51.4786, -0.02287],[51.477983,-0.024849],[51.477655,-0.024967]]), 
       // L.polyline([[51.4776, -0.02387],[51.477983,-0.024749],[51.477755,-0.024867],[51.4776, -0.02277]]),   

     // ];

  map.addLayer(new L.TileLayer(config.tileUrl, {attribution: config.tileAttrib}));
  map.setView(config.initLatLng, config.initZoom);
  
  /* LOOP AN ANIMATION ALONG THE POLYLINES */ 
  function moveme(){
    $.each(routeLines, function(i, routeLine) {
      var marker = L.animatedMarker(routeLine.getLatLngs(), {
        icon: dotIcon,
        autoStart: false,
        onEnd: function() {
          $(this._shadow).fadeOut();
          $(this._icon).fadeOut(3000, function(){    
            map.removeLayer(this);   
            if(i==0) moveme(); 
          });
        }
      });
      map.addLayer(marker);
      $(marker._icon).hide().fadeIn(1000, function(){
        marker.start();
      });
    });   
  }
  //moveme();
  
  /* PERFORM A FUNCTION WHEN THE MAP IS CLICKED */  
  var popup = L.popup();     
  function onMapClick(e) {  
  	popup
  		.setLatLng(e.latlng)
  		.setContent("You clicked the map at " + e.latlng.toString())
  		.openOn(map);     
  }
  map.on('click', onMapClick);

  /* ADD A DOT */    
  var circle = L.circle([51.477766,-0.025259], 5, {
      color: 'red',
      fillColor: '#f03',
      fillOpacity: 0.5
  }).addTo(map); 
  
})();
