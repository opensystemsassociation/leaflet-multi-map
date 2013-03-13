<?php  
/* ======================
 * Selection of functions to: 
 *   - Display a specific map
 *   - Save data via a POST
 *   - Return formated data in response to an AJAX request
 *   - Write data to a file
 *   - Read data from a file
 *   - Convert XML to json
 *   - Test output
 * ======================
*/
lmm_init();


/* 
 * Application logic   
*/
function lmm_init(){  
  $page = '';  
  if(isset($_GET['q'])) $page = lmm_checkInput($_GET['q']);
  switch($page){      
    case "geojson":
      print lmm_geojson();
    case "parsedata": 
      lmm_parsedata(); 
    break;  
    case "savedata": 
      lmm_saveposteddata(); 
    break;      
    default:  
      if($page!=''){
        $js = $page.'/custom.js';
      }else{
        $js = 'map-geojson/custom.js';
      }  
      include('layout.php');
    break; 
  }   
}


/* 
 * Save posted lat/lng data
*/
function lmm_geojson(){
   
}

/* 
 * Save posted lat/lng data
*/
function lmm_saveposteddata(){
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
 * Quick and dirty tester function to parse submitted lat/lng data   
*/
function lmm_parsedata(){
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

/*
 * Convert XML input into an array
 * Derived from: http://outlandishideas.co.uk/blog/2012/08/xml-to-json
 * Eample usage:
     $xmlNode = simplexml_load_file('example.xml');
     $arrayData = lmm_xmltoarray($xmlNode);
     echo json_encode($arrayData);
*/
function lmm_xmltoarray($xml, $options = array()){
    $defaults = array(
        'namespaceSeparator' => ':',//you may want this to be something other than a colon
        'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
        'alwaysArray' => array(),   //array of xml tag names which should always become arrays
        'autoArray' => true,        //only create arrays for tags which appear more than once
        'textContent' => '$',       //key used for the text content of elements
        'autoText' => true,         //skip textContent key if node has no attributes or child nodes
        'keySearch' => false,       //optional search and replace on tag and attribute names
        'keyReplace' => false       //replace values for above search values (as passed to str_replace())
    );
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces();
    $namespaces[''] = null; //add base (empty) namespace
 
    //get attributes from all namespaces
    $attributesArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }
 
    //get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = xmlToArray($childXml, $options);
            list($childTagName, $childProperties) = each($childArray);
 
            //replace characters in tag name
            if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            //add namespace prefix, if any
            if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
 
            if (!isset($tagsArray[$childTagName])) {
                //only entry with this key
                //test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                        ? array($childProperties) : $childProperties;
            } elseif (
                is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                === range(0, count($tagsArray[$childTagName]) - 1)
            ) {
                //key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                //key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
            }
        }
    }
 
    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;
 
    //stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
 
    //return node as array
    return array(
        $xml->getName() => $propertiesArray
    );
}




?> 
