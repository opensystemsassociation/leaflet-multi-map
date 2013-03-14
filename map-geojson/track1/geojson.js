var detail = {
    "title": "A track name",
    "author": "Tom Keene",
    "start-time": "FeatureCollection",
    "end-time": "FeatureCollection",
    "device-uuid": "FeatureCollection"
}

var gps = {
    "type": "FeatureCollection",
    "features": 
	[{
            "type": "Feature",
            "geometry": {
                "type": "LineString",
                "coordinates": [ [51.54407, 0.70926],[51.84407, 0.70926] ]
            },
	    "properties": {
	        "elapsedtime": [1.66, 400.66]
                "speed": [88, 88]
                "direction": [12, 12]
            }	
        }]
    }
};

var images = {
    "type": "FeatureCollection",
    "features": 
	[{   
	    "type": "Feature",
            "geometry": { 
		 "type": "Point", 
		 "coordinates": [51.54719, 0.69544] 
            },
            "properties": { 
		 "filename": "name.jpg", 
		 "elapsedtime": 12
  	    }
        },{
            "type": "Feature",
            "geometry": { 
		 "type": "Point", 
		 "coordinates": [51.58719, 0.69544] 
   	    },
            "properties": { 
                 "filename": "name2.jpg", 
                 "elapsedtime": 124 
            }
        }]
};

var accelerometer = {
    "type": "FeatureCollection",
    "features": 
	 [{   
	    "type": "Feature",
            "geometry": { 
		"type": "LineString", 
		"coordinates": [ [51.54719, 0.69544], [51.54719, 0.69544], [51.54719, 0.69544] ]  
	    },
            "properties": { 
                "info": "Data is saved as: [elapsedtime, X, Y, Z]",
                "maxvalue": 255 
		"elapsedtime-value": [ [1234567, 1,3,4], [123456789, 1,3,4], [123456789, 1,3,4] ] 
 	    }
	 }]
};

var light = {
    "type": "FeatureCollection",
    "features": 
         [{      
            "type": "Feature",
            "geometry": {
                "type": "LineString",
                "coordinates": [ [51.54719, 0.69544], [51.54719, 0.69544], [51.54719, 0.69544] ]
            },
            "properties": {
                "info": "Data is saved as: [elapsedtime, lightlevel]",
                "maxvalue": 10000
                "elapsedtime-value": [ [12,500], [123, 1000], [125, 500] ]
            }
         }]
};

