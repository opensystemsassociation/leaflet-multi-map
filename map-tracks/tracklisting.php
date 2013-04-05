<?php
$arr = array();
$arr = readdirectory('tracks', 'data.txt', $arr);
print json_encode($arr);  
  
// Recursivly search a directory structure for files named 'data.txt'
function readdirectory($path, $searchfor, $arr, $lvl=0){  
  $path = realpath($path);
  $oldpath = $path;
  if ($handle = opendir($path)) {
    while (false !== ($ref = readdir($handle))) {
      if ($ref != '.' and $ref != '..') {
        $newpath = $path.'/'.$ref;
        if(is_dir($newpath)){
          $arr = readdirectory($newpath, $searchfor, $arr);
        }else{
          $savename = explode('/',$newpath);
          $cnt = count($savename);
          if($ref==$searchfor) $arr[] = $savename[$cnt-3].'/'.$savename[$cnt-2].'/'.$savename[$cnt-1];
        }
      }
    }
    closedir($handle);
  } 
  return $arr;
}

?>