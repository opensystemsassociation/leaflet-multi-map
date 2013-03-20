<?php  
/* ======================
 * Selection of functions to: 
 *   - Display a specific map
 *   - Save data to a file via POST or GET
 *   - Return formated data in response to an AJAX request
 *   - Test output
 * ======================
*/
lmm_init();


/* 
 * Application logic   
*/
function lmm_init(){

  // prep vars  
  $page = '';  
  $val = '';
  $jsvars = array();
  $page = lmm_checkPOSTGETvar('q', '');
  $jsvars['dlat'] = lmm_checkPOSTGETvar('dlat', '', 'GET');
  $jsvars['dlng'] = lmm_checkPOSTGETvar('dlng', '', 'GET'); 

  // select output
  switch($page){        
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
        $js = 'map-live/custom.js';
      }  
      include('layout.php');
    break; 
  }  
}

/* 
 * Save posted lat/lng data
*/
function lmm_saveposteddata(){   
  // Set the variables
  $status = "[STATUS] ";
  $lat = lmm_checkPOSTGETvar('la', NULL, 'GET');
  $lng = lmm_checkPOSTGETvar('lo', NULL, 'GET');
  $uuid = lmm_checkPOSTGETvar('uuid', NULL, 'GET'); 

  // if ok, then save them
  if($lat!=NULL && $lng!=NULL && $uuid!=NULL ){
    // Initialise vars
    $status .= "GotVars";       
    $msg = "[$lat,$lng]"; 
    $root = $_SERVER['DOCUMENT_ROOT']."/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/tracks/$uuid";
    $path = "$root/gps.txt";

    // Create a root directory if it doesn't exist
    if(!is_dir($root)){
      mkdir($root);
    }
    // Create/Write to file
    if(!file_exists($path)){ 
      $f = fopen($path, "a+");
      fwrite($f, $msg);
      fclose($f);
      chmod($path, 0755);    
    }else{   
      $f = fopen($path, 'a') or die("Can't Open File");
      fwrite($f, ",$msg");
      fclose($f);  
    }    
  }else{
    $status .= "NoVars";
  }

  // Log the current output
  lmm_logoutput($status);
}  

/*
* Helper function to log output
*
*/
function lmm_logoutput($status){
  // Prep the vars
  $msg = implode($_GET);
  $msg = "$status [VARS] ";
  foreach($_GET as $key=>$value){
    $msg .= "$key=$value | ";
  }

  // Write to file
  $logpath = "/var/www/localhost/htdocs/drupal7/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/tracks/log.txt";
  $f = fopen($logpath, 'a') or die(" can't open file");
  fwrite($f, "$msg\n");
  fclose($f);   
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
 * Check if POST/GET variable is set and asign default value
*/ 
function lmm_checkPOSTGETvar($key, $defaultvalue, $GETPOST="GET+POST"){
  if($GETPOST=='GET' || $GETPOST=='GET+POST'){
    if(isset($_GET[$key])) return lmm_checkInput($_GET[$key]);
  }
  if($GETPOST=='POST' || $GETPOST=='GET+POST'){
    if(isset($_POST[$key])) return lmm_checkInput($_POST[$key]);
  }
  return $defaultvalue;
}

/* 
 * Write data to the begining of a file
*/ 
function lmm_write_to_file($str, $filename){
	if(!file_exists ($filename )){
		$fp = fopen($filename,"w"); 
		fwrite($fp, '');
		fclose($fp);
	}
	// Read & save old contents
	$old = file_get_contents($filename);
	// Open the file & write to it
	$fp = fopen($filename,"w"); 
	if(!$fp) return "Can't save string to file";
	else fwrite($fp, $str.$old); 
	fclose($fp);
	// Alls fine so don't return an error
	return NULL;
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


/* 
 * Sanitise strings to prevent SQL inject attacks etc   
*/
function lmm_checkInput($str) {
  $str = @strip_tags($str);
  $str = @stripslashes($str);
  $invalid_characters = array("$", "%", "#", "<", ">", "|");
  $str = str_replace($invalid_characters, "", $str);
  return $str;
}

?>