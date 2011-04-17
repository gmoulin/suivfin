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

{* add / update (@todo) form, slide down from top, need show icon and shortcut support *}
<aside id="form_switch">
	<a href="#" title="afficher le formulaire" class="button round2 add small">Ajouter</a>
</aside>
<form id="payment_form" action="" method="post">
	<input type="hidden" id="id" name="id" value="">
	<input type="hidden" id="action" name="action" value="add">
	<fieldset>
		<h1>Paiement</h1>
		<ul class="clearfix">
			<li>
				<label for="typeFK">Type</label>
			</li>
			<li>
				{foreach $types as $id => $name}
					<input type="radio" name="typeFK" id="type_{$id}" value="{$id}" required>
					<label for="type_{$id}">{$name}</label>
				{/foreach}
			</li>

			<li>
				<label for="label">Libellé</label>
			</li>
			<li>
				<input type="text" id="label" name="label" value="" autocomplete="off" required placeholder="courses">
			</li>

			<li>
				<label for="paymentDate">Date</label>
			</li>
			<li>
				<input type="date" id="paymentDate" name="paymentDate" value="" autocomplete="off" required pattern="^([012][123456789]|[123]0|31)\/([0][123456789]|[1][012])\/20[0-9]{ldelim}2{rdelim}$" placeholder="{$smarty.now|date_format:'%d/%m/%Y'}">
			</li>

			<li>
				<label for="amount">Montant</label>
			</li>
			<li>
				<input type="number" id="amount" name="amount" value="" autocomplete="off" required min="0.0" placeholder="0.0">
			</li>

			<li>
				<label for="comment">Commentaire</label>
			</li>
			<li>
				<textarea id="comment" name="comment" placeholder="précisions"></textarea>
			</li>

			<li>
				<label for="recurrent_0">Récurrence</label>
			</li>
			<li>
				<input type="radio" id="recurrent_0" name="recurrent" value="0" checked="checked"><label for="recurrent_0">Non</label>
				<input type="radio" id="recurrent_1" name="recurrent" value="1"><label for="recurrent_1">Oui</label>
			</li>

			<li>
				<label for="recipientFK">Bénéficiaire</label>
			</li>
			<li>
				<input type="text" id="recipientFK" name="recipientFK" value="" autocomplete="off" required list="recipientList" placeholder="entreprise ou compte">
				<datalist id="recipientList"></datalist>
			</li>

			<li>
				<label for="methodFK">Méthode</label>
			</li>
			<li>
				<input type="text" id="methodFK" name="methodFK" value="" autocomplete="off" required list="methodList" placeholder="carte">
				<datalist id="methodList"></datalist>
			</li>

			<li>
				<label for="originFK">Origine</label>
			</li>
			<li>
				{foreach $origins as $id => $name}
					{if array_key_exists($id, $limits)}
						<input type="radio" name="originFK" id="origin_{$id}" value="{$id}" class="origins_switch" required>
						<label for="origin_{$id}">{$name}</label>
					{/if}
				{/foreach}
			</li>

			<li>
				<label for="currencyFK">Monnaie</label>
			</li>
			<li>
				{foreach $currencies as $id => $name}
					<input type="radio" name="currencyFK" id="currency_{$id}" value="{$id}" required readonly>
					<label for="currency_{$id}">{$name}</label>
				{/foreach}
			</li>

			<li>
				<label for="statusFK">Statut</label>
			</li>
			<li>
				{foreach $statuses as $id => $name}
					<input type="radio" name="statusFK" id="status_{$id}" value="{$id}" required>
					<label for="status_{$id}">{$name}</label>
				{/foreach}
			</li>

			<li>
				<label for="locationFK">Location</label>
			</li>
			<li>
				<input type="text" id="locationFK" name="locationFK" value="" autocomplete="off" required list="locationList" placeholder="Carouge">
				<datalist id="locationList"></datalist>
			</li>
		</ul>
		<div class="formButtons">
			<button type="submit" id="formSubmit" name="formSubmit" class="formButton" data-icon="y" rel="">Enregistrer</button>
			<button type="reset" id="formCancel" name="formCancel" class="formButton" data-icon="x" rel="cancel">Annuler</button>
		</div>
	</fieldset>
</form>

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

{* labels at the left, list or select on hover, @todo change markup accordingly *}
<aside id="filter">
	<h2>Filtres :</h2>

	<section>
		Fréquence : <output></output>
		<ul class="filter button-bar small">
			{strip}
				<li><a href="#" class="active" data-group="recurrent" data-filter="*">Tout</a></li>
				<li><a href="#" data-group="recurrent" data-filter=".recurrent">Récurrent</a></li>
				<li><a href="#" data-group="recurrent" data-filter=".punctual">Ponctuel</a></li>
			{/strip}
		</ul>
	</section>

	<section>
		Origine : <output></output>
		<ul class="filter button-bar small">
			{strip}
				<li><a href="#" class="active" data-group="origin" data-filter="*">Tout</a></li>
				{foreach $origins as $id => $name}
					{if array_key_exists($id, $limits)}
						<li><a href="#" data-group="origin" data-filter=".origin_{$id}">{$name}</a></li>
					{/if}
				{/foreach}
			{/strip}
		</ul>
	</section>

	<section>
		Status : <output></output>
		<ul class="filter button-bar small">
			{strip}
				<li><a href="#" class="active" data-group="status" data-filter="*">Tout</a></li>
				{foreach $statuses as $id => $name}
					<li><a href="#" data-group="status" data-filter=".status_{$id}" class="status_{$id}">{$name}</a></li>
				{/foreach}
			{/strip}
		</ul>
	</section>

	<section>
		Bénéficiaire :
		<select name="recipient" id="recipient_filter">
			{strip}
				<option value="*" selected="selected">Tout</option>
				{foreach $recipients as $id => $name}
					<option value=".recipient_{$id}">{$name}</option>
				{/foreach}
			{/strip}
		</select>
	</section>

	<section>
		Type : <output></output>
		<ul class="filter button-bar small">
			{strip}
				<li><a href="#" class="active" data-group="type" data-filter="*">Tout</a></li>
				{foreach $types as $id => $name}
					<li><a href="#" data-group="type" data-filter=".type_{$id}">{$name}</a></li>
				{/foreach}
			{/strip}
		</ul>
	</section>

	<section>
		Monnaie : <output></output>
		<ul class="filter button-bar small">
			{strip}
				<li><a href="#" class="active" data-group="currency" data-filter="*">Tout</a></li>
				{foreach $currencies as $id => $name}
					<li><a href="#" data-group="currency" data-filter=".currency_{$id}">{$name}</a></li>
				{/foreach}
			{/strip}
		</ul>
	</section>

	<section>
		Méthode : <output></output>
		<ul class="filter button-bar small">
			{strip}
				<li><a href="#" class="active" data-group="method" data-filter="*">Tout</a></li>
				{foreach $methods as $id => $name}
					<li><a href="#" data-group="method" data-filter=".method_{$id}">{$name}</a></li>
				{/foreach}
			{/strip}
		</ul>
	</section>

	<section>
		Localisation :
		<select name="location" id="location_filter">
			{strip}
				<option value="*" selected="selected">Tout</option>
				{foreach $locations as $id => $name}
					<option value=".location_{$id}">{$name}</option>
				{/foreach}
			{/strip}
		</select>
	</section>
</aside>

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