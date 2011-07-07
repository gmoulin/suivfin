		<footer id="help">
			{*include "help.tlp"*}
		</footer>

		<!-- JavaScript at the bottom for fast page loading -->

		<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
		<script src="js/libs/jquery-1.6.2.min.js"></script>
		<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js">\x3C/script>')</script>

		<!-- scripts concatenated and minified via ant build script-->
		<script src="js/mylibs/jquery.isotope.min.js"></script>
		<script src="js/mylibs/jquery.tmpl.min.js"></script>
		<script src="js/mylibs/highcharts.min.js"></script>
		<script src="js/mylibs/helper.js"></script>
		<script src="js/script.js?v={$js}"></script>
		<!-- end scripts-->

		{include file="inline-scripts.html"}
		{include file="payment.template.html"}
	</body>
</html>
