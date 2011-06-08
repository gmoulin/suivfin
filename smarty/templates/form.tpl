{* add / update form, slide down from center top *}
<form id="payment_form" action="" method="post">
	<input type="hidden" id="id" name="id" value="">
	<input type="hidden" id="action" name="action" value="add">
	<fieldset>
		<h1>Paiement par {$owners[$owner]}</h1>
		<h2 class="offlineWarning">Les ajouts et modifications seront pris en compte lorsque vous repasserez en ligne.</h2>
		<ul class="clearfix">
			<li>
				<label for="type_1">Type</label>
			</li>
			<li>
				{foreach $types as $id => $name}
					<label for="type_{$id}"><input type="radio" name="typeFK" id="type_{$id}" value="{$id}" required> {$name}</label>
				{/foreach}
			</li>

			<li class="ownerChoice">
				<label for="transfert_owner_1">Receveur</label>
			</li>
			<li class="ownerChoice">
				{foreach $owners as $id => $name}
					<label for="transfert_owner_{$id}"><input type="radio" name="transfert_ownerFK" id="transfert_owner_{$id}" value="{$id}" {if $owner == $id}checked="checked"{/if}> {$name}</label>
				{/foreach}
			</li>

			<li>
				<label for="label">Libellé</label>
			</li>
			<li>
				<input type="text" id="label" name="label" list="labelList" value="" autocomplete="off" required placeholder="courses">
				<datalist id="labelList"></datalist>
			</li>

			<li>
				<label for="paymentDate">Date</label>
			</li>
			<li>
				<input type="{if stripos($smarty.server.HTTP_USER_AGENT, "safari")}text{else}date{/if}" id="paymentDate" name="paymentDate" value="" autocomplete="off" required pattern="^([012][123456789]|[123]0|31)\/([0][123456789]|[1][012])\/20[0-9]{ldelim}2{rdelim}$" placeholder="{$smarty.now|date_format:'%d/%m/%Y'}">
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
				<label for="recurrent_0"><input type="radio" id="recurrent_0" name="recurrent" value="0" checked="checked"> Non</label>
				<label for="recurrent_1"><input type="radio" id="recurrent_1" name="recurrent" value="1"> Oui</label>
			</li>

			<li>
				<label for="originFK">Origine</label>
			</li>
			<li>
				<input type="text" id="originFK" name="originFK" value="" autocomplete="off" required list="originList" placeholder="entreprise ou compte">
				<datalist id="originList"></datalist>
			</li>

			<li>
				<label for="currency_1">Monnaie</label>
			</li>
			<li>
				{foreach $currencies as $id => $name}
					<label for="currency_{$id}"><input type="radio" name="currencyFK" id="currency_{$id}" value="{$id}" required> {$name}</label>
				{/foreach}
			</li>

			<li>
				<label for="amount">Montant</label>
			</li>
			<li>
				<input type="number" id="amount" name="amount" value="" autocomplete="off" required min="0.0" placeholder="0.0" pattern="^[0-9]+[\.]?[0-9]*$">
			</li>

			<li>
				<label for="methodFK">Méthode</label>
			</li>
			<li>
				<input type="text" id="methodFK" name="methodFK" value="" autocomplete="off" required list="methodList" placeholder="carte">
				<datalist id="methodList"></datalist>
			</li>

			<li>
				<label for="recipientFK">Bénéficiaire</label>
			</li>
			<li>
				<input type="text" id="recipientFK" name="recipientFK" value="" autocomplete="off" required list="recipientList" placeholder="entreprise ou compte">
				<datalist id="recipientList"></datalist>
			</li>

			<li>
				<label for="status_1">Statut</label>
			</li>
			<li>
				{foreach $statuses as $id => $name}
					<label for="status_{$id}"><input type="radio" name="statusFK" id="status_{$id}" value="{$id}" required> {$name}</label>
				{/foreach}
			</li>

			<li>
				<label for="locationFK">Localisation</label>
			</li>
			<li>
				<input type="text" id="locationFK" name="locationFK" value="" autocomplete="off" required list="locationList" placeholder="Carouge">
				<datalist id="locationList"></datalist>
			</li>
		</ul>
		<div class="formButtons">
			<button type="submit" id="formSubmit" class="big button">Enregistrer</button>
			<button type="reset" id="formCancel" class="big button danger">Annuler</button>
		</div>
	</fieldset>
</form>