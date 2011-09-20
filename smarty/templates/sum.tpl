{strip}
	{if !$partial}
		<section id="sums" class="clearfix">
	{/if}
	{if !empty($sums.list)}
		<div data-month="{$sumMonth}">
			<table>
				<thead>
					<tr>
						<th title="du 24 précédent au 25">{$monthsTranslation[$sumMonth|substr:-2]}</th>
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
							{if !isset($sums.balance[$header])}
								<td></td>
							{else}
								{foreach $sums.balance[$header] as $currency => $amount}
									<td>{$amount|number_format:2:'.':'\''} {$currenciesWSymbol[$currency].symbol}</td>
								{/foreach}
							{/if}
						{/foreach}
					</tr>
					{foreach $sums.list as $type => $sum_fromto}
						<tr>
							{if strpos($type, 'r') !== false}
								{assign var="isRecurrent" value=1}
								{assign var="type" value=$type|replace:'r':''}
							{else}
								{assign var="isRecurrent" value=0}
							{/if}
							<td class="type{if $isRecurrent == 1} recurrent{/if}">{$types[$type]|capitalize}</td>
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
							{if !isset($sums.total[$header])}
								<td></td>
							{else}
								{foreach $sums.total[$header] as $currency => $sum}
									<td>{$sum|number_format:2:'.':'\''} {$currenciesWSymbol[$currency].symbol}</td>
								{/foreach}
							{/if}
						{/foreach}
					</tr>
				</tfoot>
			</table>
		</div>
	{/if}
	{if !$partial}
		</section>
	{/if}
{/strip}
