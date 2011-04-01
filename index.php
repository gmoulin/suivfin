<?php
try {
	require_once('conf.ini.php');

	//metadata
	$metadata['description'] = 'application de gestion des paiements';
	$metadata['motscles'] = 'gestion, débit, crédit, transfert, compte, banque, monnaie, balance, paiement';
	$lang = 'fr';

    $css = filemtime( LMS_PATH.'/css/style.css' );

    $js = filemtime( LMS_PATH.'/js/script.js' );
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
?>
<?php include('html_header.php'); ?>

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
	</fieldset>
</form>

<?php include('html_footer.php'); ?>
