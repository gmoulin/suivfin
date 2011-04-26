/* Author: Guillaume Moulin <gmoulin.dev@gmail.com>
*/
$(document).ready(function(){
	var $container = $('#container'),
		$sums = $('#sums'),
		$form = $('#payment_form'),
		$filter = $('#filter'),
		$timeframe = $('#time_frame'),
		filters = {},
		buffer = null;

	//ajax global management
		$('#ajax_loader').ajaxStart(function(){
			console.log('ajaxStart');
			$('#ajax_loader').addClass('loading');
			$(this).empty(); //global error message deletion
		})
		.ajaxStop(function(){
			console.log('ajaxStop');
			$('#ajax_loader').removeClass('loading');
		})
		.ajaxError(function(event, xhr, settings, exception){
			console.log('ajaxError');
			if( xhr.responseText != '' ) alert("Error requesting page " + settings.url + ", error : " + xhr.responseText, 'error');
		});

	//forms actions
		$('#form_switch a').click(function(e){
			e.preventDefault();

			$form.resetForm()
				 .addClass('deploy');
		});

		$form.submit(function(e){
			e.preventDefault();

			if( $(this)[0].checkValidity() && $form.data('submitting') != 1 ){
				$form.data('submitting', 1); //multiple call protection

				//is payment month present in time frame
				var paymentDate = $('#paymentDate').val().split('/'),
					tmp = new Date(paymentDate[2], paymentDate[1], paymentDate[0]),
					tmp = (tmp.getDay() >= 24 ? new Date(tmp.getFullYear(), tmp.getMonth() + 1, 1) : tmp),
					newMonth = tmp.getFullYear() + '-' + tmp.getMonth();

				var needReload = false;
				if( $('#time_frame').find('input[value=' + newMonth + ']').length ){
					//make sure the checkbox is checked and trigger the change event to check the corresponding year checkbox if needed
					$('#time_frame').find('input[value=' + newMonth + ']').attr('checked', 'checked').change();
				} else {
					needReload = true;
				}

				var tf = $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(',');

				$.ajax({
					url: 'ajax/payment.php',
					data: $(':input', '#payment_form').serialize() + ( !needReload ? '&timeframe=' + tf : '' ),
					type: 'POST',
					dataType: 'json',
					complete: function(){
						$form.data('submitting', 0);
					},
					success: function(data){
						$form.data('submitting', 0); //security, sometimes complete() is not called...
						if( data == 'ok' ){
							if( needReload ) window.location.reload();
							else reloadParts();

						} else if( data.payments ){
							//form hide
							$form.removeClass('deploy')
								 .find('datalist, select').loadList();
							$filter.find('select').loadList();

							//focus the payment add button
							$('#form_switch a').focus();

							refreshParts( data );

						} else {
							//form errors display
							formErrors(data);
						}
					}
				});
			}
		}).delegate('input[name=typeFK]', 'change', function(e){
			if( this.id == 'type_3' ){
				$form.find('.ownerChoice').fadeIn();
			} else {
				$form.find('.ownerChoice:visible').fadeOut();
			}
		});

		$('#formCancel').click(function(){
			console.log('formCancel click');

			$form.removeClass('deploy').resetForm();
		});

		$(document).unbind('keypress').keypress(function(e){
			// ESCAPE key pressed
			if( e.keyCode == 27 ){
				$('#payment_form.deploy').removeClass('deploy');
			}
		}).unbind('keydown').keydown(function(e){
			//"a" pressed for add
			if( e.which == 65 ){
				$('#payment_form:not(.deploy)')
					.resetForm()
					.addClass('deploy');
			}
		}).delegate('#amount', 'keydown', function(e){
			if( e.which == 188 ){ //, pressed (comma)
				e.preventDefault();
				$('#amount').val(function(){ return this.value + '.'; });
			}
		});

		$form.find('datalist, select').loadList();

		$('#originFK').change(function(e){
			if( this.value != '' && limits[ this.value ] ){
				$('#currency_' + limits[ this.value ]).attr('checked', 'checked');
				$('#amount').focus();
			}
		});

	//isotope
		$('#container').isotope({
			// options
			itemSelector: '.item',
			layoutMode: 'fitRows',
			sortBy: 'date',
			sortAscending: false,
			getSortData: {
				date: function( $elem ){
					return $elem.data('date');
				},
				recipient: function( $elem ){
					return $elem.data('recipient');
				},
				method: function( $elem ){
					return $elem.data('method');
				},
				origin: function( $elem ){
					return $elem.data('origin');
				},
				status: function( $elem ){
					return $elem.data('status');
				},
				amount: function( $elem ){
					return parseFloat( $elem.data('amount') );
				}
			},
			cellsByRow: {
				columnWidth: 240,
				rowHeight: 200
			},
			cellsByColumn: {
				columnWidth: 240,
				rowHeight: 200
			},
			masonry: {
				columnWidth: 240
			},
			masonryHorizontal: {
				rowHeight: 200
			}
		}).delegate('.edit', 'click', function(e){
			e.preventDefault();
			$form.resetForm()
				 .addClass('deploy');
			$('#action').val('update');

			$.post('ajax/payment.php', 'action=get&id=' + $(this).attr('href'), function(data){
				var decoder = $('textarea'),
					$field = null,
					$radio = null;
				$.each(data, function(key, value){
					$field = $('#' + key);
					if( !isNaN(parseInt(value)) ){
						$radio = $('#' + key.replace(/FK/, '') + '_' + value);
					} else {
						$radio = null;
					}
					if( $field.length ){
						if( $field.is('input[type=text][list]') ){ //datalist
							$field.val( $( $field.attr('list') ).children('[data-id=' + value + ']').text() );

						} else if( $field.is('textarea') ){
							$field.val( decoder.html( value ).text() );

						} else if( $field.is('input[type=date]') ){
							var d = value.split('-');
							$field.val( d[2] + '/' + d[1] + '/' + d[0] );

						} else {
							$field.val( decoder.html( value ).text() );
						}
					} else if( $radio.length ){
						$radio.attr('checked', 'checked');
					}
				});


			});
		}).delegate('.delete', 'click', function(e){
			e.preventDefault();
			if( confirm('Êtes-vous sûr de supprimer ce paiement ?') ){
				var tf = $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(',');

				$.post('ajax/payment.php', 'action=delete&id=' + $(this).attr('href') + '&timeframe=' + tf, function(data){
					if( data.payments ){
						$form.find('datalist, select').loadList();
						$filter.find('select').loadList();

						refreshParts( data );
					} else {
						alert( data );
					}
				});
			}
		});

		$('#sort a').click(function(e){
			e.preventDefault();
			// get href attribute, minus the '#'
			var sortName = $(this).attr('href').substr(1);
			$('#container').isotope({ sortBy: sortName });
		});

	//filter buttons
		$filter.delegate('a', 'click', function(e){
			e.preventDefault();

			var $this = $(this),
				group = $this.data('group'),
				filter = $this.data('filter');

			//output the current value
			$this.closest('section').children('output').text( $(this).text() );

			// store filter value in object
			filters[ group ] = filter;

			applyFilters($container, filters);
		}).find('.primary').each(function(){
			//output the current value
			$(this).closest('section').children('output').text( $(this).text() );
		});

	//filter list
		$filter.delegate('select', 'change', function(e){
			var $this = $(this),
				group = $this.attr('name'),
				filter = $this.val();

			// store filter value in object
			filters[ group ] = filter;

			applyFilters($container, filters);
		});

	//next month recurrent payments generation
		$('#next_month a').click(function(e){
			e.preventDefault();
			if( confirm("Êtes-vous sûr ?\nAucune vérification ne sera réalisée !") ){

				//is next month present in time frame
				var now = new Date(),
					next = new Date(now.getFullYear(), now.getMonth()+1, 1),
					newMonth = next.getFullYear() + '-' + next.getMonth();

				var needReload = false;
				if( $('#time_frame').find('input[value=' + newMonth + ']').length ){
					//make sure the checkbox is checked and trigger the change event to check the corresponding year checkbox if needed
					$('#time_frame').find('input[value=' + newMonth + ']').attr('checked', 'checked').change();
				} else {
					needReload = true;
				}

				var tf = $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(',');

				$.post('ajax/payment.php', 'action=initNextMonth' + ( !needReload ? '&timeframe=' + tf : '' ), function(data){
					if( data == 'ok' ){
						if( needReload ) window.location.reload();

					} else if( data.payments ){
						refreshParts( data );

					} else {
						alert( data );
					}
				});
			}
		});

	//time frame chekboxes
		$('#time_frame :checkbox').change(function(e){
			//toggle the months checkboxes if the event target is a year checkbox
			if( $(this).hasClass('year') ){
				var isChecked = $(this).is(':checked');

				var $months = $(this).parent().find('ul.filter');

				$months.find(':checkbox').each(function(i, cb){
					cb.checked = isChecked;
				});

			//check the year checkbox if needed
			} else {
				$(this).closest('ul').parent().find('.year').attr('checked', 'checked');
			}

			//wait 500ms before reloading data, help when user check several checkboxes quickly
			clearTimeout(buffer);
			buffer = setTimeout(function(){ reloadParts(); }, 500);
		});

	//sums cells hover
		$('#sums')
			.delegate('tbody td:not(.type)', 'mouseenter', function(){
				var $this = $(this),
					index = $this.parent().children(':not(.type)').index($this);

				$this.siblings('.type').addClass('highlight');
				$this.closest('table').find('thead th.fromto:eq(' + index + ')').addClass('highlight');
			})
			.delegate('tbody td:not(.type)', 'mouseleave', function(){
				var $this = $(this),
					index = $this.parent().children(':not(.type)').index($this);

				$this.siblings('.type').removeClass('highlight');
				$this.closest('table').find('thead th.fromto:eq(' + index + ')').removeClass('highlight');
			})
			.delegate('tfoot td', 'mouseenter', function(){
				var $this = $(this),
					index = $this.parent().children('td').index($this);

				$this.siblings('th').addClass('highlight');
				$this.closest('table').find('thead th.fromto:eq(' + index + ')').addClass('highlight');
			})
			.delegate('tfoot td', 'mouseleave', function(){
				var $this = $(this),
					index = $this.parent().children('td').index($this);

				$this.siblings('th').removeClass('highlight');
				$this.closest('table').find('thead th.fromto:eq(' + index + ')').removeClass('highlight');
			});

	//remove <a.button> active state after click
		$('#filter, #sort, #next_month, #owners').delegate('.button', 'click', function(){
			$(this).blur().addClass('primary').parent().find('.primary').not( $(this) ).removeClass('primary');
		});


	/**
	 * refresh the payments, forecast and sum parts with ajax return
	 * @params json data: array containing payments, forecasts and sums html code
	 */
	function resfreshParts( data ){
		$sums.empty().html( data.sums );

		var $forecast = $('#forecasts'),
			title = $forecast.children('h2').detach();
		$forecast.empty().html( data.forecasts ).prepend( title );

		//prepare jQuery Template
		if( !$('#paymentListTemplate').data('tmpl') ){
			$('#paymentListTemplate').template('paymentList');
		}

		//empty list then add new elements
		var $items = $.tmpl('paymentList', data);
		$container.isotope('remove', $container.children('.item')).isotope('reLayout').append( $items ).isotope( 'appended', $items);
		applyFilters();
	}


	/**
	 * reload the payments, sums and forecasts according to the selected months
	 */
	function reloadParts(){
		var tf = $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(',');
		if( tf == '' ) return;

		//resfresh sums
		$.post('ajax/payment.php', 'action=refresh&timeframe=' + tf, function(data){
			if( data.payments ){
				resfreshParts( data );
			} else {
				alert( data );
			}
		});
	}


	/**
	 * apply given filters to isotope
	 * and sums
	 */
	function applyFilters(){
		var isoFilters = [],
			prop;

		for ( prop in filters ) {
			isoFilters.push( filters[ prop ] );
		}

		/* use of * as "all" selector can cause error with 2 or more consecutive * */
		$container.isotope({ filter: isoFilters.join('').replace(/\*/g, '') });

		var sortName = $('#sort a.primary').attr('href').substr(1);
		$container.isotope({ sortBy: sortName });
	}
});

/**
 * ajax load for <datalist> and <select> options
 */
$.fn.loadList = function(){
	return this.each(function(){
		var $list = $(this);

		//ask the list values to the server and create the <option>s with it
		var decoder = $('<textarea>');
		$.get( 'ajax/loadList.php?field=' + $list.attr('id').replace(/FK$/g, 'List'), function(data, textStatus, jqXHR){
			//server will send a 304 status if the list has not changed
			if( jqXHR.status == 200 ){
				var isDatalist = $list.is('datalist');

				if( isDatalist ) $list.empty();
				else {
					var isFilter = false;
					if( $list.attr('id').search(/Filter/) != -1 && list.val() != '' ){
						$list.data('sav', list.val());
						isFilter = true;
					}
					$list.find('option:gt(0)').remove(); //keep the first option aka "placeholder"
				}

				$.each(data, function(id, name){
					name = decoder.html(name).val();
					if( isDatalist ){
						$('<option>', { "value": name, text: name, 'data-id': id }).appendTo( $list )
					} else {
						$('<option>', { "value": id, text: name }).appendTo( $list );
					}
				});

				if( isFilter ) $list.val( $list.data('sav') );

				if( $list.data('selectedId') ){
					$list.val( list.data('selectedId') );
					$list.removeData('selectedId');
				}
			}
		});
	});
}

/**
 * reset the payment form fields
 */
$.fn.resetForm = function(){
	console.log('resetForm');
	return this.each(function(){
		var $f = $(this);

		$f.removeClass('deploy')
			.find(':input:visible, #id').each(function(i, field){
				if( field.type == 'radio' ) field.checked = false;
				else field.value = '';
			});
		$f.find('.ownerChoice').hide();
		$('#recurrent_0').attr('checked', 'checked');
		$('#type_2').attr('checked', 'checked');
		$('#action').val('add');
	});
}


/**
 * display the form errors
 * use ".class + .validation-icon" css rules
 * use ".class ~ .tip" css rules
 * @param array [[field id, message, error type]]
 */
function formErrors( data ){
	console.log('formErrors');
	$.each(data, function(index, error){
		$('#' + error[0]).addClass(error[2]).siblings('.tip').remove(); //remove previous error message if present

		$('#' + error[0]).parent().append( $('<span>', { 'class': 'tip', 'text': error[1] }) ); //add error message
	});
}

//functions for payment list jquery template
String.prototype.formatDate = function(){
	var tmp = this.substr(0, 10).split('-');
	return tmp[2] + '-' + tmp[1] + '-' + tmp[0];
}

String.prototype.timestamp = function(){
	var d = this.substr(0, 10).split('-'),
		tmp = new Date(d[0], d[1], d[2]);
	return tmp.getTime();
}

String.prototype.capitalize = function(){
	return this.charAt(0).toUpperCase() + this.substr(1);
}

String.prototype.format = function(sepa, thousandSepa){
	var num = this,
		res = "",
		cent = "",
		sepa = '.',
		thousand = '\'';

	if( arguments[0] != undefined ) sepa = arguments[0];
	if( arguments[1] != undefined ) thousand = arguments[1];

	if( num.indexOf('.') != -1 ){
		cent = num.slice( num.lastIndexOf('.') + 1, num.length );
		num = num.slice( 0, num.lastIndexOf('.') );
	}

	if( num.length > 3 ){
		while( num.length > 3 ){
			res = num.slice( num.length - 3, num.length ) + (res.length ? thousand + res : res);
			num = num.slice( 0, num.length - 3 );
		}
	}
	if( num.length ) res = num + (res.length ? thousand + res : res);

	if( cent.length ) res += sepa + cent;

	return res;
}

function getValue( data, index ){
	return data[index];
}

function getSymbol( data, index ){
	return data[index].symbol;
}
