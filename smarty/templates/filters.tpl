{* labels at the left, list or select on hover, @todo change markup accordingly *}
<section id="filter" class="deploy">
	<h2>Filtres :<span class="switch active"></span></h2>

	<section>
		<h3>Fréquence :</h3>
		<ul>
		{strip}
			<li>
				<label for="frequency-all">
					<input type="radio" id="frequency-all" name="recurrent" value="*" checked />
					Tout
				</label>
			</li>
			<li>
				<label for="frequency-recurrent">
					<input type="radio" id="frequency-recurrent" name="recurrent" value=".recurrent" />
					Récurrent
				</label>
			</li>
			<li>
				<label for="frequency-punctual">
					<input type="radio" id="frequency-punctual" name="recurrent" value=".punctual" />
					Ponctuel
				</label>
			</li>
		{/strip}
		</ul>
	</section>

	<section>
		<h3>Origine :</h3>
		<span class="switch"></span>
		<output></output>
		<div class="checkbox dropdown" id="origin_filter">
			<input type="search" id="origin_search" value="" autocomplete="off" />
			<span class="reset" rel="origin_search">x</span>
			<ul>
			{strip}
				<li data-order="0">
					<label for="origin-all">
						<input type="checkbox" id="origin-all" name="origin" value="*" checked />
						Tout
					</label>
				</li>
			{/strip}
			</ul>
		</div>
	</section>

	<section>
		<h3>Status :</h3>
		<span class="switch"></span>
		<output></output>
		<div class="checkbox dropdown">
			<ul>
			{strip}
				<li>
					<label for="status-all">
						<input type="checkbox" id="status-all" name="status" value="*" checked />
						Tout
					</label>
				</li>
				{foreach $statuses as $id => $name}
					<li>
						<label for="status-{$id}" class="status_{$id}">
							<input type="checkbox" id="status-{$id}" name="status" value=".status_{$id}" />
							{$name}
						</label>
					</li>
				{/foreach}
			{/strip}
			</ul>
		</div>
	</section>

	<section>
		<h3>Bénéficiaire :</h3>
		<span class="switch"></span>
		<output></output>
		<div class="checkbox dropdown" id="recipient_filter">
			<input type="search" id="recipient_search" value="" autocomplete="off" />
			<span class="reset" rel="recipient_search">x</span>
			<ul>
			{strip}
				<li data-order="0">
					<label for="recipient-all">
						<input type="checkbox" id="recipient-all" name="recipient" value="*" checked />
						Tout
					</label>
				</li>
			{/strip}
			</ul>
		</div>
	</section>

	<section>
		<h3>Type :</h3>
		<span class="switch"></span>
		<output></output>
		<div class="checkbox dropdown">
			<ul>
			{strip}
				<li>
					<label for="type-all">
						<input type="checkbox" id="type-all" name="type" value="*" checked />
						Tout
					</label>
				</li>
				{foreach $types as $id => $name}
					<li>
						<label for="type-{$id}">
							<input type="checkbox" id="type-{$id}" name="type" value=".type_{$id}" />
							{$name}
						</label>
					</li>
				{/foreach}
			{/strip}
			</ul>
		</div>
	</section>

	<section>
		<h3>Monnaie :</h3>
		<ul>
		{strip}
			<li>
				<label for="currency-all">
					<input type="radio" id="currency-all" name="currency" value="*" checked />
					Tout
				</label>
			</li>
			{foreach $currencies as $id => $name}
				<li>
					<label for="currency-{$id}">
						<input type="radio" id="currency-{$id}" name="currency" value=".currency_{$id}" />
						{$name}
					</label>
				</li>
			{/foreach}
		{/strip}
		</ul>
	</section>

	<section>
		<h3>Méthode :</h3>
		<span class="switch"></span>
		<output></output>
		<div class="checkbox dropdown">
			<ul>
			{strip}
				<li>
					<label for="method-all">
						<input type="checkbox" id="method-all" name="method" value="*" checked />
						Tout
					</label>
				</li>
				{foreach $methods as $id => $name}
					<li>
						<label for="method-{$id}">
							<input type="checkbox" id="method-{$id}" name="method" value=".method_{$id}" />
							{$name}
						</label>
					</li>
				{/foreach}
			{/strip}
			</ul>
		</div>
	</section>

	<section>
		<h3>Localisation :</h3>
		<span class="switch"></span>
		<output></output>
		<div class="checkbox dropdown" id="location_filter">
			<input type="search" id="location_search" value="" autocomplete="off" />
			<span class="reset" rel="location_search">x</span>
			<ul>
			{strip}
				<li data-order="0">
					<label for="location-all">
						<input type="checkbox" id="location-all" name="location" value="*" checked />
						Tout
					</label>
				</li>
			{/strip}
			</ul>
		</div>
	</section>

	<section>
		<h3>Période :</h3>
		<label for="date_from">Du</label>
		<input type="text" name="from" id="date_from" value="" pattern="^([012][123456789]|[123]0|31)\/([0][123456789]|[1][012])\/20[0-9]{ldelim}2{rdelim}$" />
		<span class="reset" rel="date_from">x</span>
		<label for="date_to">Au</label>
		<input type="text" name="to" id="date_to" value="" value="" pattern="^([012][123456789]|[123]0|31)\/([0][123456789]|[1][012])\/20[0-9]{ldelim}2{rdelim}$" />
		<span class="reset" rel="date_to">x</span>
	</section>
</section>
