{strip}
	{include "html_header.tpl"}

	<header>
		<input type="hidden" id="current_owner" value="{$owner}">
		<nav class="owners button-group">
			{foreach $owners as $id => $name}
				<a href="?owner={$id}" title="voir le suivi de {$name}" class="button pill {if $id == $owner}primary{/if}">{$name}</a>
			{/foreach}
		</nav>

		<aside class="form_switch">
			<a href="#" title="afficher le formulaire" class="button pill">Ajouter</a>
		</aside>

		<nav id="time_frame">
			<ul data-group="year">
				{foreach $yearsAndMonths as $y => $months}
					<li>
						<input type="checkbox" class="year" id="year_{$y}" value="{$y}" {if array_key_exists($y, $selectedTimeFrame)}checked="checked"{/if} autocomplete="off">
						<label for="year_{$y}">{$y}</label>
						<span class="switch"></span>
						<ul data-group="month_{$y}">
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
		</nav>
	</header>
	<footer>
		<nav class="mobile button-group left">
			{foreach $owners as $id => $name}
				<a href="?owner={$id}" title="voir le suivi de {$name}" class="button big {if $id == $owner}primary{/if}">{$name|capitalize|substr:0:1}</a>
			{/foreach}
		</nav>
		<nav class="mobile button-group right">
			<a href="#" class="form_switch button big icon icon-only add" title="afficher le formulaire">Ajouter</a>
			<a href="#" class="switch_view button big icon icon-only clock" title="alterner entre la liste et le graphique">Alterner la présentation</a>
			<a href="#" class="next_month button big danger icon icon-only calendar" title="la génération sera faite pour tous les comptes et personnes">Générer les paiements récurrents pour le mois prochain</a>
		</nav>
		<nav class="mobile button-group left chart_view">
			<a href="#" rel="expense" class="chart_type primary button big">Dépenses</a>
			<a href="#" rel="evolution" class="chart_type button big">Total</a>
			<a href="#" rel="recipient" class="chart_type button big">Bénéficiaire</a>
			<a href="#" class="switch_view button big icon icon-only clock" title="alterner entre la liste et le graphique">Alterner la présentation</a>
		</nav>

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
				<a href="#" rel="recipient" class="button pill">Bénéficiaire</a>
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

	<div class="box">
		<input type="radio" id="modalShow" name="modalToggle" class="boxToggleInput" autocomplete="off" />
		<div id="modalOverlay" class="overlay">
			<div class="wrapper">
				<div class="block">
				</div>
			</div>
		</div>
	</div>
	<input type="radio" id="modalHide" name="modalToggle" class="boxToggleInput" autocomplete="off" />

	{include "html_footer.tpl"}
{/strip}
