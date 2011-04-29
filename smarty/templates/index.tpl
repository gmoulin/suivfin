{include "html_header.tpl"}

{* Navigation - top left*}
<nav id="owners" class="button-group">
	{strip}
		{foreach $owners as $id => $name}
			<a href="?owner={$id}" title="voir le suivi de {$name}" class="button pill {if $id == $owner}primary{/if}">{$name}</a>
		{/foreach}
	{/strip}
</nav>

<aside id="next_month">
	<a href="#" class="button pill danger" title="la génération sera faite pour tous les comptes et personnes">Générer les paiements récurrents pour le mois prochain</a>
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
								{$monthsTranslation[$m]}
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
	<h2>Tri : </h2>
	<div class="button-group">
		{strip}
			<a href="#date" class="primary button pill">date</a>
			<a href="#recipient" class="button pill">bénéficiaire</a>
			<a href="#method" class="button pill">méthode</a>
			<a href="#origin" class="button pill">origine</a>
			<a href="#status" class="button pill">statut</a>
			<a href="#amount" class="button pill">montant</a>
		{/strip}
	</div>
</aside>

{include "payment.tpl"}
{include "sum.tpl"}


<aside id="switch_view">
	<a href="#" class="button pill" title="alterner entre la liste et le graphique">Alterner la présentation</a>
</aside>
<aside id="chart_type">
	<h2>Type : </h2>
	<div class="button-group">
		{strip}
			<a href="#" rel="expense" class="primary button pill">Dépenses</a>
			<a href="#" rel="evolution" class="button pill">Total</a>
			<a href="#" rel="recipient" class="button pill">Bénéficiaire</a>
		{/strip}
	</div>
</aside>
<section id="chart"></section>

{include "html_footer.tpl"}