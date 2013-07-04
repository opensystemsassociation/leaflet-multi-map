<?php

$str = '{  
    "points" : {
        "time":[****TIME****],
        "gps": [***GPS-TRACK***],
    }
}'; 
$data = readdirectory('livedevices');
print json_encode($data); 

function readdirectory($path){     
  $arr = array();
  $dh = '.';
  $realpath = realpath($path);
  $i=0;
  if ($handle = opendir($realpath)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != '.' and $file != '..' and filetype($path.'/'.$file) == 'file') {
      	$fullfilepath = $_SERVER['DOCUMENT_ROOT']."/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/livedevices/$file";
        $data = json_decode(file_get_contents($fullfilepath)); 
        $key = filemtime($fullfilepath);
        if(isset($arr['gps'][$key])) $key = $key+1;
        $arr['gps'][$key] = array($data[1], $data[2]);
        $arr['date'][$key] = $data[0];
        $arr['dname'][$key] = $data[3];
        $arr['modified'][$key] = $data[0];
      }
    }
    closedir($handle);
  } 
  return $arr;
}

?>