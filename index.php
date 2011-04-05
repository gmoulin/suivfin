<?php
try {
	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'application de gestion des paiements';
	$metadata['motscles'] = 'gestion, débit, crédit, transfert, compte, banque, monnaie, balance, paiement';
	$lang = 'fr';

    $css = filemtime( SF_PATH.'/css/style.css' );

    $js = filemtime( SF_PATH.'/js/script.js' );

	if( !filter_has_var(INPUT_GET, 'owner') ){
		//@todo choose owner overlay
		$owner = 1; //default
	} else {
		$owner = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_NUMBER_INT);
		if( is_null($owner) || $owner === false ){
			throw new Exception('Gestion des comptes : compte incorrect.');
		}

	}

	init::getInstance()->setOwner( $owner );

	//get payments for current month
	$oPayment = new payment();
	$payments = $oPayment->loadForCurrentMonth();

	//get all related lists
	$oOrigin = new origin();
	$origins = $oOrigin->loadListForFilter();

	$oStatus = new status();
	$statuses = $oStatus->loadListForFilter();

	$oRecipient = new recipient();
	$recipients = $oRecipient->loadListForFilter();

	$oType = new type();
	$types = $oType->loadListForFilter();

	$oCurrency = new currency();
	$currencies = $oCurrency->loadListForFilter();

	$oMethod = new method();
	$methods = $oMethod->loadListForFilter();

	$oLocation = new location();
	$locations = $oLocation->loadListForFilter();

	$oOwner = new owner();
	$owners = $oOwner->loadListForFilter();

	$months = array(
		'01' => 'Janvier',
		'02' => 'Février',
		'03' => 'Mars',
		'04' => 'Avril',
		'05' => 'Mai',
		'06' => 'Juin',
		'07' => 'Juillet',
		'08' => 'Août',
		'09' => 'Septembre',
		'10' => 'Octobre',
		'11' => 'Novembre',
		'12' => 'Décembre',
	);


} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>
<?php include('html_header.php'); ?>

<nav id="owners">
	Comptes :
	<ul>
		<?php foreach( $owners as $id => $info ){
			echo '<li><a href="?owner='.$id.'" title="voir le suivi de '.$info['name'].'" class="'.( $id == $owner ? 'active' : '' ).'">'.$info['name'].'</a>';
		} ?>
	</ul>
</nav>

<form id="payment_form" action="" method="post">
	<input type="hidden" id="id" name="id" value="">
	<input type="hidden" id="ownerFK" name="ownerFK" value="<?php echo $ownerFK; ?>">
	<input type="hidden" id="action" name="action" value="add">
	<fieldset>
		<legend>Paiement</legend>
		<ul>
			<li>
				<label for="typeFK">Type</label>
			</li>
			<li>
				<select id="typeFK" name="typeFK" autocomplete="off" required>
					<option value=""></option>
				</select>
			</li>

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
				<input type="date" id="paiementDate" name="paiementDate" value="" autocomplete="off" required pattern="^([012][123456789]|[3][01])\/([0][123456789]|[1][12])\/20[0-9]{2}$" placeholder="<?php echo date('d/m/Y'); ?>">
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
				<input type="radio" id="recurrent0" name="recurrent" value="0" checked="checked"><label for="recurrent0">Non</label>
				<input type="radio" id="recurrent1" name="recurrent" value="1"><label for="recurrent1">Oui</label>
			</li>

			<li>
				<label for="recipientFK">Bénéficiaire</label>
			</li>
			<li>
				<input type="text" id="recipientFK" name="recipientFK" value="" autocomplete="off" required list="recipientList" placeholder="Migros">
				<datalist id="recipientList"></datalist>
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
				<input type="text" id="methodFK" name="methodFK" value="" autocomplete="off" required list="methodList" placeholder="carte">
				<datalist id="methodList"></datalist>
			</li>

			<li>
				<label for="originFK">Origine</label>
			</li>
			<li>
				<input type="text" id="originFK" name="originFK" value="" autocomplete="off" required list="originList" placeholder="liquide CHF">
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
	<ul>
		<li><a href="#masonry" class="selected" >masonry</a></li>
		<li><a href="#fitRows">fitRows</a></li>
		<li><a href="#cellsByRow">cellsByRow</a></li>
		<li><a href="#straightDown">straightDown</a></li>
		<li><a href="#masonryHorizontal" class="horizontal" >masonryHorizontal</a></li>
		<li><a href="#fitColumns" class="horizontal" >fitColumns</a></li>
		<li><a href="#cellsByColumn" class="horizontal">cellsByColumn</a></li>
	</ul>
</aside>

<aside id="time_frame">
	Années (et mois) à afficher :
	<h2>Année</h2>
	<ul class="filter" data-group="year">
		<?php for( $y = 2011; $y <= date('Y'); $y++ ){ ?>
			<li>
				<label for="year_<?php echo $y; ?>">
					<?php echo $y; ?>
				</label>
				<input type="checkbox" id="year_<?php echo $y; ?>" value=".<?php echo $y; ?>" <?php if( $y == date('Y') ){ ?>checked="checked"<?php } ?>>
				<div>
					<h3>Mois</h3>
					<ul class="filter" data-group="month_<?php echo $y; ?>">
						<?php for( $m = 1; $m <= 12; $m++ ){
							$m = str_pad($m, 2, '0', STR_PAD_LEFT);
						?>
							<li>
								<label for="year_<?php echo $y; ?>_month_<?php echo $m; ?>">
									<?php echo $months[$m]; ?>
								</label>
								<input type="checkbox" id="year_<?php echo $y; ?>_month_<?php echo $m; ?>" value=".<?php echo $m.'-'.$y; ?>" <?php if( $y == date('Y') && $m == date('m') ){ ?>checked="checked"<?php } ?>>
							</li>
						<?php } ?>
					</ul>
				</div>
			<detail>
			</li>
		<?php } ?>
	</ul>
</aside>

<aside id="filter">
	Filtres :
	<label>Fréquence</label>
	<ul class="filter">
		<li><a href="#" class="selected" data-group="recurrent" data-filter="*">Tout</a></li>
		<li><a href="#" data-group="recurrent" data-filter=".recurrent">Récurrent</a></li>
		<li><a href="#" data-group="recurrent" data-filter=".punctual">Ponctuel</a></li>
	</ul>
	<label>Origine</label>
	<ul class="filter">
		<li><a href="#" class="selected" data-group="origin" data-filter="*">Tout</a></li>
		<?php foreach( $origins as $id => $info ){
			echo '<li><a href="#" data-group="origin" data-filter=".origin_'.$id.'">'.$info['name'].'</a></li>';
		} ?>
	</ul>
	<label>Status</label>
	<ul class="filter">
		<li><a href="#" class="selected" data-group="status" data-filter="*">Tout</a></li>
		<?php foreach( $statuses as $id => $info ){
			echo '<li><a href="#" data-group="status" data-filter=".status_'.$id.'">'.$info['name'].'</a></li>';
		} ?>
	</ul>
	<label for="recipient_filter">Bénéficiaire</label>
	<select name="recipient" id="recipient_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $recipients as $id => $info ){
			echo '<option value=".recipient_'.$id.'">'.$info['name'].'</option>';
		} ?>
	</select>
	<label for="type_filter">Type</label>
	<select name="type">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $types as $id => $info ){
			echo '<option value=".type_'.$id.'">'.$info['name'].'</option>';
		} ?>
	</select>
	<label for="currency_filter">Monnaie</label>
	<select name="currency" id="currency_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $currencies as $id => $info ){
			echo '<option value=".currency_'.$id.'">'.$info['name'].'</option>';
		} ?>
	</select>
	<label for="method_filter">Méthode</label>
	<select name="method" id="method_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $methods as $id => $info ){
			echo '<option value=".method_'.$id.'">'.$info['name'].'</option>';
		} ?>
	</select>
	<label for="origin_filter">Origine</label>
	<select name="origin" id="origin_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $origins as $id => $info ){
			echo '<option value=".origin_'.$id.'">'.$info['name'].'</option>';
		} ?>
	</select>
	<label for="location_filter">Location</label>
	<select name="location" id="location_filter">
		<option value="*" selected="selected">Tout</option>
		<?php foreach( $locations as $id => $info ){
			echo '<option value=".location_'.$id.'">'.$info['name'].'</option>';
		} ?>
	</select>
</aside>

<aside id="sort">
	Tri :
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
