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

			$(':input:visible, #id', '#payment_form').each(function(field){
				if( $(field).is(':checkbox') ) $(field).removeAttr('checked');
				else $(field).val('');
			});
			$('#recurrent0').attr('checked', 'checked');
		});

	//help
		$('#helpToggle').click(function(e){
			e.preventDefault();

			$('#help').toggleClass('deploy');
		});

	$('datalist, select', '#payment_form').loadList();

	$('#container').isotope({
		// options
		itemSelector: '.item',
		layoutMode: 'fitRows',
		sortBy: 'date',
		sortAscending: 'false',
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
		}
	});

	$('#sort a').click(function(e){
		e.preventDefault();
		// get href attribute, minus the '#'
		var sortName = $(this).attr('href').substr(1);
		$('#container').isotope({ sortBy: sortName });
	});

	var $container = $('#container'),
		filters = {};

	// filter buttons
	$('#filter a').click(function(e){
		e.preventDefault();

		var $this = $(this),
			isoFilters = [],
			prop, selector;

		// store filter value in object
		// i.e. filters.color = 'red'
		filters[ $this.data('group') ] = $this.data('filter');

		for ( prop in filters ) {
			isoFilters.push( filters[ prop ] )
		}
		selector = isoFilters.join('');
		$container.isotope({ filter: selector });
	});

	// filter buttons
	$('#filter select').change(function(e){
		var $this = $(this),
			isoFilters = [],
			prop, selector;

		// store filter value in object
		// i.e. filters.color = 'red'
		filters[ $this.attr('name') ] = $this.val();

		for ( prop in filters ) {
			isoFilters.push( filters[ prop ] )
		}
		selector = isoFilters.join('');
		$container.isotope({ filter: selector });
	});

	// filter buttons
	$('#timeframe :checkbox').click(function(e){
		reloadPayments();
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


});

/**
 * ajax load <datalist> and <select> content
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

				var isOrigin = $list.is('#originList');
				var regexp = false;
				if( isOrigin && $('#owners .active').length ) regexp = new RegExp($('#owners .active').text()+'$', 'g');

				if( isDatalist ) $list.empty();
				else {
					var isFilter = false;
					if( $list.attr('id').search(/Filter/) != -1 && list.val() != '' ){
						$list.data('sav', list.val());
						isFilter = true;
					}
					$list.find('option:gt(0)').remove(); //keep the first option aka "placeholder"
				}

				$.each(data, function(i, obj){
					obj.name = decoder.html(obj.name).val();
					if( isDatalist ){
						var $o = $('<option>', { "value": obj.name, text: obj.name })
						if( isOrigin && regexp != false ){
							if( $o.val().search(regexp) != -1 ) $o.appendTo( $list );
						} else {
							$o.appendTo( $list );
						}
					} else {
						$('<option>', { "value": ( obj.id ? obj.id : obj.name ), text: obj.name }).appendTo( $list );
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

function reloadPayments(){
	$.post('ajax/loadPayments.php', 'timeframe=' + $('#time_frame').serialize(), function(data){
		//empty list then add new elements
		$container.isotope('remove', $('.item', $container)).isotope( 'appended', $(data));
	});
}















