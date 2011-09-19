{* labels at the left, list or select on hover, @todo change markup accordingly *}
<aside id="filter">
	<h2>Filtres :</h2>

	<section>
		Fréquence : <output></output>
		<div class="button-group">
		{strip}
			<a href="#" class="primary button pill" data-group="recurrent" data-filter="*">Tout</a>
			<a href="#" class="button pill" data-group="recurrent" data-filter=".recurrent">Récurrent</a>
			<a href="#" class="button pill" data-group="recurrent" data-filter=".punctual">Ponctuel</a>
		{/strip}
		</div>
	</section>

	<section>
		Origine :
		<select name="origin" id="origin_filter">
			<option value="*" selected="selected">Tout</option>
		</select>
	</section>

	<section>
		Status : <output></output>
		<div class="button-group">
		{strip}
			<a href="#" class="primary button pill" data-group="status" data-filter="*">Tout</a>
			{foreach $statuses as $id => $name}
				<a href="#" class="button pill status_{$id}" data-group="status" data-filter=".status_{$id}" class="status_{$id}">{$name}</a>
			{/foreach}
		{/strip}
		</div>
	</section>

	<section>
		Bénéficiaire :
		<select name="recipient" id="recipient_filter">
			<option value="*" selected="selected">Tout</option>
		</select>
	</section>

	<section>
		Type : <output></output>
		<div class="button-group">
		{strip}
			<a href="#" class="primary button pill" data-group="type" data-filter="*">Tout</a>
			{foreach $types as $id => $name}
				<a href="#" class="button pill" data-group="type" data-filter=".type_{$id}">{$name}</a>
			{/foreach}
		{/strip}
		</div>
	</section>

	<section>
		Monnaie : <output></output>
		<div class="button-group">
		{strip}
			<a href="#" class="primary button pill" data-group="currency" data-filter="*">Tout</a>
			{foreach $currencies as $id => $name}
				<a href="#" class="button pill" data-group="currency" data-filter=".currency_{$id}">{$name}</a>
			{/foreach}
		{/strip}
		</div>
	</section>

	<section>
		Méthode : <output></output>
		<div class="button-group">
		{strip}
			<a href="#" class="primary button pill" data-group="method" data-filter="*">Tout</a>
			{foreach $methods as $id => $name}
				<a href="#" class="button pill" data-group="method" data-filter=".method_{$id}">{$name}</a>
			{/foreach}
		{/strip}
		</div>
	</section>

	<section>
		Localisation :
		<select name="location" id="location_filter">
			<option value="*" selected="selected">Tout</option>
		</select>
	</section>
</aside>
