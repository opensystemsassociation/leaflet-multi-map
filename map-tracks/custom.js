(function(){ 
    /* SETUP paper.js canvas */
    $("#graph").html('<canvas id="myCanvas" style="border 1px solid #ccc" resize> </canvas>');
    var canvas = document.getElementById('myCanvas');
    paper.setup(canvas);

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
    //Initialise the animation html
    animateimages("load", []);

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
        var layers = { 
            shake : {} 
        };
        var gps = data.track.points.gps;
        var gps_timestamp = data.track.points.gps_timestamp;
        var dist = data.track.points.dist;
        var len = gps.length-1;
        var pointcounter = {
            images:0,  
            messages:0
        };
        var strings = {
            images : "",  
            messages : "",
            IOIOlight : ""
        };
        var imagelist = {
            urls:[], 
            loaded:[]
        };
        var layersConfig = [
            { type : "shakeevent",  displayname : "Shake Events", icon : "" },
        ];
        var nn = layersConfig.length;
        // ADD LAYERS TO THE MAP
        while( nn-- ){
            // Prep layer vars
            var lc = layersConfig[nn];
            var lg = new L.layerGroup();
            var smoothObj = new smooth(20);
            var distThreshold = 1.0;
            var weirdpoints = [];
            var cleangps = [];
            console.log("Pos:402 "+gps[402]+" Pos:409 "+gps[409]);
            // LOOP THROUGH ALL THE DATA POINTS
            for (var i = 0; i < gps.length; i++) {
                // PREP GPS VARS AND CHUCK WEIRDNESS
                console.log(i);
                var gpspoint = gps[i];
                if(i>0){
                    var dist = distance(gps[i][0], gps[i][1], gps[i-1][0], gps[i-1][1]);
                    var avdist = smoothObj.calc(dist);
                    // Testing weird GPS exceptions 
                    /*console.log(dist+" lat1:"+gps[i]+" lng1:"+gps[i].lng+" lat2:"+gps[i-1].lat+" lng2:" +gps[i-1].lng);
                    if(avdist>=1.0 || avdist<-1.0 || avdist==0.0000){
                        weirdpoints.push(i); 
                    }else{
                        if(i<=409){
                            cleangps.push(gpspoint); 
                            console.log("Pos:"+i+" Ave dist: "+avdist+" lat:"+gps[i][0]);
                        }
                    } */
                    cleangps.push(gpspoint); 
                    
                    //}
                }else{
                    cleangps.push(gpspoint);
                }
                // DISPLAY BUMP EVENTS
                if(dist<=distThreshold){
                    var layerData = data.track.points[lc.type][i];
                    if( lc.hasOwnProperty( "icon" ) && lc.icon != "" ) {
                        lg.addLayer( new L.marker(gpspoint, {icon: new L.icon()}) );
                    } else {
                        lg.addLayer( new L.CircleMarker(gpspoint, { radius: layerData }) );
                    }     
                }           
                // GENERATE IMAGE HTML
                if(data.track.points.image[i]!=0){
                    var url = rootdir + "tracks/" + QueryString.uuid + "/" + QueryString.title + "/dwebimages/thumbs/"+data.track.points.image[i];
                    var mystr = '<img id="i'+pointcounter.images+'" src="'+url+'" ref="'+data.track.points.image[i]+'" />';
                    strings.images += mystr;
                    imagelist.urls.push(url);
                    pointcounter.images++;
                }
                // LOAD MESSAGES
                //if(data.track.points.messages!=undefined || data.track.points.messages!=NaN){
                //    if(data.track.points.messages[i]!=0){
                //        pointcounter.messages++;
                //        strings.messages += data.track.points.messages[i];
                //    }
                //}
            }         
            console.log(weirdpoints);

            // Add line to the map
            var line = addline(cleangps, redlinestyle);  
            // Add layer to map.
            lg.addTo( map );
            // Create layer control config...
            var lcConfig = {};
            lcConfig[lc.displayname] = lg;
            // Add controls for layer.
            L.control.layers(null, lcConfig ).addTo( map );
        }

        // LETS BUILD THE PAGE
        // Create an image animation
        $("#allimages").html( strings.images );   
        animateimages("run", imagelist);

        // Draw the graph
        drawcanvasgraphs(data.track.points);
        //htmlgraph(data.track.points.IOIOlight);

        // Write the messages
        //$("#messages").html("<strong>"+pointcounter.messages+" messages</strong><br />"+strings.messages);

        // Draw the map
        map.setView(gps[0], config.initZoom);
        addmarker(gps[0], 10, redlinestyle);   
        addmarker(gps[len], 10, redlinestyle);  
        routeLines = [ L.polyline(gps) ];   
        //moveme();  
    }

    // Draw a pure html graph
    function htmlgraph(points){
        var str = "";
        for (var i = 0; i < points.length; i++) {
            var ioid = 'io'+i;
            var x = i*(100/points.length);
            var y = points[i]*100+10;
            var css = 'border:1px solid red;left:'+x+'%;top:'+y+'%;width:1px;height:1px;position:absolute;';
            var mystr = '<div class="p" id="'+ioid+'" style="'+css+'"></div>';
            str += mystr;
        }
        $("#graph").html("<strong>"+i+" points</strong><div id=\"gIOIO\">"+str+"</div>");
        $("#graph #gIOIO").css({'border':'1px solid #ccc', 'height':'100%'});
    }

    // Draw the graph in a canvas element
    function drawcanvasgraphs(points){
        // Dont draw the graph if there isn't any
        if(points.IOIOlight===undefined) return;
        // Initial vars
        var borderleft = 30;
        var cw = $("#myCanvas").width()-(borderleft+10);
        var xInc = cw/points.IOIOlight.length;
        var ch = $("#graph").height()-20;
        var scale = 1;

        // Prep GSR graph variables
        var colGsr = 'green';
        var maxYgsr = 0;
        var minYgsr = 0;
        var pathGSR = new paper.Path();
        pathGSR.style = { strokeColor: colGsr,strokeWidth: 1 };

        // Light vars
        var colLight = 'red';
        var maxYlight = 0;
        var minYlight = 0;
        var pathLight = new paper.Path();
        pathLight.style = { strokeColor: colLight,strokeWidth: 1 };

        // Accelleromter X variables
        var colAcellx = 'grey';
        var maxAccelx = 0;
        var minAccelx = 0;
        var pathAccelx = new paper.Path();
        pathAccelx.style = { strokeColor: colAcellx, strokeWidth: 1 };

        // Accelleromter Y variables
        var colAcelly = 'grey';
        var maxAccely = 0;
        var minAccely = 0;
        var pathAccely = new paper.Path();
        pathAccely.style = { strokeColor: colAcelly, strokeWidth: 0.5 };

        // Accelleromter Z variables
        var colAcellz = 'gey';
        var maxAccelz = 0;
        var minAccelz = 0;
        var pathAccelz = new paper.Path();
        pathAccelz.style = { strokeColor: colAcellz, strokeWidth: 0.5};

        // Now loop through the points and draw
        for (var i = 0; i < points.IOIOgsr.length; i++) {
            var x = (i*xInc);
            // GSR
            var y = ch-(ch*points.IOIOgsr[i]);
            if(y>maxYgsr || i==0) maxYgsr = y;
            if(y<minYgsr || i==0) minYgsr = y;
            pathGSR.add(new paper.Point(x, y));
            // LIGHT;
            var y = ch-(ch*points.IOIOlight[i])*scale;
            if(y>maxYlight || i==0) maxYlight = y;
            if(y<minYlight || i==0) minYlight = y;
            pathLight.add(new paper.Point(x, y));
            // ACCELLEROMETER
            var accelS = points.accelerometer[i].split(":");
            var yx = parseFloat(accelS[0]);
            var yy = parseFloat(accelS[1]);
            var yz = parseFloat(accelS[2]);
            yx = ch-(ch*yx);
            yy = ch-(ch*yy);
            yz = ch-(ch*yz);
            //if(y>maxAccelx || i==0) maxYaccel = y;
            //if(y<minYaccel || i==0) minYaccel = y;
            pathAccelx.add(new paper.Point(x, yx));
            pathAccely.add(new paper.Point(x, yy));
            pathAccelz.add(new paper.Point(x, yz));
        }

        // SHIFT POSITIONS TO ALLOW FOR LABELING
        var labelx = 6;
        // Now postion GSR
        pathGSR.position.x += borderleft;
        var tpos = pathGSR._segments[0]._point._y+6; 
        var textGsr = new paper.PointText({point: [labelx, tpos], content: 'gsr',fillColor:colGsr,fontSize: 12});

        // Now postion LIGHT
        pathLight.position.x += borderleft;
        var tpos = pathLight._segments[0]._point._y+6; 
        var textLight = new paper.PointText({point: [labelx, tpos], content: 'ldr',fillColor:colLight,fontSize: 12});

        // ACCELEROMETER;
        pathAccelx.position.y = 40;
        pathAccelx.position.x += borderleft;
        pathAccelx.scale(1,0.02);
        pathAccely.position.y = 40;
        pathAccely.position.x += borderleft;
        pathAccely.scale(1,0.03);
        pathAccelz.position.y = 40;
        pathAccelz.position.x += borderleft;
        pathAccelz.scale(1,0.03);
        var tpos = pathAccelx._segments[0]._point._y+6; 
        var textAccel = new paper.PointText({point: [labelx, tpos], content: 'xyz',fillColor:colAcellx,fontSize: 12});

        //console.log('maxYaccel:'+maxYaccel+' minYaccel:'+minYaccel);
        //console.log('rangedminY:'+range(maxYaccel, minYaccel, ch, 0, minYaccel) );
        //console.log('rangedmaxY:'+range(maxYaccel, minYaccel, ch, 0, maxYaccel) );
        // Draw the path
        //path.fullySelected = false;
        //pathGSR.smooth();
        paper.view.draw();
        return paper;
    }
    function drawcanvastimeline(index){

    }

    // Convert a varable from one range to another 
    // i.e an 'int 50' in a range of 1-100 converts to an 'int 5' in a range of 1-0
    function range(oldMax, oldMin, newMax, newMin, value){
        OldRange = (oldMax - oldMin)  
        NewRange = (newMax - newMin)  
        return (((value - oldMin) * NewRange) / OldRange) + newMin
    }


    /* CREATE ANIMATION FROM DIV FULL OF IMAGES
     */
    function animateimages(func, imagelist){
        if(func=="load"){
            // Create a new div to animate in
            var transurl = basedir+'trans.png';
            $("#imageanimation").html('<div class="animation"><img src="'+transurl+'" /><div class="info"></div><div class="msg"></div></div>');
            $("#imageanimation .animation").css({'background-repeat': 'no-repeat', 'background-image':'#ccc'});
        }else{
            var i = 0;
            var looped = 0;
            var paused = 0;
            // Set markers so we know if an image has loaded or not
            $('#allimages img').data('status',0);
            $('#allimages img').error(function() {
                //alert('Image not loaded');
                $(this).attr("src", basedir+'trans.png').load(function(){
                    $(this).data("status", 1);
                });
                $(this).data("status", 1);
            });
            $('#allimages img').load(function() {
                $(this).data("status", 2);
            });
            // Now loop through display of images
            setInterval(function(){
                if(i < imagelist.urls.length){
                    var iid = "#i"+i;
                    var url = $(iid).attr('src');
                    var fn = url.split("/");
                    var fnn = fn[fn.length-1];
                    // Only show if an image has loaded
                    if($(iid).data("status")==2){
                        $("#imageanimation .animation img").css({'background-image': 'url("'+url+'")'});
                        $("#imageanimation .animation .info").html($(iid).ref+" "+fnn);
                    }
                }else{
                    i=0;
                    looped=1;
                }
                i++;
            }, 240);
        }
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

    /* ADD A POPUPs l
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

    // calculate the distance between GPS points
    function distance(lat1, lon1, lat2, lon2) {
        //Radius of the earth in:  1.609344 miles,  6371 km  | var R = (6371 / 1.609344);
        var R = 3958.7558657440545; // Radius of earth in Miles 
        var dLat = toRad(lat2-lat1);
        var dLon = toRad(lon2-lon1); 
        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
        var d = R * c;
        return d;
    }
    function toRad(Value) {
        /** Converts numeric degrees to radians */
        return Value * Math.PI / 180;
    } 

    // Class to smooth sensor data
    function smooth(numReadings){
        this.numReadings = numReadings;
        this.readings = [];
        this.index = 0;
        this.total = 0.0;          
        this.average = 0.0; 
        i = this.numReadings; 
        while (i--) this.readings[i] = 0;   
        this.calc = function(newvar){
            this.total = this.total - this.readings[this.index]; // Subtract the last reading  
            this.readings[this.index] = newvar;                  // Add the new reading
            this.total = this.total + this.readings[this.index];           // Add the reading to the total    
            this.index = this.index + 1;                         // Advance to the next position in the array                 
            if(this.index >= this.numReadings) this.index = 0;   // Wrap around to the beginning                   
            var average = this.total / this.numReadings;         // Calculate the average
            return  average.toFixed(5);  // round the float to 5 decimal places
        }
    }

})();
