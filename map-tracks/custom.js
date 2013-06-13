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

        /* INITIALISE MAP CLICK FUNCTONALITY */    
        map.on('click', onMapClick);   

        addData(data);

    };

    function addData(data) {

        var layers = { shake : {} };

        var gps = data.track.points.gps;
        var gps_timestamp = data.track.points.gps_timestamp;
        var dist = data.track.points.dist;
        var line = addline(gps, redlinestyle);
        var len = gps.length-1;
        var pointcounter = {images:0,  messages:0};
        var strings = {images : "",  messages : ""};
        var imagelist = [];
        var layersConfig = [
            { type : "shakeevent",  displayname : "Shake Events", icon : "" }
        ];
        var nn = layersConfig.length;


        while( nn-- ){
            var lc = layersConfig[nn],
                lg = new L.layerGroup();

            for (var i = 0; i < gps.length; i++) {
                // DISPLAY BUMP EVENTS
                
                if(lc.type==undefined){
                    $("#info").append(i+":"+lc.type);
                }
                   var gpspoint = gps[i],
                        layerData = data.track.points[lc.type][i];
                    if( lc.hasOwnProperty( "icon" ) && lc.icon != "" ) {
                        lg.addLayer( new L.marker(gpspoint, {icon: new L.icon()}) );
                    } else {
                        lg.addLayer( new L.CircleMarker(gpspoint, { radius: layerData }) );
                    }
                //}
                

                // LOAD IMAGES
                if(data.track.points.image[i]!=0){
                    pointcounter.images++;
                    var url = jsonpath =  rootdir + "tracks/" + QueryString.uuid + "/" + QueryString.title + "/dwebimages/thumbs/"+data.track.points.image[i];
                    var mystr = '<img src="'+url+'" />';
                    strings.images += mystr;
                    imagelist.push(mystr);
                }
                /* LOAD MESSAGES
                if(data.track.points.messages!=undefined || data.track.points.messages!=NaN){
                    if(data.track.points.messages[i]!=0){
                        pointcounter.messages++;
                        strings.messages += data.track.points.messages[i];
                    }
                }*/
            }
            // Write the images
            $("#allimages").html("<strong>"+pointcounter.images+" images</strong><br />"+strings.images);
            // Create an image animation
            animateimages(imagelist);
            // Write the messages
            $("#messages").html("<strong>"+pointcounter.messages+" messages</strong><br />"+strings.messages);

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
        //moveme();  

    }

    /* CREATE ANIMATION FROM A DIV FULL OF IMAG
     */
    function animateimages(imagelist){
        // Create a new div to animate in
        var imgcount = 0;
        setInterval(function(){
            imgcount++;
            if(imgcount < imagelist.length){
                $("#imageanimation").html(imagelist[imgcount]);
            }else{
                imgcount=0;
            }
        }, 125);
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
