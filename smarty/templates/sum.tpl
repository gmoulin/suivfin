{strip}
	{if !$partial}
		<section id="sums" class="clearfix">
	{/if}
	{if !empty($sums.list)}
		{foreach $sums.list as $month => $sum_types}
			<table>
				<thead>
					<tr>
						<th title="du 24 précédent au 25">{$monthsTranslation[$month|substr:-2]}</th>
						{foreach $sums.fromto as $ori}
							<th class="fromto">
								{$origins[$ori]|replace:$owners[$owner]:''|replace:'Liquide ':''|replace:'Euro':'€'|replace:'Franc':'CHF'|trim}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="type">Balance</td>
						{foreach $sums.fromto as $header}
							{if !isset($sums.balance[$month][$header])}
								<td></td>
							{else}
								{foreach $sums.balance[$month][$header] as $currency => $amount}
									<td>{$amount|number_format:2:'.':'\''} {$currenciesWSymbol[$currency].symbol}</td>
								{/foreach}
							{/if}
						{/foreach}
					</tr>
					{foreach $sum_types as $type => $sum_fromto}
						<tr>
							<td class="type">{$types[$type]|capitalize}</td>
							{foreach $sums.fromto as $header}
								{if !isset($sum_fromto[$header])}
									<td></td>
								{else}
									{foreach $sum_fromto[$header] as $currency => $sum}
										<td>{$sum|number_format:2:'.':'\''} {$currenciesWSymbol[$currency].symbol}</td>
									{/foreach}
								{/if}
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<th>Total</th>
						{foreach $sums.fromto as $header}
							{if !isset($sums.total[$month][$header])}
								<td></td>
							{else}
								{foreach $sums.total[$month][$header] as $currency => $sum}
									<td>{$sum|number_format:2:'.':'\''} {$currenciesWSymbol[$currency].symbol}</td>
								{/foreach}
							{/if}
						{/foreach}
					</tr>
				</tfoot>
			</table>
			{if !$sum_types@last}<hr />{/if}
		{/foreach}
	{/if}
	{if !$partial}
		</section>
	{/if}
{/strip}
