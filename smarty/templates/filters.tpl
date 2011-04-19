{* labels at the left, list or select on hover, @todo change markup accordingly *}
<aside id="filter">
	<h2>Filtres :</h2>

	<section>
		Fréquence : <output></output>
		<div>
		{strip}
			<a href="#" class="primary button pill left" data-group="recurrent" data-filter="*">Tout</a>
			<a href="#" class="button pill middle" data-group="recurrent" data-filter=".recurrent">Récurrent</a>
			<a href="#" class="button pill right" data-group="recurrent" data-filter=".punctual">Ponctuel</a>
		{/strip}
		</div>
	</section>

	<section>
		Origine : <output></output>
		<select name="origin" id="origin_filter">
			{strip}
				<option value="*" selected="selected">Tout</option>
				{foreach $origins as $id => $name}
					<option value=".origin_{$id}">{$name}</option>
				{/foreach}
			{/strip}
		</select>
	</section>

	<section>
		Status : <output></output>
		<div>
			{strip}
				<a href="#" class="primary button pill left" data-group="status" data-filter="*">Tout</a>
				{foreach $statuses as $id => $name}
					<a href="#" class="button pill {if $name@last}right{else}middle{/if}" data-group="status" data-filter=".status_{$id}" class="status_{$id}">{$name}</a>
				{/foreach}
			{/strip}
		</div>
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
		<div>
			{strip}
				<a href="#" class="primary button pill left" data-group="type" data-filter="*">Tout</a>
				{foreach $types as $id => $name}
					<a href="#" class="button pill {if $name@last}right{else}middle{/if}" data-group="type" data-filter=".type_{$id}">{$name}</a>
				{/foreach}
			{/strip}
		<div>
	</section>

	<section>
		Monnaie : <output></output>
		<div>
			{strip}
				<a href="#" class="primary button pill left" data-group="currency" data-filter="*">Tout</a>
				{foreach $currencies as $id => $name}
					<a href="#" class="button pill {if $name@last}right{else}middle{/if}" data-group="currency" data-filter=".currency_{$id}">{$name}</a>
				{/foreach}
			{/strip}
		</div>
	</section>

	<section>
		Méthode : <output></output>
		<div>
			{strip}
				<a href="#" class="primary button pill left" data-group="method" data-filter="*">Tout</a>
				{foreach $methods as $id => $name}
					<a href="#" class="button pill {if $name@last}right{else}middle{/if}" data-group="method" data-filter=".method_{$id}">{$name}</a>
				{/foreach}
			{/strip}
		</div>
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
