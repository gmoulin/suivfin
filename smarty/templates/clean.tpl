{strip}
<!DOCTYPE html>
<html lang="{$lang}" manifest="site.manifest">
<head>
	<title>Suivi Financier</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta name="identifier-url" content="http://{$smarty.server.SERVER_NAME}" />
	<meta name="Description" content="{$metadata.description|escape}" />
	<meta name="Keywords" content="{$metadata.motscles|escape}" />
	<meta name="robots" content="noarchive" />
	<meta name="author" content="Guillaume Moulin" />
	<meta http-equiv="Pragma" content="no-cache" />

	<link rel="shortcut icon" href="favicon.ico" type="images/x-icon" />

	<!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements & feature detects -->
	<script src="js/libs/modernizr-2.0.6.custom.min.js"></script>
</head>
<body>
	<button onclick="cleanCache()">Nettoyer le cache pour ce site</button> (localStorage et applicationCache).

	<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
	<script src="js/libs/jquery-1.6.2.min.js"></script>
	<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.js">\x3C/script>')</script>

	<script>
		{literal}
			function cleanCache(){
				if( Modernizr.applicationcache ){
					/*
						@toto buggy javascript method (mozItems)
					*/
					console.log(window.applicationCache);
					if( window.applicationCache.mozItems ) console.log(window.applicationCache.mozItems);

					if( window.applicationCache.mozItems && window.applicationCache.mozItems.length ){
						for( c in window.applicationCache.mozItems ){
							window.applicationCache.mozRemove(c);
						}
					}
				}

				if( Modernizr.localstorage ){
					localStorage.clear();
				} else {
					document.cookie = "localStorage=; path=/";
				}

				/*
					ask the server for smarty and stash caches cleaning
				*/
				$.get('clean.php', 'servercache=1', function(data){
					$('body').append('<p>'+ data +'</p>');
				});
			}
		{/literal}
	</script>

</body>
</html>
{/strip}