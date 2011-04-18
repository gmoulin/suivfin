{if !$partial}
	<section id="forecasts">
		<h2>Prévisions</h2>
{/if}

{if !empty($forecasts)}
	{foreach $forecasts as $month => $fc_statuses}
			<strong>{$lang_months[$month]} :</strong>
			<ul class="statuses">
				{foreach $fc_statuses as $status => $fc_currencies}
					<li class="status_{$status}">
						{$statuses[$status]}
						<ul class="currencies">
							{foreach $fc_currencies as $currency => $forecast}
								<li class="currency_{$currency}">
								{strip}
									{$forecast} {$currenciesWSymbol[$currency].symbol}
									{if !$forecast@last},{/if}
								{/strip}
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
