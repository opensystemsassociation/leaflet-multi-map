<?php
// SETUP VARS
// Which IP addresses have permission to delete tracks?
$IParray = array("217.44.120.135");

// Now lets sort permissions
$IP = $_SERVER['REMOTE_ADDR'];
if(in_array($IP, $IParray)){
  define("ISADMIN", true);
}else{
  define("ISADMIN", false);
}

// Give human names to the phones
$humanuuid = array(
  '287BE0FC-A3BC-4FE1-94C0-5A8FB42167FE' =>"Toms Iphone",
  '683c1fef2ba40248'=>"TK Android",
  'c8f95d649cd7addd'=>"GF Android",
  'aaf09baa1477949'=>"Grahams Android"
);

// Now on to the main bit
include_once "../utils.php";
$deleteme = lmm_checkPOSTGETvar("delete", null, "GET");
$editme = lmm_checkPOSTGETvar("edit", null, "GET");
$format = lmm_checkPOSTGETvar("format", "json", "GET");
$arr = array();
$arr = readdirectory('tracks', 'data.json', $arr);

// PAGE LOGIC
$output = "";
// Do we need to edit or delete a track?
$deleteoutput = "";
$editoutput = "";
if(ISADMIN){
  $format = "html";
  $deleteoutput = deletetrackdir($deleteme);
  $editoutput .= edittrack($editme);
}
// HTML or json output?
if( $format == "json" ) {
  // print a json array of all data files
  print json_encode($arr['list']);  
}else{
  $htmllists = nicehtmllist($arr, $humanuuid, $IParray);
  printpage($deleteoutput.$editoutput.$htmllists);
}

// FUNCTIONS
// Now check if we are deleting a track
function deletetrackdir($deleteme){
  $output = "";
  if(!is_null($deleteme)){
    $deletepath = $deleteme;
    $output .= "<div style=\"clear:both;\">";
    $output .= "Are you sure you want to delete this track?<br />";
    $output .= "<a href=\"?delete=$deletepath&confirm=yes\">Yes</a> ".$deletepath;
    $output .= "</div>";
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
  return $output;
}

// Create a form to edit a track
function edittrack($editme){
  $output = "";
  if(!is_null($editme)){
    // Check if we have new posted data
    $posted = lmm_checkPOSTGETvar("editjson", null, "POST");
    if(!is_null($posted)){
      $output .= "SAVEME";
      // Write to file
      $fullpath = realpath(dirname("."))."/$editme";
      $f = fopen($fullpath, "w") or $output .= "Can't edit file";
      if(file_exists($fullpath)){
        $output .= "SAVEME";
        fwrite($f, $posted);
      }
      fclose($f);
    }
    // Build the edit form
    $submiturl = $_SERVER['SCRIPT_NAME']."?format=html&edit=$editme";
    $output .= "<form id=\"editform\" action=\"$submiturl\" method=\"post\">";
    $output .= '<textarea id="editjson" name="editjson">'.file_get_contents($editme).'</textarea>';
    $output .= "<input type=\"submit\" name=\"submit\" value=\"Save changes (Be very Carefull!!!)\">";
    $output .= "<form>";
  }
  return $output;
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

// Generate a nice set of html lists
function nicehtmllist($arr, $humanuuid, $IParray){
  // setup vars
  $output = "";
  $rooturl = str_replace('/map-tracks/tracklisting.php', '', $_SERVER['SCRIPT_NAME']); 

  // Loop through the array of files
  foreach($arr['orderd'] as $key=>$item){
    sort($arr['orderd'][$key]);
    rsort($arr['orderd'][$key]);
    $uuid = $key;
    if(isset($humanuuid[$key])) $uuid = $humanuuid[$key];
    $output .= "<div class=\"box\"><h3>$uuid</h3>";
    $output .=  '<ol>';

    // Loop through each track 
    foreach($arr['orderd'][$key] as $datafile){

      // Lets sus the urls
      $mapurl = $rooturl."?q=map-tracks&uuid=$key&title=$datafile";
      $jsonurl = $rooturl."/map-tracks/tracks/$key/$datafile/data.json";
      $editjsonurl = $_SERVER['SCRIPT_NAME']."?format=html&edit=tracks/$key/$datafile/data.json";
      $deleteurl = "tracks/$key/$datafile";

      // Lets get the tagname
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

      // Now prep the links
      $output .=  "<li>";
      if(ISADMIN) $output .= "<a class=\"delete\" href=\"?delete=$deleteurl\">[x]</a>";
      $output .=  " <a href=\"$mapurl\">";
      $output .=  "<span>$date</span>"; 
      $output .=  " <span>$time</span>"; 
      $output .=  "</a>";
      $output .=  " <span class=\"tag\">$tag</span>"; 
      $output .=  " <a class=\"jsonlink\" href=\"$jsonurl\">json</a> "; 
      if(ISADMIN) $output .=  "<a class=\"jsonlink\" href=\"$editjsonurl\">[e]&nbsp;</a>"; 
      $output .=  "</li>";
    }
    $output .=  "</ol></div>";    
  }
  $output .=  '<div class="footer">IP: '.$_SERVER['REMOTE_ADDR'].'</div>';
  return $output;
}

// Print an HTML page with links
function printpage($content){ ?>

  <!DOCTYPE html>
  <head>
    <title>List of all tracks</title>
    <style>
      body{font-family:verdana;padding:10px;font-size:13px;}
      ol{margin:0px;padding:0px;margin-right:5px;}
      li{list-style:none;border-bottom:1px solid #ccc;}
      a{text-decoration:none}
      a.jsonlink{text-decoration:none;color:#ccc;font-size:0.7em;float:right;}
      .tag{font-size:0.9em;font-color:#555;}
      .box{float:left;width:24%;}
      .footer{clear:both;border-top:20px solid #fff;margin-top:20px;color:#ccc;}
      .delete{color:red;font-size:0.7em;}
      #editjson{width:100%;height:400px;}
      #editform{clear:both;}
      #editform input{width:100%;}
    </style>
  </head>
  <body>
  <?php print $content; ?>
  </body>
  </html>

<? }; ?>