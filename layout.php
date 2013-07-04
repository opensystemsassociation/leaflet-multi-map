<?php
$path =  realpath(dirname("."))."/map-tracks/tracks/".$_GET['uuid'].'/'.$_GET['title'].'/'.'data.json';
$rawjson = file_get_contents($path);
$json = json_decode($rawjson);
if($json===null){
  $info = "<br /><strong>Error:<br /></strong>Can't load data due to invalid json file.";
}else{
  foreach($json->track->points as $var => $value){
    $pointnames .= $var.': '.count($value).'<br />';
  }
  $info = "<ul class=\"infobox\">";
  if(!isset($_GET['embed'])) $info .= "<li><h2>".$json->track->description."</h2></li>";
  //$info .= "<li>".$json->track->title."</li>";
  $timearr = explode(' ', $json->track->starttime);
  $time = $timearr[0].' '.$timearr[1].' '.$timearr[2].' '.$timearr[3].' <br />'.$timearr[4];
  $info .= "<li><strong>".$time."</strong></li>";
  $info .= "<li><div class=\"datappoints\"><strong># Data</strong><br />".$pointnames ."</div></li>";
  $info .= "</ul>";
}
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="ltr" lang="en-US" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="ltr" lang="en-US" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="ltr" lang="en-US" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="ltr" lang="en-US" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="ltr" lang="en-US"> <!--<![endif]-->    
<!-- <?php echo $baseDir; ?> -->
<head>
<title>Tests with Leaflet</title>
<!--  Mobile Viewport Fix -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<!-- Google WebFonts -->
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Raleway:100" />
<link rel="stylesheet" href="<?php echo $baseDir ?>libs-js-css/leaflet.css" />
<!--[if lte IE 8]>
    <link rel="stylesheet" href="<?php echo $baseDir ?>libs-js-css/leaflet.ie.css" />
<![endif]-->
<link rel="stylesheet" href="<?php echo $baseDir ?>style.css" />
<!-- make legacy Internet Explorer play nice(r) -->
<!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
<script type="text/javascript">
	<?php 
		foreach($jsvars as $key=>$value){
			print "var $key='$value';\n";
		}
	?>
    var basedir = "<?php echo $baseDir; ?>";
</script>
</head>
<body>

<?php if( ! isset($_GET['q']) && $_GET['q'] === 'map-tracks' ) : ?>

	<header id="branding" role="banner" class="clearfix">
	  <hgroup id="logo">
	      <h1><span><a href="http://transport.yoha.co.uk/" title="cibi.me" rel="home">Southend Mapping</a></span></h1>
	      <h2>
	       <a href="?q=map-live">Last</a>  |
	       <a href="?q=map-live&amp;dlat=51.54335&amp;dlng=0.7103">SOS</a>  |
	       <a href="?q=map-live&amp;dlat=51.44625&amp;dlng=-0.11124">TulseH</a>
	      </h2>
	  </hgroup>
	</header>  

    <?php endif; ?>

  <!--embed the list of images -->
<div id="container">
  <div id="info" class="datawidth"><div><?php print $info; ?></div></div>
  <div id="imageanimation"  class="datawidth">image animation</div>
  <div id="graph" class="datawidth">Graph</div>
  <div id="allimages"  class="datawidth">Loading</div>
  <div id="json"  class="datawidth">
    <?php print $rawjson; ?>
    <h2><a href="https://github.com/opensystemsassociation/southendtransportresearch/blob/master/README.md">Read about data structure on github...</a></h2>
  </div>
</div>
  <!--embed the map -->
  <div id="map"></div>
  <script type="text/javascript" src="<?php echo $baseDir ?>libs-js-css/jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo $baseDir ?>libs-js-css/paper-min.js"></script>
  <script type="text/javascript" src="<?php echo $baseDir ?>libs-js-css/leaflet.js"></script>
  <script type="text/javascript" src="<?php echo $baseDir ?>libs-js-css/AnimatedMarker.js"></script>
  <script type="text/javascript" src="<?php echo $baseDir ?>libs-js-css/utils.js"></script>
  <script src="<?php print $baseDir.$js; ?>"></script>

</body>
</html>
