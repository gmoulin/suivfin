{include "html_header.tpl"}

{* Navigation - top left*}
<nav id="owners">
	<ul class="button-bar">
		{strip}
			{foreach $owners as $id => $name}
				<li>
					<a href="?owner={$id}" title="voir le suivi de {$name}" class="{if $id == $owner}active{/if}">{$name}</a>
				</li>
			{/foreach}
		{/strip}
	</ul>
</nav>

{include "form.tpl"}

{* temporary, bottom left *}
<aside id="layouts">
	Affichage :
	<ul class="button-bar small">
		{strip}
			<li><a href="#masonry">masonry</a></li>
			<li><a href="#fitRows" class="active">fitRows</a></li>
			<li><a href="#cellsByRow">cellsByRow</a></li>
			<li><a href="#straightDown">straightDown</a></li>
			<li><a href="#masonryHorizontal" class="horizontal">masonryHorizontal</a></li>
			<li><a href="#fitColumns" class="horizontal">fitColumns</a></li>
			<li><a href="#cellsByColumn" class="horizontal">cellsByColumn</a></li>
		{/strip}
	</ul>
</aside>

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
	<ul class="button-bar small">
		{strip}
			<li><a href="#date" class="active">date</a></li>
			<li><a href="#recipient">bénéficiaire</a></li>
			<li><a href="#method">méthode</a></li>
			<li><a href="#origin">origine</a></li>
			<li><a href="#status">statut</a></li>
			<li><a href="#amount">montant</a></li>
		{/strip}
	</ul>
</aside>

{include "payment.tpl"}
{include "sum.tpl"}

<script>
	var limits = {$limits|json_encode};
</script>
{include "html_footer.tpl"}