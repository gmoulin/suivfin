		<footer id="help">
			{*include "help.tlp"*}
		</footer>

		<!-- JavaScript at the bottom for fast page loading -->

		<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
		<script src="js/libs/jquery-1.6.2.min.js"></script>
		<script>window.jQuery || document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js">\x3C/script>')</script>


		<!-- scripts concatenated and minified via ant build script-->
		<script defer src="js/mylibs/jquery.isotope.min.js"></script>
		<script defer src="js/mylibs/jquery.tmpl.min.js"></script>
		<script defer src="js/mylibs/highcharts.min.js"></script>
		<script defer src="js/mylibs/helper.min.js"></script>
		<script defer src="js/script.js?v={$js}"></script>
		<!-- end scripts-->

		<script>
			var limits = {$limits|json_encode};
		</script>
		{include file="payment.template.html"}

		<!-- mathiasbynens.be/notes/async-analytics-snippet Change UA-XXXXX-X to be your site's ID -->
		<!--
		<script>
			{literal}
				var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview'],['_trackPageLoadTime']];
				(function(d,t){
					var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
					g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
					s.parentNode.insertBefore(g,s)
				}(document,'script'));
			{/literal}
		</script>
		-->

		<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
		   chromium.org/developers/how-tos/chrome-frame-getting-started -->
		<!--[if lt IE 7 ]>
		<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
		<script>
			{literal}
				window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})
			{/literal}
		</script>
		<![endif]-->
	</body>
</html>