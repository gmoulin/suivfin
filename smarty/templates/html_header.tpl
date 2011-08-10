<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- add manifest="site.manifest" if needed -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" manifest="site.manifest"> <!--<![endif]-->
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

	<!-- Mobile viewport optimization j.mp/bplateviewport -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320"/>
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">

	<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

	<!-- CSS: implied media=all -->
	<!-- CSS concatenated and minified via ant build script-->
	<link rel="stylesheet" href="css/style.css?v={$css}">
	<!-- end CSS-->

	<!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

	<!-- All JavaScript at the bottom, except for Modernizr / Respond.
	   Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
	   For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
	<script src="js/libs/modernizr-2.0.6.custom.min.js"></script>
</head>
<body>