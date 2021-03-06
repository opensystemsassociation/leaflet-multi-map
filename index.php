<?php  
/* ======================
 * Selection of functions to: 
 *   - Save data to a file via POST or GET
 *   - Display a specific map
 *   - Return formated data in response to an AJAX request
 *   - Test output
 * ======================
*/
include_once "utils.php";
lmm_init();


/* 
 * Application logic   
*/
function lmm_init(){
  // prep vars  
  $config = lmm_getConfig();
  $page = '';  
  $val = '';
  $jsvars = array();
  $page = lmm_checkPOSTGETvar('q', '');
  $jsvars['dlat'] = lmm_checkPOSTGETvar('dlat', '', 'GET');
  $jsvars['dlng'] = lmm_checkPOSTGETvar('dlng', '', 'GET'); 

  // Select output
  switch($page){
    case "savelivedevice":
      // lmm_savelivedevice(); 
    break;        
    case "savedatastring":
        lmm_saveposteddatastring();
    break; 
    case "saveB64imagestr":
        lmm_saveB64imagestr(); 
        break;
    case "savedata":
        lmm_saveposteddata(); 
    break;  
    case "postform":
      lmm_postform();
    break;     
    default:  
        $baseUrl = lmm_getBase();
        $baseDir = lmm_getBase(true);
      if($page!=''){
        $js = $page.'/custom.js';
      }else{
        $js = "{$config->defaultMap}/custom.js";
      }  
      include('layout.php');
    break; 
  }  
}

/* 
 * Save posted lat/lng data
*/
function lmm_savelivedevice(){   
  // Set the variables
  $status = "";
  $uuid = lmm_checkPOSTGETvar('uuid', NULL, 'GET');
  $lat = lmm_checkPOSTGETvar('la', NULL, 'GET');
  $lng = lmm_checkPOSTGETvar('lo', NULL, 'GET');
  $dn = lmm_checkPOSTGETvar('dn', NULL, 'GET');
  $time = date('D jS F Y h:i:s A');

  // if ok, then save them
  if($lat!=NULL && $lng!=NULL && $uuid!=NULL){
    // Initialise vars
    $status .= "gv";       
    $msg = "[\"$time\",$lat,$lng, \"$dn\"]"; 
    $root = realpath(dirname(".")) . "/map-tracks/live-devices";
    $path = "$root/$uuid.txt";
    // Create/Write to file
    $f = fopen($path, "a+");
    if(!file_exists($path)){ 
      fwrite($f, $msg);
      fclose($f);
      chmod($path, 0755);
      $status .= " cf";    
    }else{   
      $f = fopen($path, 'w') or die("Can't Open File");
      fwrite($f, $msg);
      fclose($f);
      $status .= " W";  
    }    
  }else{
    $status .= "NoVars";
  }
  // print a json response
  print $status;
  // Log the current output
  // lmm_logoutput($status);
}  

/* 
 * Save posted lat/lng data. Accepts track as file.
 * URL - ?q=savedata&?uuid={uuid}&title={tracktitle}&FILES['data']
 */
function lmm_saveposteddata(){   
    // Set the variables
    $status = "";
    $uuid = lmm_checkPOSTGETvar('uuid', NULL, 'GET');
    $title = lmm_checkPOSTGETvar('title', NULL, 'GET');
    $hasFilePost = isset($_FILES['data']);

    // Passed validation?
    if($uuid!=NULL && $title!='' && $hasFilePost) {

        // If all params are present then get file.
        $file = $_FILES['data'];

        // Initialise vars
        $track = str_replace(' ', '', lmm_checkisset($title, 'default') );
        $status .= "gv"; // Got Variables.
        $trackdir = realpath(dirname(".")) . "/map-tracks/tracks/$uuid/$track";
        $trackpath = "$trackdir/data.json";

        // Create a track directory if it doesn't exist
        if(!is_dir($trackdir)){
            mkdir($trackdir, 0775, true); // Recursive.
            $status .= " ctd"; // Directory created.
        }

        // Move uploaded file.
        if( move_uploaded_file($file['tmp_name'], $trackpath )) {
            $status .= " fct"; // File Created.
            echo "File is valid, and was successfully uploaded.\n";
        } else {
            $status .= " ff"; // File Failed.
            echo "Possible file upload attack!\n";
        }

    }else{
        $status .= "NoVars";
    }
    print $status;
}


/* 
 * Save posted AppFurnace. Accepts track as string in POST.
 * URL - ?q=savedatastring&?uuid={uuid}
 */
function lmm_saveposteddatastring(){   
  // Set the variables
  $status = "";
  $uuid = lmm_checkPOSTGETvar('uuid', NULL, 'GET');
  $varsstr = lmm_checkPOSTGETvar('vars', '', 'POST');
  $vars = json_decode($varsstr);

  // if ok, then save them
  if(isset($vars->track) && $uuid!=NULL && $varsstr!='') {
    // Initialise vars
    $track = str_replace(' ', '', lmm_checkisset($vars->track->title, 'default') );
    $status .= "gv";       
    $msg = $varsstr; 
    $root = realpath(dirname(".")) . "/map-tracks/tracks/$uuid/";
    $trackdir = "$root/$track";
    $path = "$trackdir/data.json";
    // Create a root directory if it doesn't exist
    if(!is_dir($root)){
      mkdir($root, 0775, true);
      //chmod($root, 0755);
      $status .= " crd";
    }
    // Create a track directory if it doesn't exist
    if(!is_dir($trackdir)){
      mkdir($trackdir, 0775, true);
      //chmod($trackdir, 0755);
      $status .= " ctd";
    }
    // Create/Write to file
    if(!file_exists($path)){ 
      $f = fopen($path, "a+");
      fwrite($f, $msg);
      fclose($f);
      //chmod($path, 0755);
      $status .= " cf";    
    }else{   
      $f = fopen($path, 'w') or die("Can't Open File");
      fwrite($f, $msg);
      fclose($f);
      $status .= " w";  
    }    
  }else{
    $status .= "NoVars";
  }
  // print a json response
  print $status;
  // Log the current output
  //lmm_logoutput($status);
}  


/* 
 * Post some lat/lng data
*/
function lmm_postform(){
  print '
   <html> 
    <form action="?q=savedata" method="post">
      lat: <input type="text" name="la" value="51.54695"><br>
      Lng: <input type="text" name="lo" value="0.71162"><br>
      Lng: <input type="text" name="uuid" value="onlineform"><br>
      <input type="submit" value="Submit">
    </form>
    </body>
    </html>
  ';
}


/* 
 * Save an image uploaded via phonegap   
 * UNTESTED
*/
function lmm_saveB64imagestr(){

    // Set the variables
  $status = "";
  $uuid = lmm_checkPOSTGETvar('uuid', NULL, 'GET');
  $tracktitle = lmm_checkPOSTGETvar('tracktitle', null, 'POST');
  $imgtitle = lmm_checkPOSTGETvar('imgtitle', null, 'POST');
  $imgstr = lmm_checkPOSTGETvar('imgstr', null, 'POST');

  // if ok, then save them
  if(isset($tracktitle) && isset($imgtitle) && isset($imgstr) && $uuid!=NULL) {
    // Initialise vars
    $status .= "gv";       
    
    $imgstr = str_replace('data:image/jpeg;base64,', '', $imgstr);
    $msg = base64_decode($imgstr);

    $root = realpath(dirname(".")) . "/map-tracks/tracks/$uuid/";
    $trackdir = "$root/$tracktitle";
    $path = "$trackdir/".$imgtitle;
    // Create a root directory if it doesn't exist
    if(!is_dir($root)){
      mkdir($root, 0775, true);
      //chmod($root, 0755);
      $status .= " crd";
    }
    // Create a track directory if it doesn't exist
    if(!is_dir($trackdir)){
      mkdir($trackdir, 0775, true);
      //chmod($trackdir, 0755);
      $status .= " ctd";
    }
    // Create/Write to file
    if(!file_exists($path)){ 
      $f = fopen($path, "a+");
      fwrite($f, $msg);
      fclose($f);
      //chmod($path, 0755);
      $status .= " cf";    
    }else{   
      $f = fopen($path, 'w') or die("Can't Open File:".$path);
      fwrite($f, $msg);
      fclose($f);
      $status .= " w";  
    }    
  }else{
    $status .= "NoVars";
  }
  // print a json response
  print $status;
  // Log the current output
  //lmm_logoutput($status);
}  


// Returns /path/to/maps/index.php
function lmm_getBase($getdir=false) {
    // Get executing filename.
    $break = explode('/', $_SERVER["SCRIPT_NAME"]);
    $currfile = $break[count($break) - 1]; 

    $url = $_SERVER['REQUEST_URI']; 
    $parts = explode('/',$url);
    $dir = "";
    for ($i = 0; $i < count($parts) - 1; $i++) {

        if( $getdir === false 
            || ( $getdir === true && $parts[$i] != $currfile ) ){
                $dir .= $parts[$i] . "/";
            }

    }
    return $dir;

}

function lmm_getConfig(){
    return (object) array(
        "defaultMap"    => "map-live"
    );
}

?>
