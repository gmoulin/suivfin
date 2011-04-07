{if !empty($payments)}
	{if !$partial}
		<div id="container" class="clearfix">
	{/if}
			{foreach $payments as $payment}
				<div class="item
						{* classes are used for filtering *}
						{if $payment['recurrent'] == 1}recurrent{else}punctual{/if}
						recipient_{$payment['recipientFK']}
						type_{$payment['typeFK']}
						currency_{$payment['currencyFK']}
						method_{$payment['methodFK']}
						origin_{$payment['originFK']}
						status_{$payment['statusFK']}
						location_{$payment['locationFK']}
					" title="dernière modification le {$payment['modificationDate']|strtotime|date_format:'%d-%m-%y'}"

					{* data-* attributes are used for sorting *}
					data-date="{$payment['paymentDate']|strtotime}"
					data-recipient="{$recipients[ $payment['recipientFK'] ]}"
					data-method="{$methods[ $payment['methodFK'] ]}"
					data-origin="{$origins[ $payment['originFK'] ]}"
					data-status="{$statuses[ $payment['statusFK'] ]}"
					data-amount="{$payment['amount']}"
				>

					<a class="" data-icon="{rdelim}" href="{$payment['id']}" title="voir le détail"></a>

					<dl>
						<dd>{$types[ $payment['typeFK'] ]}</dd>
						<dd>{$payment['paymentDate']|strtotime|date_format:'%d-%m-%y'}</dd>
						<dd>le {$payment['label']}</dd>
						<dd>pour {$payment['amount']} {$currencies[ $payment['currencyFK'] ]}s</dd>
						<dd>depuis {$origins[ $payment['originFK'] ]} vers {$recipients[ $payment['recipientFK'] ]}</dd>
						<dd>en {$methods[ $payment['methodFK'] ]}</dd>
					</dl>
				</div>
			{/foreach}
	{if !$partial}
		</div>
	{/if}
{/if}
