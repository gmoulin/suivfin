{strip}
	{if !$partial}
		<section id="forecasts">
			<h2>Prévisions</h2>
	{/if}

	{if !empty($forecasts)}
		{foreach $forecasts as $month => $fc_statuses}
			<strong>{$lang_months[$month]} :&nbsp;</strong>
			<ul class="statuses">
				{foreach $fc_statuses as $status => $fc_currencies}
					<li class="status_{$status}">
						{$statuses[$status]}&nbsp;
						<ul class="currencies">
							{foreach $fc_currencies as $currency => $forecast}
								<li class="currency_{$currency}">
									{$forecast} {$currenciesWSymbol[$currency].symbol}
									{if !$forecast@last},&nbsp;{/if}
								</li>
							{/foreach}
						</ul>
					</li>
				{/foreach}
			</ul>
		{/foreach}
	{/if}

	{if !$partial}
		</section>
	{/if}
{/strip}
