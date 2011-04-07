{include "html_header.tpl"}
<nav id="owners">
	Comptes :
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

<form id="payment_form" action="" method="post">
	<input type="hidden" id="id" name="id" value="">
	<input type="hidden" id="action" name="action" value="add">
	<fieldset>
		<legend>Paiement</legend>
		<ul>
			<li>
				<label for="typeFK">Type</label>
			</li>
			<li>
				{foreach $types as $id => $name}
					<label for="type_{$id}">{$name}</label>
					<input type="radio" name="typeFK" id="type_{$id}" value="{$id}" required>
				{/foreach}
			</li>

			<li>
				<label for="label">Libellé</label>
			</li>
			<li>
				<input type="text" id="label" name="label" value="courses" autocomplete="off" required placeholder="courses">
			</li>

			<li>
				<label for="paymentDate">Date</label>
			</li>
			<li>
				<input type="date" id="paymentDate" name="paymentDate" value="{$smarty.now|date_format:'%d/%m/%Y'}" autocomplete="off" required pattern="^([012][123456789]|[123]0|31)\/([0][123456789]|[1][012])\/20[0-9]{ldelim}2{rdelim}$" placeholder="{$smarty.now|date_format:'%d/%m/%Y'}">
			</li>

			<li>
				<label for="amount">Montant</label>
			</li>
			<li>
				<input type="number" id="amount" name="amount" value="12" autocomplete="off" required min="0.0" placeholder="0.0">
			</li>

			<li>
				<label for="comment">Commentaire</label>
			</li>
			<li>
				<textarea id="comment" name="comment">test ajout</textarea>
			</li>

			<li>
				<label for="recurrent0">Récurrence</label>
			</li>
			<li>
				<input type="radio" id="recurrent0" name="recurrent" value="0" checked="checked"><label for="recurrent0">Non</label>
				<input type="radio" id="recurrent1" name="recurrent" value="1"><label for="recurrent1">Oui</label>
			</li>

			<li>
				<label for="recipientFK">Bénéficiaire</label>
			</li>
			<li>
				<input type="text" id="recipientFK" name="recipientFK" value="un chanceux" autocomplete="off" required list="recipientList" placeholder="entreprise ou compte">
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
						<label for="origin_{$id}">{$name}</label>
						<input type="radio" name="originFK" id="origin_{$id}" value="{$id}" class="origins_switch" required>
					{/if}
				{/foreach}
			</li>

			<li>
				<label for="currencyFK">Monnaie</label>
			</li>
			<li>
				{foreach $currencies as $id => $name}
					<label for="currency_{$id}">{$name}</label>
					<input type="radio" name="currencyFK" id="currency_{$id}" value="{$id}" required readonly>
				{/foreach}
			</li>

			<li>
				<label for="statusFK">Statut</label>
			</li>
			<li>
				{foreach $statuses as $id => $name}
					<label for="status_{$id}">{$name}</label>
					<input type="radio" name="statusFK" id="status_{$id}" value="{$id}" required>
				{/foreach}
			</li>

			<li>
				<label for="locationFK">location</label>
			</li>
			<li>
				<input type="text" id="locationFK" name="locationFK" value="" autocomplete="off" required list="locationList" placeholder="Carouge">
				<datalist id="locationList"></datalist>
			</li>
		</ul>
		<div class="formButtons">
			<button type="submit" id="formSubmit" name="formSubmit" class="button formButton" data-icon="y" rel="">Enregistrer</button>
			<button type="reset" id="formCancel" name="formCancel" class="button formButton" data-icon="x" rel="cancel">Annuler</button>
		</div>
	</fieldset>
</form>


<aside id="layouts">
	Affichage :
	<ul class="button-bar">
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

<aside id="time_frame">
	Années (et mois) à afficher :
	<h2>Année</h2>
	<ul class="filter" data-group="year">
		{foreach $yearsAndMonths as $y => $months}
			<li>
				<input type="checkbox" class="year" id="year_{$y}" value="{$y}" {if $y == $currentYear}checked="checked"{/if}>
				<label for="year_{$y}">
					{$y}
				</label>
				<div>
					<h3>Mois</h3>
					<ul class="filter" data-group="month_{$y}">
						{foreach $months as $m}
							<li>
								<input type="checkbox" id="year_{$y}_month_{$m}" value="{$y}-{$m}" {if $y == $currentYear && $m == $currentMonth}checked="checked"{/if}>
								<label for="year_{$y}_month_{$m}">
									{$lang_months[$m]}
								</label>
							</li>
						{/foreach}
					</ul>
				</div>
			<detail>
			</li>
		{/foreach}
	</ul>
</aside>

<aside id="filter">
	Filtres :

	<label>Fréquence</label>
	<ul class="filter button-bar">
		{strip}
			<li><a href="#" class="active" data-group="recurrent" data-filter="*">Tout</a></li>
			<li><a href="#" data-group="recurrent" data-filter=".recurrent">Récurrent</a></li>
			<li><a href="#" data-group="recurrent" data-filter=".punctual">Ponctuel</a></li>
		{/strip}
	</ul>

	<label>Origine</label>
	<ul class="filter button-bar">
		{strip}
			<li><a href="#" class="active" data-group="origin" data-filter="*">Tout</a></li>
			{foreach $origins as $id => $name}
				{if array_key_exists($id, $limits)}
					<li><a href="#" data-group="origin" data-filter=".origin_{$id}">{$name}</a></li>
				{/if}
			{/foreach}
		{/strip}
	</ul>

	<label>Status</label>
	<ul class="filter button-bar">
		{strip}
			<li><a href="#" class="active" data-group="status" data-filter="*">Tout</a></li>
			{foreach $statuses as $id => $name}
				<li><a href="#" data-group="status" data-filter=".status_{$id}">{$name}</a></li>
			{/foreach}
		{/strip}
	</ul>

	<label for="recipient_filter">Bénéficiaire</label>
	<select name="recipient" id="recipient_filter">
		{strip}
			<option value="*" selected="selected">Tout</option>
			{foreach $recipients as $id => $name}
				<option value=".recipient_{$id}">{$name}</option>
			{/foreach}
		{/strip}
	</select>

	<label>Type</label>
	<ul class="filter button-bar">
		{strip}
			<li><a href="#" class="active" data-group="type" data-filter="*">Tout</a></li>
			{foreach $types as $id => $name}
				<li><a href="#" data-group="type" data-filter=".type_{$id}">{$name}</a></li>
			{/foreach}
		{/strip}
	</ul>

	<label>Monnaie</label>
	<ul class="filter button-bar">
		{strip}
			<li><a href="#" class="active" data-group="currency" data-filter="*">Tout</a></li>
			{foreach $currencies as $id => $name}
				<li><a href="#" data-group="currency" data-filter=".currency_{$id}">{$name}</a></li>
			{/foreach}
		{/strip}
	</ul>

	<label>Méthode</label>
	<ul class="filter button-bar">
		{strip}
			<li><a href="#" class="active" data-group="method" data-filter="*">Tout</a></li>
			{foreach $methods as $id => $name}
				<li><a href="#" data-group="method" data-filter=".method_{$id}">{$name}</a></li>
			{/foreach}
		{/strip}
	</ul>

	<label for="location_filter">Localisation</label>
	<select name="location" id="location_filter">
		{strip}
			<option value="*" selected="selected">Tout</option>
			{foreach $locations as $id => $name}
				<option value=".location_{$id}">{$name}</option>
			{/foreach}
		{/strip}
	</select>
</aside>

<aside id="sort">
	Tri :
	<ul class="button-bar">
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


{assign var=partial value=false}
{include "payment.tpl"}
{include "sum.tpl"}
{include "forecast.tpl"}

<script>
	var limits = {$limits|json_encode};
</script>
{include "html_footer.tpl"}
