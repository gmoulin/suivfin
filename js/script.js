/* Author: Guillaume Moulin <gmoulin.dev@gmail.com>
*/
$(document).ready(function(){
	if( $.browser.mozilla ) $('html').addClass('mozilla');
	else if( $.browser.webkit ) $('html').addClass('webkit');

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
			if( xhr.responseText != '' ) inform("Error requesting page " + settings.url + ", error : " + xhr.responseText, 'error');
		});

	//forms actions
		$('#form_switch a').click(function(e){
			e.preventDefault();

			$('#payment_form').addClass('deploy');
			$('#action').val('add');
		});

		$('form').submit(function(e){
			e.preventDefault();

			if( $(this)[0].checkValidity() ){
				$.ajax({
					url: 'ajax/payment.php',
					data: $(':input', '#payment_form').serialize(),
					type: 'POST',
					dataType: 'json',
					complete: function(){
						$('#formSubmit').data('save_clicked', 0);
					},
					success: function(data){
						if( data == 'ok' ){
							//form hide

							//if item present, update it and the list
							if( $('#id').val() != '' && $('#payment_' + $('#id').val()).length ){
								//todo
								console.log('todo');
							} else {
								//refresh the whole list
								reloadPayments();
							}
						} else {
							//form errors display
							formErrors(data);
						}
					}
				});
			}
		});

		$('#formSubmit').click(function(e){
			console.log('formSubmit click');

			//multiple call protection
			if( $(this).data('save_clicked') != 1 ){
				$(this).data('save_clicked', 1);
			}
		});

		$('#formCancel').click(function(){
			console.log('formCancel click');

			$('#payment_form')
				.removeClass('deploy')
				.find(':input:visible, #id').each(function(field){
					if( $(field).is(':checkbox') ) $(field).removeAttr('checked');
					else $(field).val('');
				});
			$('#recurrent_0').click();
		});

		$('.origins_switch').change(function(e){
			if( limits[$(this).val()] ){
				$('#currency_' + limits[ $(this).val() ]).attr('checked', 'checked');
			}
		});

		$('html').unbind('keypress').keypress(function(e){
			// ESCAPE key pressed
			if( e.keyCode == 27 ){
				console.log('escape pressed');
				$('#payment_form.deploy').removeClass('deploy');
			}
		});

		$('datalist, select', '#payment_form').loadList();

	//isotope
		var $container = $('#container'),
			$sums = $('#sums'),
			filters = {};

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
		}).delegate('.eject', 'click', function(e){
			e.preventDefault();
			$('#payment_form').addClass('deploy');
			$('#action').val('update');
			$.post('ajax/payment.php', 'action=get&id=' + $(this).attr('href'), function(data){
				var decoder = $('textarea'),
					$field = null,
					$radio = null;
				$.each(data, function(key, value){
					$field = $('#' + key);
					$radio = $('#' + key.replace(/FK/, '') + '_' + value);
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
			if( confirm('Êtes-vous sûr de supprimer ce paiement ?') ){
				$.post('ajax/payment.php', 'action=delete&id=' + $(this).attr('href'), function(e){
					reloadPayments( $container, filters, $sums );
				});
			}
		});

		$('#sort a').click(function(e){
			e.preventDefault();
			// get href attribute, minus the '#'
			var sortName = $(this).attr('href').substr(1);
			$('#container').isotope({ sortBy: sortName });
		});

	// filter buttons
		$('#filter a').click(function(e){
			e.preventDefault();

			var $this = $(this),
				group = $this.data('group'),
				filter = $this.data('filter');

			//output the current value
			$this.closest('section').children('output').text( $(this).text() );

			// store filter value in object
			filters[ group ] = filter;

			applyFilters($container, filters);
		}).filter('.active').each(function(){
			//output the current value
			$(this).closest('section').children('output').text( $(this).text() );
		});

	// filter list
		$('#filter select').change(function(e){
			var $this = $(this),
				group = $this.attr('name'),
				filter = $this.val();

			// store filter value in object
			filters[ group ] = filter;

			applyFilters($container, filters);
		});

	// time frame chekboxes
		$('#time_frame :checkbox').change(function(e){
			//toggle the months checkboxes if the event target is a year checkbox
			if( $(this).hasClass('year') ){
				var isChecked = $(this).is(':checked');

				var $months = $(this).parent().find('ul.filter');

				$months.find(':checkbox').each(function(i, cb){
					cb.checked = isChecked;
				});
			}

			reloadPayments( $container, filters, $sums);
		});

	// change layout
		var isHorizontal = false;
		$('#layouts a').click(function(e){
			e.preventDefault();
			var mode = $(this).attr('href').substr(1);
				wasHorizontal = isHorizontal;
			isHorizontal = $(this).hasClass('horizontal');

			if ( wasHorizontal !== isHorizontal ) {
				// need to do some clean up for transitions and sizes
				var style = isHorizontal ?
					{ height: '80%', width: $container.width() } :
					{ width: 'auto' };
				// stop any animation on container height / width
				$container.filter(':animated').stop();

				$container.addClass('no-transition').css( style );
				setTimeout(function(){
					$container.removeClass('no-transition').isotope({ layoutMode : mode });
				}, 100 )
			} else {
				// go ahead and apply new layout
				$container.isotope({ layoutMode : mode });
			}
		});

	//sums cells hover
		$('#sums')
			.delegate('td:not(.type)', 'mouseenter', function(){
				var $this = $(this),
					index = $this.parent().children(':not(.type)').index($this);

				$this.siblings('.type').addClass('highlight');
				$this.closest('table').find('thead th.origin:eq(' + index + ')').addClass('highlight');
			})
			.delegate('td:not(.type)', 'mouseleave', function(){
				var $this = $(this),
					index = $this.parent().children(':not(.type)').index($this);

				$this.siblings('.type').removeClass('highlight');
				$this.closest('table').find('thead th.origin:eq(' + index + ')').removeClass('highlight');
			});

	//buttons active state and focus remove after click
		$('body').delegate('a.button', 'click', function(e){
			$(this).blur();
		}).delegate('.button-bar a', 'click', function(e){
			$(this).blur().addClass('active').closest('ul').find('.active').not( $(this) ).removeClass('active');
		});
});

/**
 * ajax load for <datalist> and <select> options
 */
$.fn.loadList = function(){
	console.log('loadList');
	return this.each(function(){
		console.log('loadList inner');
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
 * apply given filters to isotope
 * and sums
 */
function applyFilters( $container, filters ){
	var isoFilters = [],
		prop;

	for ( prop in filters ) {
		isoFilters.push( filters[ prop ] );
	}

	/* use of * as "all" selector can cause error with 2 or more consecutive * */
	$container.isotope({ filter: isoFilters.join('').replace(/\*/g, '') });

	var sortName = $('#sort a.active').attr('href').substr(1);
	$container.isotope({ sortBy: sortName });
}

/**
 * reload the payments according to the selected months
 */
function reloadPayments( $container, filters, $sums ){
	var tf = $('#time_frame :checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(',');
	if( tf == '' ) return;
	//do both ajax call to refresh sums and list then reapply filters
	$.post('ajax/payment.php', 'action=sum&timeframe=' + tf, function(data){
		$sums.empty().html( data );
	}),
	$.post('ajax/payment.php', 'action=list&timeframe=' + tf, function(data){
		//empty list then add new elements
		var $items = $(data);
		$container.isotope('remove', $container.children('.item')).isotope('reLayout').append( $items ).isotope( 'appended', $items);
		applyFilters( $container, filters );
	});
}

/**
 * display the form errors
 * use ".class + .validation-icon" css rules
 * use ".class ~ .tip" css rules
 * @param array [[field id, message, error type]]
 */
function formErrors( data ) {
	console.log('formErrors');
	$.each(data, function(index, error){
		$('#' + error[0]).addClass(error[2]).siblings('.tip').remove(); //remove previous error message if present

		$('#' + error[0]).parent().append( $('<span>', { 'class': 'tip', 'text': error[1] }) ); //add error message
	});
}













