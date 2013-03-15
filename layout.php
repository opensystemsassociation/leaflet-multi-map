<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="ltr" lang="en-US" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="ltr" lang="en-US" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="ltr" lang="en-US" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="ltr" lang="en-US" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="ltr" lang="en-US"> <!--<![endif]-->    

<head>
<title>Tests with Leaflet</title>
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
	<header id="branding" role="banner" class="clearfix">
	  <hgroup id="logo">
	      <h1><span><a href="http://transport.yoha.co.uk/" title="cibi.me" rel="home">Southend Mapping</a></span></h1>
	      <h2>
	       <a href="?q=map-live" rel="home">Refresh Live data</a>  
	      </h2>
	  </hgroup>
	</header>  

  <div id="map"></div>
  <script type="text/javascript" src="libs-js-css/jquery.min.js"></script>
  <script type="text/javascript" src="libs-js-css/leaflet.js"></script>
  <script type="text/javascript" src="libs-js-css/AnimatedMarker.js"></script>
  <script src="<?php print  $js; ?>"></script>
</body>
</html>
