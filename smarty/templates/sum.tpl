{if !empty($sums)}
	{if !$partial}
		<section id="sums" class="clearfix">
			<h2>Totaux</h2>
	{/if}
	{foreach $sums as $month => $sum_types}
		<div class="sum">
			<table>
				<thead>
					<tr>
						<th>{$lang_months[$month]}</th>
						{foreach $sum_types as $type => $sum_origins}
							{if $sum_origins@first}
								{foreach $sum_origins as $origin => $sum_currencies}
									<th class="origin">{$origins[$origin]|replace:$owners[$owner]:''|trim}</th>
								{/foreach}
							{/if}
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach $sum_types as $type => $sum_origins}
						<tr>
							<td class="type">{$types[$type]|capitalize}</td>
							{foreach $sum_origins as $origin => $sum_currencies}
								{foreach $sum_currencies as $currency => $sum}
									<td>{$sum} {$currenciesWSymbol[$currency].symbol}</td>
								{/foreach}
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	{/foreach}
	{if !$partial}
		</section>
	{/if}
{/if}