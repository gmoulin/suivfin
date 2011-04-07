{if !empty($forecasts)}
	{if !$partial}
		<div id="forecasts" class="clearfix">
	{/if}

			{foreach $forecasts as $month => $fc_statuses}
				<div class="forecast">
					{$month}
					<ul class="statuses">
						{foreach $fc_statuses as $status => $fc_currencies}
							<li class="status_{$status}">
								{$statuses[$status]}
								<ul class="currencies">
									{foreach $fc_currencies as $currency => $forecast}
										<li class="currency_{$currency}">
											total: {$forecast} {$currencies[$currency]}s
										</li>
									{/foreach}
								</ul>
							</li>
						{/foreach}
					</ul>
				</div>
			{/foreach}

	{if !$partial}
		</div>
	{/if}
{/if}