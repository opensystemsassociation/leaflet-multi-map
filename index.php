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
        $js = 'map-live/custom.js';
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
    if(!file_exists($path)){ 
      $f = fopen($path, "a+");
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
 * Save posted lat/lng data
*/
function lmm_saveposteddata(){   
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
    $root = $_SERVER['DOCUMENT_ROOT']."/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/tracks/$uuid";
    $trackdir = "$root/$track";
    $path = "$trackdir/data.txt";
    // Create a root directory if it doesn't exist
    if(!is_dir($root)){
      mkdir($root);
      //chmod($root, 0755);
      $status .= " crd";
    }
    // Create a track directory if it doesn't exist
    if(!is_dir($trackdir)){
      mkdir($trackdir);
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
* Helper function to log output
*
*/
function lmm_checkisset($var, $default){
  if(isset($var)) return $var;
  else return $default;
}

/*
* Helper function to log output
*
*/
function lmm_logoutput($status){
  $msg = strftime('%c')."\n$status\n";
  // Prep the vars
  $msg = "$status [GETVARS] ";
  foreach($_GET as $key=>$value){
    $msg .= "$key=$value | ";
  }
  //$msg .= " [POSTVARS]";
  //foreach($_POST as $key=>$value){
  //  $msg .= "$key=$value | ";
  //}
  //$msg .= " [SERVERVARS]";
  //foreach($_SERVER as $key=>$value){
  //  $msg .= "$key=$value\n";
  //}  
  /*$postvars = lmm_checkPOSTGETvar('vars', NULL, 'POST');
  $vars = json_decode($postvars); 
  $msg .= 'Title:'.$vars->track->title."\n"; 
  $msg .= 'author:'.$vars->track->author."\n"; 
  $msg .= 'starttime:'.$vars->track->starttime."\n";
  $msg .= 'endtime:'.$vars->track->endtime."\n"; 
  $msg .= 'name:'.$vars->track->device->name."\n";   
  $msg .= 'cordova:'.$vars->track->device->cordova."\n";
  $msg .= 'platform:'.$vars->track->device->platform."\n";
  $msg .= 'version:'.$vars->track->device->version."\n";
  $msg .= 'uuid:'.$vars->track->device->uuid."\n";
  */
  // Write to file
  $logpath = "/var/www/localhost/htdocs/drupal7/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/log.txt";
  $f = fopen($logpath, 'a') or die(" can't open file");
  fwrite($f, "\n$msg\n\n");
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