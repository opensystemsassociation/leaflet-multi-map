var gps = {
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "geometry": {
                "type": "LineString",
                "coordinates": [ [51.54407, 0.70926],[51.84407, 0.70926] ]
            },
            "properties": {
                "popupContent": "Some in formation about this track.",
                "timestamp": [123456788, 1234567888]
  		"speed": [123456788, 1234567888]
		"direction": [123456788, 1234567888]
            }
        }]
    }
};

var images = {
    "type": "FeatureCollection",
    "features": [
        {
            "geometry": { "type": "Point", "coordinates": [51.54719, 0.69544] },
            "type": "Feature",
            "properties": { 
		 "filename": "name.jpg", 
		 "timestamp": 124345566
  	    }
        },
        {
            "geometry": { "type": "Point", "coordinates": [51.58719, 0.69544] },
            "type": "Feature",
            "properties": { 
                 "filename": "name2.jpg", 
                 "timestamp": 124345566 
            }
        }
    ]
};

var accelerometer = {
    "type": "FeatureCollection",
    "features": [
        {
            "geometry": { 
		"type": "LineString", 
		"coordinates": [ [51.54719, 0.69544], [51.54719, 0.69544], [51.54719, 0.69544] ]  
	    },
            "type": "Feature",
            "properties": { 
                "info": "Data is saved as: [timestamp, X, Y, Z]",
		"timestamp-value": [ [1234567, 1,3,4], [123456789, 1,3,4], [123456789, 1,3,4] ] 
 	    },
         }
    ]
};

var light = {
    "type": "FeatureCollection",
    "features": [
        {
            "geometry": { 
                "type": "Linestring", 
                "coordinates": [ [51.54719, 0.69544], [51.54719, 0.69544], [51.54719, 0.69544] ]
            },  
            "type": "Feature",
            "properties": { 
                "info": "Data is saved as: [timestamp, lightlevel]",
                "timestamp-value": [ [1234567, 500], [123456789, 1000], [123456789, 500] ]
            },   
         }
    ]
};



