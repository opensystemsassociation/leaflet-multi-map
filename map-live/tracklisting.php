<?php

$files = readdirectory('tracks');
print json_encode($files);  

function readdirectory($path){     
  $arr = array();
  $dh = '.';
  $path = realpath($path);
  if ($handle = opendir($path)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != '.' and $file != '..' and filetype($path.'/'.$file) == 'file') {
        $arr[] = $file; 
      }
    }
    closedir($handle);
  } 
  return $arr;
}

?>