{if !$partial}
	<section id="sums">
{/if}
{if !empty($sums)}
	{foreach $sums.list as $month => $sum_types}
		<div class="sum">
			<table>
				<thead>
					<tr>
						<th>{$lang_months[$month]}</th>
						{foreach $sums.fromto as $fromto}
							<th class="fromto">{$origins[$fromto]|replace:$owners[$owner]:''|trim}</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
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
							{foreach $sums.total[$month][$header] as $currency => $sum}
								<td>{$sum|number_format:2:'.':'\''} {$currenciesWSymbol[$currency].symbol}</td>
							{/foreach}
						{/foreach}
					</tr>
				</tfoot>
			</table>
		</div>
	{/foreach}
{/if}
{if !$partial}
	</section>
{/if}
