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

function lmm_getConfig(){
    return (object) array(
        "defaultMap"    => "map-live"
    );
}

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

  // select output
  switch($page){
    case "savelivedevice":
      lmm_savelivedevice(); 
    break;        
    case "savedata":
      lmm_saveposteddata(); 
    break; 
    case "postform":
      lmm_postform(); 
    break;     
    default:  
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
    $root = $_SERVER['DOCUMENT_ROOT']."/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/livedevices";
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
  lmm_logoutput($status);
}  

/* 
 * Save posted lat/lng data - ?q=savedata&?uuid={uuid}&title={tracktitle}&FILES['data']
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
            mkdir($trackdir, 0777, true); // Recursive.
            $status .= " ctd"; // Directory created.
        }

        // If file exists then break. Should not happen.
        if( file_exists( $trackpath ) ){
            $status .= " fex"; // File EXists.
            print $status;
            exit;
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
function lmm_saveimage(){
  $postdata = file_get_contents("php://input");
  $postdata = str_replace('data:image/jpg;base64,', '', $postdata);
  $imgdata = base64_decode($postdata);
  file_put_contents(
        'test/' . $fn,
        $imgdata
  );
}  

?>
