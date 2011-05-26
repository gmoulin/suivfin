{strip}
	{if !$partial}
		<section id="balances">
			<h2>Soldes</h2>
	{/if}

	{if !empty($balances)}
		{foreach $balances as $origin => $info}
			<strong>{$origin} :&nbsp;</strong>
			<span>{$info.balance}</span>
			<span>{$info.symbol}</span>
			<br />
		{/foreach}
	{/if}

	{if !$partial}
		</section>
	{/if}
{/strip}
