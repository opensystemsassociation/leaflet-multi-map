<?php
include_once "../utils.php";
$format = lmm_checkPOSTGETvar("format", "html", "GET");
$arr = array();
$arr = readdirectory('tracks', 'data.json', $arr);
  
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
          if($ref==$searchfor) $arr[] = $savename[$cnt-3].'/'.$savename[$cnt-2].'/'.$savename[$cnt-1];
        }
      }
    }
    closedir($handle);
  } 
  return $arr;
}
if( $format == "json" ) {
    print json_encode($arr);  
} else {
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="ltr" lang="en-US" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="ltr" lang="en-US" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="ltr" lang="en-US" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="ltr" lang="en-US" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="ltr" lang="en-US"> <!--<![endif]-->    

<head>
<title>List of all tracks</title>
<!--  Mobile Viewport Fix -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<!-- Google WebFonts -->
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Raleway:100" />
<link rel="stylesheet" href="libs-js-css/leaflet.css" />
<!--[if lte IE 8]>
    <link rel="stylesheet" href="libs-js-css/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="style.css" />
<!-- make legacy Internet Explorer play nice(r) -->
<!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
</head>
<body>
</body>
</html>
<? }; ?>
