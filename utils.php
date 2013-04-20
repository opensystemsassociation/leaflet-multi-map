<?php  

// Which IP addresses have permission to delete tracks?
$IParray = array(
"217.44.120.135", "81.2.68.178", "217.42.187.243"
);

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
/*
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
  // /*
  // Write to file
  $logpath = "/var/www/localhost/htdocs/drupal7/sites/transport.yoha.co.uk/leaflet-multi-map/map-live/log.txt";
  $f = fopen($logpath, 'a') or die(" can't open file");
  fwrite($f, "\n$msg\n\n");
  fclose($f);   
}
*/

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
