<?php  
/* Select which fuction to perform */
init();

/* 
 * Application logic   
 *
*/ 
function init(){  
  $page = 'map';  
  if(isset($_GET['q'])) $page  = $_GET['q'];
  switch($page){      
    case "parsedata": 
      parsedata(); 
    break;  
    case "savedata": 
      saveposteddata(); 
    break;      
    case "map": 
    default:  
      if(isset($_GET['map'])){
        $js = $_GET['map'].'/'.$_GET['map'].'.js';
      }else{
        $js = 'map-animatedpaths/map-animatedpaths.js';
      }  
      include('layout.php');
    break; 
  }   
}

/* 
 * Save posted lat/lng data
*/
function saveposteddata(){
  if(isset($_POST['la'])) $lat  = $_POST['la'];      
  else $lat = 0;
  if(isset($_POST['lo'])) $lng  = $_POST['lo'];      
  else $lng = 0; 
  $ip = $_SERVER['REMOTE_ADDR'];   
  $msg = "$lat,$lng,$ip\n";
  $err = write_to_file($msg, "data.txt");
  if($err) print $err; // If there's been an error let us know  
  else print "posted";
}


/* 
 * Write data to the begining of a file
*/ 
function write_to_file($str, $filename){
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
function saveimage(){
  $postdata = file_get_contents("php://input");
  $postdata = str_replace('data:image/jpg;base64,', '', $postdata);
  $imgdata = base64_decode($postdata);
  file_put_contents(
        'test/' . $fn,
        $imgdata
  );
}  


/* 
 * Quick and dirty function to parse submitted lat/lng data   
*/
function parsedata(){
  $str ="51.477766,-0.025259,82.132.224.4
  51.477757,-0.025247,82.132.224.4
  51.477749,-0.025233,82.132.224.4
  51.47775,-0.025266,82.132.224.4
  51.477744,-0.02531,82.132.224.4
  51.477744,-0.025358,82.132.224.4
  51.477745,-0.025398,82.132.224.4
  51.477748,-0.025419,82.132.224.4
  51.477746,-0.025437,82.132.224.4";  
  $array = explode("\n", trim($str));  
  print 'L.polyline([';
  foreach($array as $line){
     $data = explode(',',$line);  
     print  '['.$data[0].','.$data[1].'],';  
  } 
  print ']),';
}
?> 