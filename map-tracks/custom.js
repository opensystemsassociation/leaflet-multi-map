(function(){ 

    /* SETUP VARIABLES  */  
    var defaults = false;
    if(dlat=='' || dlng=='') {
        dlat = 51.4458016;
        dlng = -0.1119497;
        defaults = true;
    }

    var rootdir = basedir+"map-tracks/",
        routeLines,
        map;
    var config = {
        tileUrl : 'http://{s}.tiles.mapbox.com/v3/openplans.map-g4j0dszr/{z}/{x}/{y}.png',
        tileAttrib : 'Map tiles: OpenStreetMap ',
        initLatLng : new L.LatLng(dlat, dlng),      
        initZoom : 15,
        minZoom : 12,
        maxZoom :17
    };
    var redlinestyle = {
        color: 'red', 
        weight: 2, 
        opacity: 0.5,
        smoothFactor: 1
    };  
    var bluelinestyle = {
        color: 'blue', 
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

    /* LOAD TRACK FILENAMES VIA AJAX */  
    var jsonpath =  rootdir + "tracks/" + QueryString.uuid + "/" + QueryString.title + "/data.json";
    $.getJSON(jsonpath, initMap);
    /* LOAD INITIAL MAP LAYERS */
    
    function initMap(data) {

        var gpsStart = data.track.points.gps[0];
        config.initLatLng = new L.LatLng(gpsStart[0], gpsStart[1]);

        map = L.map('map', {minZoom: config.minZoom, maxZoom: config.maxZoom});
        map.addLayer(new L.TileLayer(config.tileUrl, {attribution: config.tileAttrib}));
        map.setView(config.initLatLng, config.initZoom);

        /* INITIALISE MAP FUNCTONALITY */    
        map.on('click', onMapClick);   

        addData(data);

    };

    function addData(data) {

        var layers = { shake : {} };

        var gps = data.track.points.gps;
        var line = addline(gps, redlinestyle);
        var len = gps.length-1; 

        var layersConfig = [
            { type : "shakeevent",  displayname : "Shake Events", icon : "" }
        ];

        var i = layersConfig.length;
        while( i-- ){
            var lc = layersConfig[i],
                lg = new L.layerGroup();
            for (var i = 0; i < gps.length; i++) {
                var gpspoint = gps[i],
                    layerData = data.track.points[lc.type][i];
                // Create marker and add to layer.
                if( lc.hasOwnProperty( "icon" ) && lc.icon != "" ) {
                    lg.addLayer( new L.marker(gpspoint, {icon: new L.icon()}) );
                } else {
                    lg.addLayer( new L.CircleMarker(gpspoint, { radius: layerData }) );
                }

            }
            // Add layer to map.
            lg.addTo( map );
            // Create layer control config...
            var lcConfig = {};
            lcConfig[lc.displayname] = lg;
            // Add controls for layer.
            L.control.layers(null, lcConfig ).addTo( map );
        }

        map.setView(gps[0], config.initZoom);
        addmarker(gps[0], 10, redlinestyle);   
        addmarker(gps[len], 10, redlinestyle);  
        routeLines = [ L.polyline(gps) ];   
        moveme();  

    }

    /* LOOP AN ANIMATION ALONG SOME POLYLINES 
     *  Example usage:   
     *     var firstroute = [ [51.477757,-0.025247],[51.477749] ];  
     *     var secondroute = [ [51.477757,-0.025247],[51.477749] ]; 
     *     var routeLines = [ L.polyline(firstroute), L.polyline(secondroute) ];      
     *     moveme();
     */
    function moveme(){
        $.each(routeLines, function(i, routeLine) {
	        var latlon = routeLine.getLatLngs(); 
            latlon = gpssmooth();
            var marker = L.animatedMarker(latlon, {
                icon: dotIcon,
                autoStart: false,
                onEnd: function() {
                    //$(this._shadow).fadeOut();
                    //$(this._icon).fadeOut(100, function(){    
                    //map.removeLayer(this);     
                    //if(i==0) moveme(); 
                    //});
                }
            });
            map.addLayer(marker)
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
        return thispopup.setLatLng(latlng).setContent(content).addTo(map);
    } 

    /* ADD A POPUP 
     *  Example usage: 
     *     var content = "A message <strong>to</strong> display.";
     *     addpopup([51.54193, 0.71995], content);
     */
    function addclosingpopup(latlng, content) { 
        thispopup = L.popup();   
        return thispopup.setLatLng(latlng).setContent(content).openOn(map);
    } 

    /* ADD A POPUP BOUND TO A MARKER
     *  Example usage: 
     *     var content = "A message <strong>to</strong> display.";
     *     addpopup([51.54193, 0.71995], content);
     */
    function addboundpopup(marker, content) {   
        return marker.bindPopup(content).openPopup();
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

    /* GPS FILTER */
    function gpssmooth(latlng){
        return latlng;
    }

    function karmelfilter(){
        
    }

})();
