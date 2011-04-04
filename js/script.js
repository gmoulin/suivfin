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
		$('.form').each(function(){
			//add event listener for dynamic form validation
			this.addEventListener("invalid", checkField, true);
			this.addEventListener("input", checkField, true);

			$(this).submit(function(e){
				e.preventDefault();

				//ajax
				$('#ownerFK option:gt(0)').each(function(i, op){
					$(op).attr('selected', function(){ return $(this).text() == window.location.hash.substr(1); });
				});
			});
		});

	//help
		$('.help').click(function(e){
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
		var list = $(this);

		if( list.children().length <= 1 ) forceUpdate = 1;

		//ask the list values to the server and create the <option>s with it
		var decoder = $('<textarea>');
		$.get( 'ajax/loadList.php?field=' + list.attr('id'), function(data, textStatus, jqXHR){
			//server will send a 304 status if the list has not changed
			if( jqXHR.status == 200 ){
				if( list.is('datalist') ) list.empty();
				else {
					var isFilter = false;
					if( list.attr('id').search(/Filter/) != -1 && list.val() != '' ){
						list.data('sav', list.val());
						isFilter = true;
					}
					list.find('option:gt(0)').remove(); //keep the first option aka "placeholder"
				}

				$.each(data, function(i, obj){
					obj.value = decoder.html(obj.value).val();
					$('<option>', { "value": ( obj.id ? obj.id : obj.value ), text: obj.value }).appendTo( list );
				});

				if( isFilter ) list.val(list.data('sav'));

				if( list.data('selectedId') ){
					list.val( list.data('selectedId') );
					list.removeData('selectedId');
				}
			}
		});
	});
}


/**
 * dynamic form fields validation using HTML5 form validation API
 * called through javascript events listeners
 * set classes for css form validation rules
 * ".class + .validation-icon"
 * @param object event
 */
function checkField(event){
	console.log('checkField');
	var el = $(event.target);

	if( el[0].validity ){
		if( el[0].validity.valid ){
			if( el.val() != '' ) el.removeClass('required error upload').addClass('valid');
		} else if( event.type != "input" ){
			if( el[0].validity.valueMissing ){ // User hasn't typed anything
				el.removeClass('error valid upload').addClass('required');
			} else {
				el.removeClass('required valid upload').addClass('error');
			}
		} else if( el[0].validity.valueMissing ){
			el.removeClass('required valid error upload');
		}
	//for browsers with no forum validation API support
	} else {
		if( el.val() != '' ) el.removeClass('required error upload').addClass('valid');
		else el.removeClass('error valid upload').addClass('required');
	}
}


function reloadPayments(){
	$.post('ajax/loadPayments.php', 'timeframe=' + $('#timeframe').find(':checkbox:checked').serialize(), function(data){
		//empty list then add new elements
		$container.isotope('remove', $('.item', $container)).isotope( 'appended', $(data));
	});
}















