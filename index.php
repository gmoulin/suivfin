<?php
try {
	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'application de gestion des paiements';
	$metadata['motscles'] = 'gestion, débit, crédit, transfert, compte, banque, monnaie, balance, paiement';
	$lang = 'fr';

    $css = filemtime( LMS_PATH.'/css/style.css' );

    $js = filemtime( LMS_PATH.'/js/script.js' );


	$payments = new payment()->loadForCurrentMonth();
	$origins = new origin()->loadListForFilter();
	$statuses = new status()->loadListForFilter();
	$recipients = new recipient()->loadListForFilter();
	$types = new type()->loadListForFilter();
	$currencies = new currency()->loadListForFilter();
	$methods = new method()->loadListForFilter();
	$locations = new location()->loadListForFilter();


} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>
<?php include('html_header.php'); ?>

<nav>
	<ul>
		<?php foreach( $owners as $id => $owner ){
			echo '<li><a href="?owner='.$id.'" title="voir le suivi de '.$owner.'">'.$owner.'</a>';
		?>
	</ul>
</nav>

<form id="payment_form" action="" method="post">
	<input type="hidden" name="id" value="">
	<input type="hidden" id="action" name="action" value="add">
	<fieldset>
		<legend>Paiement</legend>
		<ul>
			<li>
				<label for="label">Libellé</label>
			</li>
			<li>
				<input type="text" id="label" name="label" value="" autocomplete="off" autofocus required placeholder="courses">
			</li>

			<li>
				<label for="paiementDate">Date</label>
			</li>
			<li>
				<input type="date" id="paiementDate" name="paiementDate" value="" autocomplete="off" required pattern="^([012][123456789]|[3][01])\/([0][123456789]|[1][12])\/[20][0-9]{2}$" placeholder="<?php echo $date('d/m/Y'); ?>">
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
				<textarea id="comment" name="comment"></textarea>
			</li>

			<li>
				<label for="recurrent0">Récurrence</label>
			</li>
			<li>
				<input type="radio" id="recurrent0" name="recurrent" value="0"><label for="recurrent0">Non</label>
				<input type="radio" id="recurrent1" name="recurrent" value="1" checked><label for="recurrent1">Oui</label>
			</li>

			<li>
				<label for="recipientFK">Bénéficiaire</label>
			</li>
			<li>
				<input type="text" id="recipientFK" name="recipientFK" value="" autocomplete="off" required datalist="recipientList" placeholder="Migros">
				<datalist id="recipientList"></datalist>
			</li>

			<li>
				<label for="typeFK">Type</label>
			</li>
			<li>
				<select id="typeFK" name="typeFK" autocomplete="off" required>
					<option value=""></option>
				</select>
			</li>

			<li>
				<label for="currencyFK">Monnaie</label>
			</li>
			<li>
				<select id="currencyFK" name="currencyFK" autocomplete="off" required>
					<option value=""></option>
				</select>
			</li>

			<li>
				<label for="methodFK">Méthode</label>
			</li>
			<li>
				<input type="text" id="methodFK" name="methodFK" value="" autocomplete="off" required datalist="methodList" placeholder="carte">
				<datalist id="methodList"></datalist>
			</li>

			<li>
				<label for="originFK">Origine</label>
			</li>
			<li>
				<input type="text" id="originFK" name="originFK" value="" autocomplete="off" required datalist="originList" placeholder="liquide CHF">
				<datalist id="originList"></datalist>
			</li>

			<li>
				<label for="statusFK">Statut</label>
			</li>
			<li>
				<select id="statusFK" name="statusFK" autocomplete="off" required>
					<option value=""></option>
				</select>
			</li>

			<li>
				<label for="ownerFK">Compte</label>
			</li>
			<li>
				<select id="ownerFK" name="ownerFK" autocomplete="off" required>
					<option value=""></option>
				</select>
			</li>

			<li>
				<label for="locationFK">location</label>
			</li>
			<li>
				<input type="text" id="locationFK" name="locationFK" value="" autocomplete="off" required datalist="locationList" placeholder="Carouge">
				<datalist id="locationList"></datalist>
			</li>
		</ul>
		<div class="formButtons">
			<button type="submit" id="formSubmit" name="formSubmit" class="button formButton" data-icon="y" rel="">Enregistrer</button>
			<button type="reset" id="formCancel" name="formCancel" class="button formButton" data-icon="x" rel="cancel">Annuler</button>
		</div>
	</fieldset>
</form>

<ul id="layouts">
	<li><a href="#masonry" class="selected" >masonry</a></li>
	<li><a href="#fitRows">fitRows</a></li>
	<li><a href="#cellsByRow">cellsByRow</a></li>
	<li><a href="#straightDown">straightDown</a></li>
	<li><a href="#masonryHorizontal" class="horizontal" >masonryHorizontal</a></li>
	<li><a href="#fitColumns" class="horizontal" >fitColumns</a></li>
	<li><a href="#cellsByColumn" class="horizontal">cellsByColumn</a></li>
</ul>

<aside id="timeframe">
	<label>Année</label>
	<ul class="filter" data-group="year">
		<?php for( $y = 2011; $y <= date('Y'); $y++ ){ ?>
			<li><label for="year_<?php echo $y; ?>"></label><input type="checkbox" id="year_<?php echo $y; ?>" value=".<?php echo $y; ?>" <?php if( $y == date('Y') ){?>checked="checked"<?php } ?>></li>
			<label>Mois</label>
			<ul class="filter" data-group="month_<?php echo $y; ?>">
				<?php for( $m = 1; $m <= 12; $m++ ){
						$m = lpad($m, 2, '0', STR_PAD_LEFT);
				?>
					<li><label for="year_<?php echo $y; ?>_month_<?php echo $m; ?>"></label><input type="checkbox" id="year_<?php echo $y; ?>_month_<?php echo $m; ?>" value=".<?php echo $m.'-'.$y; ?>" <?php if( $y == date('Y') && $m == date('m') ){?>checked="checked"<?php } ?>></li>
				<?php } ?>
			</ul>

		<? } ?>
	</ul>
</aside>

<aside id="filter">
	<label>Fréquence</label>
	<ul class="filter">
		<li><a href="#" class="selected" data-group="recurrent" data-filter="*">Tout</a></li>
		<li><a href="#" data-group="recurrent" data-filter=".recurrent">Récurrent</a></li>
		<li><a href="#" data-group="recurrent" data-filter=".punctual">Ponctuel</a></li>
	</ul>
	<label>Origine</label>
	<ul class="filter">
		<li><a href="#" class="selected" data-group="origin" data-filter="*">Tout</a></li>
		<?php foreach( $origins as $id => $name ){
			echo '<li><a href="#" data-group="origin" data-filter=".origin_'.$id.'">'.$name.'</a></li>';
		} ?>
	</ul>
	<label>Status</label>
	<ul class="filter">
		<li><a href="#" class="selected" data-group="status" data-filter="*">Tout</a></li>
		<?php foreach( $statuses as $id => $name ){
			echo '<li><a href="#" data-group="status" data-filter=".status_'.$id.'">'.$name.'</a></li>';
		} ?>
	</ul>
	<label for="recipient_filter">Bénéficiaire</label>
	<select name="recipient" id="recipient_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $recipients as $id => $name ){
			echo '<option value=".recipient_'.$id.'">'.$name.'</option>';
		} ?>
	</select>
	<label for="type_filter">Type</label>
	<select name="type">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $types as $id => $name ){
			echo '<option value=".type_'.$id.'">'.$name.'</option>';
		} ?>
	</select>
	<label for="currency_filter">Monnaie</label>
	<select name="currency" id="currency_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $currencies as $id => $name ){
			echo '<option value=".currency_'.$id.'">'.$name.'</option>';
		} ?>
	</select>
	<label for="method_filter">Méthode</label>
	<select name="method" id="method_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $methods as $id => $name ){
			echo '<option value=".method_'.$id.'">'.$name.'</option>';
		} ?>
	</select>
	<label for="origin_filter">Origine</label>
	<select name="origin" id="origin_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $origins as $id => $name ){
			echo '<option value=".origin_'.$id.'">'.$name.'</option>';
		} ?>
	</select>
	<label for="location_filter">Location</label>
	<select name="location" id="location_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $locations as $id => $name ){
			echo '<option value=".location_'.$id.'">'.$name.'</option>';
		} ?>
	</select>
</aside>

<aside id="sort">
	<ul>
		<li><a href="#date">date</a></li>
		<li><a href="#recipient">bénéficiaire</a></li>
		<li><a href="#method">méthode</a></li>
		<li><a href="#origin">origine</a></li>
		<li><a href="#status">statut</a></li>
		<li><a href="#amount">montant</a></li>
	</ul>
</aside>

<?php include('html_footer.php'); ?>
