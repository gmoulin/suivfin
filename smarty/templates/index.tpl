{strip}
	{include "html_header.tpl"}

	<header>
		<input type="hidden" id="current_owner" value="{$owner}">
		<nav class="owners button-group">
			{foreach $owners as $id => $name}
				<a href="?owner={$id}" title="voir le suivi de {$name}" class="button pill {if $id == $owner}primary{/if}">{$name}</a>
			{/foreach}
		</nav>
		<nav class="owners icons button-group">
			{foreach $owners as $id => $name}
				<a href="?owner={$id}" title="voir le suivi de {$name}" class="button pill icon icon-only user {if $id == 3}couple{/if} {if $id == $owner}primary{/if}">{$name}</a>
			{/foreach}
		</nav>

		<aside class="next_month icon">
			<a href="#" class="button pill danger icon icon-only calendar" title="la génération sera faite pour tous les comptes et personnes">Générer les paiements récurrents pour le mois prochain</a>
		</aside>

		<aside class="form_switch">
			<a href="#" title="afficher le formulaire" class="button pill">Ajouter</a>
		</aside>

		<aside class="switch_view icon">
			<a href="#" class="button pill icon icon-only clock" title="alterner entre la liste et le graphique">Alterner la présentation</a>
		</aside>

		<aside class="form_switch icon">
			<a href="#" title="afficher le formulaire" class="button pill icon icon-only add">Ajouter</a>
		</aside>

		<aside id="time_frame">
			<ul class="filter" data-group="year">
				{foreach $yearsAndMonths as $y => $months}
					<li>
						<input type="checkbox" class="year" id="year_{$y}" value="{$y}" {if array_key_exists($y, $selectedTimeFrame)}checked="checked"{/if} autocomplete="off">
						<label for="year_{$y}">{$y}</label>
						<span class="switch"></span>
						<ul class="filter" data-group="month_{$y}">
							{foreach $months as $m}
								<li>
									<input type="checkbox" id="year_{$y}_month_{$m}" value="{$y}-{$m}" {if isset($selectedTimeFrame[$y]) && in_array($m, $selectedTimeFrame[$y])}checked="checked"{/if} autocomplete="off">
									<label for="year_{$y}_month_{$m}" title="{$monthsTranslation[$m]}">
										{$monthsTranslation[$m]|substr:0:1}
									</label>
								</li>
							{/foreach}
						</ul>
					</li>
				{/foreach}
			</ul>
		</aside>
		<aside class="chart_type">
			<h2>Type : </h2>
			<div class="button-group">
				<a href="#" rel="expense" class="primary button pill">Dépenses</a>
				<a href="#" rel="evolution" class="button pill">Total</a>
				{*<a href="#" rel="recipient" class="button pill">Bénéficiaire</a>*}
			</div>
		</aside>
	</header>
	<footer>
		<aside class="next_month">
			<a href="#" class="button pill danger" title="la génération sera faite pour tous les comptes et personnes">Générer les paiements récurrents pour le mois prochain</a>
		</aside>

		<aside class="switch_view">
			<a href="#" class="button pill" title="alterner entre la liste et le graphique">Alterner la présentation</a>
		</aside>

		<aside class="chart_type">
			<h2>Type : </h2>
			<div class="button-group">
				<a href="#" rel="expense" class="primary button pill">Dépenses</a>
				<a href="#" rel="evolution" class="button pill">Total</a>
				{*<a href="#" rel="recipient" class="button pill">Bénéficiaire</a>*}
			</div>
		</aside>
	</footer>

	{include "form.tpl"}

	<aside id="calculs" class="clearfix">
		<div class="toggler"></div>
		{assign var=partial value=false}
		{include "balance.tpl"}
		{include "forecast.tpl"}
		<section id="sort">
			<h2>Tri : </h2>
			<div class="button-group">
				<a href="#date" class="primary button pill">date</a>
				<a href="#recipient" class="button pill">bénéficiaire</a>
				<a href="#method" class="button pill">méthode</a>
				<a href="#origin" class="button pill">origine</a>
				<a href="#status" class="button pill">statut</a>
				<a href="#amount" class="button pill">montant</a>
			</div>
		</section>
		{include "filters.tpl"}
		{include "sum.tpl"}
	</aside>

	{include "payment.tpl"}

	<section id="chart"></section>

	{include "html_footer.tpl"}
{/strip}
