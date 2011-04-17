{if !empty($forecasts)}
	{if !$partial}
		<section id="forecasts">
	{/if}
			<h2>Prévisions</h2>
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

	{if !$partial}
		</section>
	{/if}
{/if}