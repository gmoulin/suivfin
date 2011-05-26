<!DOCTYPE html>
<html lang="{$lang}" class="no-js" manifest="site.manifest">
<head>
	<title>Suivi Financier</title>
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
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">

	<!-- Home screen icon  Mathias Bynens http://goo.gl/6nVq0 -->
	<!-- For iPhone 4 with high-resolution Retina display: -->
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/apple-touch-icon-114x114-precomposed.png">
	<!-- For first-generation iPad: -->
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/apple-touch-icon-72x72-precomposed.png">
	<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
	<link rel="apple-touch-icon-precomposed" href="img/apple-touch-icon-precomposed.png">
	<!-- For nokia devices: -->
	<link rel="shortcut icon" href="img/apple-touch-icon.png">

	<link rel="shortcut icon" href="favicon.ico" type="images/x-icon" />

	<!--iOS web app, deletable if not needed -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<link rel="apple-touch-startup-image" href="img/splash.png">
	<link rel="apple-touch-icon" href="img/apple-touch-icon.png">

	<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
	<meta http-equiv="cleartype" content="on">


	<!-- CSS: implied media="all" -->
	<link rel="stylesheet" href="css/style.css?v={$css}">

	<!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements & feature detects -->
	<script src="js/libs/modernizr-1.7.min.js"></script>
</head>
<body>

