{if !empty($sums)}
	{if !$partial}
		<div id="sums" class="clearfix">
	{/if}

			{foreach $sums as $month => $sum_origins}
				<div class="sum">
					{$month}
					<ul class="origins">
						{foreach $sum_origins as $origin => $sum_types}
							<li class="origin_{$origin}">
								{$origins[$origin]}
								<ul class="types">
									{foreach $sum_types as $type => $sum_currencies}
										<li class="type_{$type}">
											{$types[$type]}
											<ul class="currencies">
												{foreach $sum_currencies as $currency => $sum}
													<li class="currency_{$currency}">
														total: {$sum} {$currencies[$currency]}s
													</li>
												{/foreach}
											</ul>
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