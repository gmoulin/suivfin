<?php if( !empty($payments) ){ ?>
	<?php if( !$partial ){ ?>
	<div id="container" class="clearfix">
	<?php } ?>
		<?php foreach( $payments as $payment ){ ?>
			<div id="payment_<?php echo $payment['id']; ?>"
				class="item
					<?php
						//classes are used for filtering
						$ts = strtotime($payment['paymentDate']);
						$month = date("F", $ts);
						$year = date("Y", $ts);
						$date = date("dmY", $ts);
						echo " ".$month.'-'.$year; //month
						echo " ".$year; //year
						echo " ".$date; //complete date
						echo " ".($payment['recurrent'] ? 'recurrent' : 'punctual'); //recurrent
						echo " recipient_".$payment['recipientFK']; //recipient
						echo " type_".$payment['typeFK']; //type
						echo " currency_".$payment['currencyFK']; //currency
						echo " method_".$payment['methodFK']; //method
						echo " origin_".$payment['originFK']; //origin
						echo " status_".$payment['statusFK']; //status
						echo " location_".$payment['locationFK']; //location
					?>
				" title="dernière modification le <?php echo date('d-m-y', strtotime($payment['modificationDate']));?>"

				<?php
					//data-* attributes are used for sorting
				?>
				data-date="<?php echo $ts; ?>"
				data-recipient="<?php echo $recipients[ $payment['recipientFK'] ]; ?>"
				data-method="<?php echo $methods[ $payment['methodFK'] ]; ?>"
				data-origin="<?php echo $origins[ $payment['originFK'] ]; ?>"
				data-status="<?php echo $statuses[ $payment['statusFK'] ]; ?>"
				data-amount="<?php echo $payment['amount']; ?>"
			>

				<a class="" data-icon="}" href="<?php echo $payment['id']; ?>" title="voir le détail"></a>

				<dl>
					<dd><?php echo $types[ $payment['typeFK'] ]; ?></dd>
					<dd><?php echo date("d-m-y", $ts); ?></dd>
					<dd>le <?php echo $payment['label']; ?></dd>
					<dd>pour <?php echo $payment['amount']; ?> <?php echo $currencies[ $payment['currencyFK'] ]; ?></dd>
					<dd>depuis <?php echo $origins[ $payment['originFK'] ]; ?> vers <?php echo $recipients[ $payment['recipientFK'] ]; ?></dd>
					<dd>en <?php echo $methods[ $payment['methodFK'] ]; ?></dd>
				</dl>
			</div>
		<?php } ?>
	<?php if( !$partial ){ ?>
	</div>
	<?php } ?>
<?php } ?>
