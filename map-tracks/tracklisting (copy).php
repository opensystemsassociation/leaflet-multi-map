<?php
// Which IP addresses can delet tracks?
$IParray = array("217.44.120.135");

// Give human names to the phones
$humanuuid = array(
  '287BE0FC-A3BC-4FE1-94C0-5A8FB42167FE' =>"Toms Iphone",
  '683c1fef2ba40248'=>"TK Android",
  'c8f95d649cd7addd'=>"GF Android",
  'aaf09baa1477949'=>"Grahams Android"
);

// Now on to the main bit
include_once "../utils.php";
$format = lmm_checkPOSTGETvar("format", "json", "GET");
$arr = array();
$arr = readdirectory('tracks', 'data.json', $arr);

// Now check if we are deleting a track
if(isset($_GET['delete'])){
  $format = "html";
  $deletepath = $_GET['delete'];
  print "<div style=\"clear:both;\">";
  print "Are you sure you want to delete this directory?<br />";
  print "<a href=\"?delete=$deletepath&confirm=yes\">Yes</a> ".$deletepath;
  print "</div>";
  if(isset($_GET['confirm'])){
    $realpath = realpath($deletepath);
    if (is_dir($realpath)){
      $url = "http://".$_SERVER['SERVER_NAME'].'/'.$_SERVER['SCRIPT_NAME']."?format=html";
      $files = glob($realpath.'/*'); // get all file names
      foreach($files as $file){ // iterate files
        if(is_file($file))
          unlink($file); // delete file
      }
      rmdir($realpath);
      header("Location: $url");
    }
  }
}

  
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
          if($ref==$searchfor){
            $arr['list'] = $savename[$cnt-3].'/'.$savename[$cnt-2].'/'.$savename[$cnt-1];
            $arr['orderd'][$savename[$cnt-3]][] = $savename[$cnt-2];
          }
        }
      }
    }
    closedir($handle);
  } 
  return $arr;
}

function niceprintout($arr, $humanuuid, $IParray){
  // Check if we can delete folders
  $IP = $_SERVER['REMOTE_ADDR'];
  if(in_array($IP, $IParray)) $deletelink=true;

  // Print a nice easy view list
  $rooturl = str_replace('/map-tracks/tracklisting.php', '', $_SERVER['SCRIPT_NAME']); 
  // Loop through the array of files
  foreach($arr['orderd'] as $key=>$item){
    sort($arr['orderd'][$key]);
    rsort($arr['orderd'][$key]);
    $uuid = $key;
    if(isset($humanuuid[$key])) $uuid = $humanuuid[$key];
    print "<div class=\"box\"><h3>$uuid</h3>";
    print '<ol>';
    foreach($arr['orderd'][$key] as $datafile){
      // Lets sus the urls
      $mapurl = $rooturl."?q=map-tracks&uuid=$key&title=$datafile";
      $jsonurl = $rooturl."/map-tracks/tracks/$key/$datafile/data.json";
      $deleteurl = "tracks/$key/$datafile";
      // Now lets get the tagname
      $tag = "";
      $jsonfilepath ="tracks/$key/$datafile/data.json";
      $filejson = json_decode(file_get_contents($jsonfilepath));
      if(isset($filejson->track)){
        if(isset($filejson->track->tag)){
          $tag = $filejson->track->tag;
        }
      }
      
      // Lets work out the date/time
      $timedate = explode('-',$datafile);
      if($timedate[0]=='AF') array_shift($timedate);
      $date = $timedate[2].'/'.$timedate[1].'/'.$timedate[0];
      $time  = date("g:ia", strtotime($timedate[3].':'.$timedate[4].':'.$timedate[5]));
      //$date = $datafile;
      // Now print the links
      print "<li>";
      if($deletelink=true) print "<a class=\"delete\" href=\"?delete=$deleteurl\">[x]</a>";
      print " <a href=\"$mapurl\">";
      print "<span>$date</span>"; 
      print " <span>$time</span>"; 
      print "</a>";
      print " <span class=\"tag\">$tag</span>"; 
      print " <a class=\"jsonlink\" href=\"$jsonurl\">json</a>"; 
      print "</li>";
    }
    print "</ol></div>";    
  }
  print '<div class="footer">IP: '.$IP.'</div>';

}

if( $format == "json" ) {
  // print a json array of all data files
  print json_encode($arr['list']);  

}else{
  // Print an HTML page with links
?>

<!DOCTYPE html>
<head>
  <title>List of all tracks</title>
  <style>
    body{font-family:verdana;padding:10px;}
    ol{margin:0px;padding:0px;margin-right:5px;}
    li{list-style:none;border-bottom:1px solid #ccc;}
    a{text-decoration:none}
    a.jsonlink{text-decoration:none;color:#ccc;font-size:11px;float:right;}
    .tag{font-size:14px;font-color:#999;}
    .box{float:left;width:24%;}
    .footer{clear:both;border-top:20px solid #fff;margin-top:20px;color:#ccc;}
    .delete{color:red;}
  </style>
</head>
<body>
<?php niceprintout($arr, $humanuuid, $IParray); ?>
</body>
</html>

<? }; ?>