<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]><html lang="<?php echo $lang; ?>" class="no-js ie ie6 ielt9 ielt8 ielt7"><![endif]-->
<!--[if IE 7 ]><html lang="<?php echo $lang; ?>" class="no-js ie ie7 ielt9 ielt8"><![endif]-->
<!--[if IE 8 ]><html lang="<?php echo $lang; ?>" class="no-js ie ie8 ielt9"><![endif]-->
<!--[if IE 9 ]><html lang="<?php echo $lang; ?>" class="no-js ie ie9" manifest="site.manifest"><![endif]-->
<!--[if IEMobile 7 ]><html lang="<?php echo $lang; ?>" class="no-js ie iem7" manifest="site.manifest"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="{$lang}" class="no-js" manifest="site.manifest"><!--<![endif]-->
<head>
	<title>SuivFin</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta name="identifier-url" content="http://{$smarty.server.SERVER_NAME}" />
	<meta name="Description" content="{$metadata.description|escape}" />
	<meta name="Keywords" content="{$metadata.motscles|escape}" />
	<meta name="robots" content="index, follow, noarchive" />
	<meta name="author" content="Guillaume Moulin" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta name="distribution" content="global" />
	<meta name="revisit-after" content="1 days" />

	<!-- Mobile viewport optimization http://goo.gl/b9SaQ -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320"/>

	<!-- Home screen icon  Mathias Bynens http://goo.gl/6nVq0 -->
	<!-- For iPhone 4 with high-resolution Retina display: -->
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/apple-touch-icon-114x114-precomposed.png">
	<!-- For first-generation iPad: -->
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/apple-touch-icon-72x72-precomposed.png">
	<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
	<link rel="apple-touch-icon-precomposed" href="img/apple-touch-icon-precomposed.png">
	<!-- For nokia devices: -->
	<link rel="shortcut icon" href="img/apple-touch-icon.png">

	<!--iOS web app, deletable if not needed -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<link rel="apple-touch-startup-image" href="img/splash.png">

	<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
	<meta http-equiv="cleartype" content="on">

	<link href="favicon.ico" rel="shortcut icon" type="images/x-icon" />

	<title>Suivi Financier</title>

	<!-- Mobile viewport optimized: j.mp/bplateviewport -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Place favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">

	<!-- CSS: implied media="all" -->
	<link rel="stylesheet" href="css/style.css?v={$css}">

	<!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements & feature detects -->
	<script src="js/libs/modernizr-1.7.min.js"></script>
</head>
<body>

