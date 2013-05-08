<?php
include_once "../utils.php";

// SETUP VARS
$deleteme = lmm_checkPOSTGETvar("delete", null, "GET");
$editme = lmm_checkPOSTGETvar("edit", null, "GET");
$images = lmm_checkPOSTGETvar("images", null, "GET");
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
  $editoutput = edittrack($editme);
  $imageoutput = renderimages($images);
  $imagehtml = $imageoutput['msg'].'<hr />'.imagehtml($imageoutput);
}
// HTML or json output?
if( $format == "json" ) {
  // print a json array of all data files
  print json_encode($arr['list']);  
}else{
  $htmllists = nicehtmllist($arr, $humanuuid, $IParray);
  printpage($deleteoutput.$editoutput.$htmllists.$imagehtml);
}

// FUNCTIONS
// Now check if we are deleting a track
function deletetrackdir($deleteme){
  $output = "";
  if(!is_null($deleteme)){
    $deletepath = $deleteme;
    $url = "http://".$_SERVER['SERVER_NAME'].'/'.$_SERVER['SCRIPT_NAME']."?format=html";
    $output .= "<div>";
    $output .= "<h3>Are you sure you want to delete this track?</h3><div>";
    $output .= "<a href=\"?delete=$deletepath&confirm=yes\" class=\"deletebut\">Yes?</a> ";
    $output .= "<a href=\"$url\" class=\"deletebut nobut\">Cancel</a> ";
    $output .= "</div></div>";
    if(isset($_GET['confirm'])){
      $realpath = realpath($deletepath);
      if (is_dir($realpath)){
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
    $url = "http://".$_SERVER['SERVER_NAME'].'/'.$_SERVER['SCRIPT_NAME']."?format=html";
    $output .= "<a href=\"$url\">[BACK]</a> ";

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
      $url = "http://".$_SERVER['SERVER_NAME'].'/'.$_SERVER['SCRIPT_NAME']."?format=html";
      header("Location: $url");
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

// Generate an HTML list of images
function imagehtml($track){  
  if(!isset($track['track']->track->points->image)) return "";
  $thumburl = $track['thumburl'];
  $output = "";
  $i = 0;
  foreach($track['track']->track->points->image as $image){
    //$output .= "$thumburl/$image";
    if($image!=0){
      $output .= "<img src=\"$thumburl/$image\" id=\"i$i\" />";
    }
    $i++;
  }
  return $output;
}

// Grab list of images from a directory
function renderimages($path){  
  if(is_null($path)) return "";
  // Prep vars
  $output = array();
  $throttle = 50; // make sure we only process # images at a time
  $i = 0;
  $relativeurl = str_replace('/tracklisting.php','',$_SERVER['SCRIPT_NAME']).'/'.$path;
  $thumburl = $relativeurl.'/dwebimages/thumbs';
  $path = realpath($path);
  $webimagedir = "$path/dwebimages";
  $webimagedirthumbs = "$path/dwebimages/thumbs";
  $webimagedirbigger = "$path/dwebimages/bigger";
  $trackvars = json_decode(file_get_contents("$path/data.json"));
  $output['track'] = $trackvars;
  $output['thumburl'] = $thumburl;
  $imagetotal = 0;
  $prepedimages = 0;
  if(isset($trackvars->track->imagerotate)) $rotate = $trackvars->track->imagerotate;
  else $rotate = 0;
  // Generate default directories
  if(!is_dir($webimagedir)) mkdir($webimagedir, 0775, true); // Recursive.
  if(!is_dir($webimagedirthumbs)) mkdir($webimagedirthumbs, 0775, true); // Recursive.
  if(!is_dir($webimagedirbigger)) mkdir($webimagedirbigger, 0775, true); // Recursive.
  // Now loop through the diretory
  if ($handle = opendir($path)) {
    while (false !== ($ref = readdir($handle))) {
      if ($ref != '.' and $ref != '..' and $ref!='data.json' and !is_dir($path.'/'.$ref)) {
        $imagefilepath = $path.'/'.$ref;
        $imgthumnailpath = $webimagedirthumbs.'/'.$ref;
        $arr[] = $imagefilepath;
        // If the thumbnail doesn't already exist, create it
        $thumbexists = is_file($imgthumnailpath);
        if($i<=$throttle && !$thumbexists){
          resizeimage($imagefilepath, $imgthumnailpath, $rotate, 200, 0); // set width to 200px and leave aspect ratio
          $i++;
        }else{
          $prepedimages ++;
        }
        $imagetotal ++;
        //if($thumbexists) print '<img src="'.$thumburl.'/'.$ref.'" />';
      }
    }
    closedir($handle);
  }
  $lefttorender =  $imagetotal-$prepedimages;
  // Loop through the 
  $output['imagestorender'] = $imagetotal;
  $output['msg'] = "<b>Images:</b> $imagetotal (To render:$lefttorender)";
  return $output; 
}

// Generate a new image at the specified directory can set just one w/h value & aspect ration is calculated.
function resizeimage($imagefilepath, $newimagefilepath, $rotate, $width=0, $height=0){  
  if(!is_file($imagefilepath)) return;
  if(!getimagesize($imagefilepath)) return; 
  $img = new Imagick($imagefilepath); 
  $img->setImageResolution(72,72); 
  $img->resampleImage(72,72,imagick::FILTER_UNDEFINED,1); 
  $img->scaleImage($width,$height); 
  if($rotate!=0) $img->rotateImage(new ImagickPixel(), $rotate);
  //$d = $img->getImageGeometry(); // $d['height']; 
  $img->writeImage($newimagefilepath); 
  $img->destroy(); 
}

// Generate a nice set of html lists
function nicehtmllist($arr, $humanuuid, $IParray){
  // setup vars
  $output = "<div class=\"tracklists\">";
  $rooturl = str_replace('/map-tracks/tracklisting.php', '', $_SERVER['SCRIPT_NAME']); 

  // Loop through the array of files
  foreach($arr['orderd'] as $key=>$item){
    sort($arr['orderd'][$key]);
    rsort($arr['orderd'][$key]);
    $uuid = $key;
    if(isset($humanuuid->$key)) $uuid = $humanuuid->$key;
    $output .= "<div class=\"box\"><h3 class=\"phoneid\">$uuid</h3>";
    $output .= "<div class=\"uuid\">PhoneID: $key</div>";
    $output .=  '<ol>';

    // Loop through each track 
    foreach($arr['orderd'][$key] as $datafile){

      // Lets sus the urls
      $mapurl = $rooturl."?q=map-tracks&uuid=$key&title=$datafile";
      $jsonurl = $rooturl."/map-tracks/tracks/$key/$datafile/data.json";
      $editjsonurl = $_SERVER['SCRIPT_NAME']."?format=html&edit=tracks/$key/$datafile/data.json";
      $deleteurl = "tracks/$key/$datafile";
      $imagesurl = $_SERVER['SCRIPT_NAME']."?format=html&images=tracks/$key/$datafile";

      // Are any of these being deleted?
      $deleteclass = "";
      $deleteme = lmm_checkPOSTGETvar("delete", null, "GET");
      if(!is_null($deleteme)){
        if($deleteurl==$deleteme){
          $deleteclass = "deletethis";
        }
      }

      // Lets work out the date/time
      $af = "";
      $timedate = explode('-',$datafile);
      if($timedate[0]=='AF'){
        $af = "AF:";
        array_shift($timedate);
      }
      $date = $timedate[2].'/'.$timedate[1].'/'.$timedate[0];
      $time  = date("g:ia", strtotime($timedate[3].':'.$timedate[4].':'.$timedate[5]));

      // Lets get the tagname and description
      $tag = "";
      $description = "";
      $jsonfilepath ="tracks/$key/$datafile/data.json";
      $filejson = json_decode(file_get_contents($jsonfilepath));
      if(isset($filejson->track)){
        if(isset($filejson->track->tag)){
          $tag = $filejson->track->tag;
        }
        if(isset($filejson->track->description)){
          $description = $af.$filejson->track->description;
        }
      }

      // Now prep the links
      $output .=  "<li class=\"$deleteclass\">";
      $output .=  "<div class=\"title\">";
      if(ISADMIN) $output .= "<a class=\"delete\" href=\"?delete=$deleteurl\">[x]</a>";
      $output .=  " <a href=\"$mapurl\" class=\"description\">$description</a>"; 
      $output .=  "</div>";
      $output .=  " <a href=\"$mapurl\"><span class=\"datetime\">$date $time \"$tag\"</span></a>";
      $output .=  "<div class=\"tracklinks\">";
      $output .=  " <a class=\"jsonlink\" href=\"$jsonurl\">json</a> "; 
      if(ISADMIN){
        $output .=  "<a class=\"jsonlink\" href=\"$editjsonurl\">[e]&nbsp;</a>"; 
      }
      $output .=  "<a class=\"jsonlink\" href=\"$imagesurl\">[i]&nbsp;</a>"; 
      $output .=  "</div>";
      $output .=  "</li>";
    }
    $output .=  "</ol></div>";    
  }
  $output .=  '<div class="footer">IP: '.$_SERVER['REMOTE_ADDR'].'</div>';
  $output .= '</div>';
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
      li{list-style:none;border-top:1px solid #ccc;padding-bottom:2px;}
      a{text-decoration:none}
      .tracklists{clear:both;}
      a.jsonlink{text-decoration:none;color:#ccc;font-size:0.7em;}
      a.description{font-color:#555;}
      .datetime{font-size:0.8em;color:#333;}
      .box{float:left;width:24%;}
      .footer{clear:both;border-top:20px solid #fff;margin-top:20px;color:#ccc;}
      .delete{color:red;font-size:0.7em;}
      #editjson{width:100%;height:400px;}
      #editform{clear:both;}
      #editform input{width:100%;}
      .phoneid{margin:0px;}
      .uuid{color:#ccc;height:2.5em;overflow:hidden;}
      .deletethis{border:3px solid red;}
      .deletebut{padding:5px;margin-bottom:10px;border:3px solid red;display:block;width:50px;float:left;}
      .deletebut:hover{border:3px solid #333;}
      .nobut{margin-left:50px;}
    </style>
  </head>
  <body>
  <?php print $content; ?>
  </body>
  </html>

<?php } ?>
