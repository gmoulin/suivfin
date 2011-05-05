		<footer id="help">
			{*include "help.tlp"*}
		</footer>

		<!-- JavaScript at the bottom for fast page loading -->

		<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
		<script src="js/libs/jquery-1.6.min.js"></script>
		<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.js">\x3C/script>')</script>

		<!-- scripts concatenated and minified via ant build script-->
		<script src="js/mylibs/jquery.isotope.min.js"></script>
		<script src="js/mylibs/jquery.tmpl.min.js"></script>
		<script src="js/mylibs/highcharts.js"></script>
		<script src="js/plugins.js"></script>
		<script src="js/script.js?v={$js}"></script>
		<!-- end scripts-->

		<script>
			var limits = {$limits|json_encode};
		</script>
		{literal}
			<script id="paymentListTemplate" type="text/x-jquery-tmpl">
				{{each(i, payment) payments}}
					<div class="item {{if payment.recurrent == 1}}recurrent{{else}}punctual{{/if}} recipient_${payment.recipientFK} type_${payment.typeFK} currency_${payment.currencyFK} method_${payment.methodFK} origin_${payment.originFK} status_${payment.statusFK} location_${payment.locationFK}"
						title="dernière modification le ${payment.modificationDate.formatDate()}"

						data-date="${payment.paymentDate.timestamp()}"
						data-recipient="${getValue(recipients, payment.recipientFK)}"
						data-method="${getValue(methods, payment.methodFK)}"
						data-origin="${getValue(origins, payment.originFK)}"
						data-status="${getValue(statuses, payment.statusFK)}"
						data-amount="${payment.amount}"
					>
						<div class="buttons button-group">
							<a class="button pill icon edit icon-only" href="${payment.id}" title="Editer ce paiement">Editer</a>
							<a class="button pill icon fork icon-only" href="${payment.id}" title="Duppliquer ce paiement">Duppliquer</a>
							<a class="button pill icon trash icon-only" href="${payment.id}" title="Supprimer ce paiement">Supprimer</a>
						</div>
						<dl>
							<dd>
								${getValue(types, payment.typeFK).capitalize()}
								<span>${payment.paymentDate.formatDate()}</span>
							</dd>
							<dd>${payment.label}</dd>
							<dd>
								<strong>${payment.amount.format()} ${getSymbol(currenciesWSymbol, payment.currencyFK)}</strong>
								&nbsp;par ${getValue(methods, payment.methodFK)}
							</dd>
							<dd>
								${getValue(origins, payment.originFK)} → ${getValue(recipients, payment.recipientFK)}
							</dd>
						</dl>
					</div>
				{{/each}}
			</script>
		{/literal}
	</body>
</html>
