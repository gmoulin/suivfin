{if !$partial}
	<div id="container" class="clearfix">
{/if}

{if !empty($payments)}
	{foreach $payments as $payment}
		{* classes are used for filtering *}
		<div class="item {if $payment['recurrent'] == 1}recurrent{else}punctual{/if} recipient_{$payment['recipientFK']} type_{$payment['typeFK']} currency_{$payment['currencyFK']} method_{$payment['methodFK']} origin_{$payment['originFK']} status_{$payment['statusFK']} location_{$payment['locationFK']}"
			title="dernière modification le {$payment['modificationDate']|strtotime|date_format:'%d-%m-%y'}"

			{* data-* attributes are used for sorting *}
			data-date="{$payment['paymentDate']|strtotime}"
			data-recipient="{$recipients[ $payment['recipientFK'] ]}"
			data-method="{$methods[ $payment['methodFK'] ]}"
			data-origin="{$origins[ $payment['originFK'] ]}"
			data-status="{$statuses[ $payment['statusFK'] ]}"
			data-amount="{$payment['amount']}"
		>

			<div class="buttons">
				<a class="button edit" href="{$payment['id']}" title="Editer ce paiement"><span class="pen icon"></span>Editer</a>
				<a class="button delete" href="{$payment['id']}" title="Supprimer ce paiement"><span class="trash icon"></span>Supprimer</a>
			</div>
			<dl>
				<dd>
					{$types[ $payment['typeFK'] ]|capitalize}
					<span>{$payment['paymentDate']|strtotime|date_format:'%d-%m-%y'}</span>
				</dd>
				<dd>{$payment['label']}</dd>
				<dd>
					<strong>{$payment['amount']|number_format:2:'.':'\''} {$currenciesWSymbol[ $payment['currencyFK'] ].symbol}</strong>
					par {$methods[ $payment['methodFK'] ]}
				</dd>
				<dd>
					{$origins[ $payment['originFK'] ]} → {$recipients[ $payment['recipientFK'] ]}
				</dd>
			</dl>
		</div>
	{/foreach}
{/if}

{if !$partial}
	</div>
{/if}
