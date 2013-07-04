<?php
$str = '{
    "track" : {
        "title": "A track name",
        "author": "Tom Keene", 
        "start-time": "000",
        "end-time": "000",
        "device" : {
            "name": "???",   
            "cordova": "???",
            "platform": "???",
            "version": "???",
            "uuid": "???"
        },  
        "maxvalues" : { 
          "accelerometer": 255, 
          "light"        : 10000    
        },    
        "points" : {
              "elapsed"         : [ [1],[6],[11],[16] ],
              "gps"             : [***GPS-TRACK***],
              "image-file"      : ["name2.jpg",0,0,"name2.jpg"],
              "accelerometer"   : [ [1,55,26], [1,55,26], 0, [1,55,26] ],
              "shakeevent-yn"   : [0,1,0,0]
        }
    }
}'; 
$file = $_GET['file']; 
$filename = "tracks/".$file; 
$content = file_get_contents($filename);      
$content = ltrim ($content,',');
print str_replace('***GPS-TRACK***', $content, $str);

?>