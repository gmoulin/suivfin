{include "html_header.tpl"}

{* Navigation - top left*}
<nav id="owners">
	{strip}
		{foreach $owners as $id => $name}
			<a href="?owner={$id}" title="voir le suivi de {$name}" class="button pill {if $name@first}left{elseif $name@last}right{else}middle{/if} {if $id == $owner}primary{/if}">{$name}</a>
		{/foreach}
	{/strip}
</nav>

<aside id="next_month">
	<a href="#" class="button pill" title="la génération sera faite pour tous les comptes et personnes">Générer les paiements récurrents pour le mois prochain</a>
</aside>

{include "form.tpl"}

{* top right, years visible, months on hover *}
<aside id="time_frame">
	Années (et mois) à afficher :
	<ul class="filter" data-group="year">
		{foreach $yearsAndMonths as $y => $months}
			<li>
				<input type="checkbox" class="year" id="year_{$y}" value="{$y}" {if $y == $currentYear}checked="checked"{/if} autocomplete="off">
				<label for="year_{$y}">
					{$y}
				</label>
				<ul class="filter" data-group="month_{$y}">
					{foreach $months as $m}
						<li>
							<input type="checkbox" id="year_{$y}_month_{$m}" value="{$y}-{$m}" {if $y == $currentYear && $m == $currentMonth}checked="checked"{/if} autocomplete="off">
							<label for="year_{$y}_month_{$m}">
								{$lang_months[$m]}
							</label>
						</li>
					{/foreach}
				</ul>
			</li>
		{/foreach}
	</ul>
</aside>

{assign var=partial value=false}
{include "forecast.tpl"}

{include "filters.tpl"}

{* bottom right *}
<aside id="sort">
	Tri :
	{strip}
		<a href="#date" class="primary button pill left">date</a>
		<a href="#recipient" class="button pill middle">bénéficiaire</a>
		<a href="#method" class="button pill middle">méthode</a>
		<a href="#origin" class="button pill middle">origine</a>
		<a href="#status" class="button pill middle">statut</a>
		<a href="#amount" class="button pill right">montant</a>
	{/strip}
</aside>

{include "payment.tpl"}
{include "sum.tpl"}

{include "html_footer.tpl"}