/**
 * Author: Guillaume Moulin <gmoulin.dev@gmail.com>
 */
var delayAjax = false,
	delayTimeout;

//cache the site via manifest if possible
if( Modernizr.applicationcache ){
	var debugCacheManifest = false;

	if( debugCacheManifest ){
		//force reload of the page if an update is available and log all the process
		var cacheStatusValues = [];
		cacheStatusValues[0] = 'uncached';
		cacheStatusValues[1] = 'idle';
		cacheStatusValues[2] = 'checking';
		cacheStatusValues[3] = 'downloading';
		cacheStatusValues[4] = 'updateready';
		cacheStatusValues[5] = 'obsolete';

		function logEvent(e) {
			var online, status, type, message;
			online = (navigator.onLine) ? 'yes' : 'no';
			status = cacheStatusValues[cache.status];
			type = e.type;
			message = 'online: ' + online;
			message+= ', event: ' + type;
			message+= ', status: ' + status;
			if (type == 'error' && navigator.onLine) {
				message+= ' (prolly a syntax error in manifest)';
			}
			console.log(message);
		}

		var cache = window.applicationCache;
		cache.addEventListener('cached', logEvent, false);
		cache.addEventListener('checking', logEvent, false);
		cache.addEventListener('downloading', logEvent, false);
		cache.addEventListener('error', logEvent, false);
		cache.addEventListener('noupdate', logEvent, false);
		cache.addEventListener('obsolete', logEvent, false);
		cache.addEventListener('progress', logEvent, false);
		cache.addEventListener('updateready', logEvent, false);

		setInterval(function(){cache.update()}, 10000);
	}

	//just force reload of the page if an update is available
	window.applicationCache.addEventListener(
		'updateready',
		function(){
			//busy visual information
			$('header').removeClass('loading');
			if( confirm('Une nouvelle version est disponible, voulez-vous recharger la page ?') ){
				window.applicationCache.swapCache();
				window.location.reload();
			} else {
				delayAjax = false;
			}
		},
		false
	);

	window.applicationCache.addEventListener(
		'checking',
		function(){
			//delay ajax calls if there is a new manifest version
			delayAjax = true;
		},
		false
	);
	window.applicationCache.addEventListener(
		'downloading',
		function(){
			//busy visual information
			$('header').addClass('loading');
		},
		false
	);
	window.applicationCache.addEventListener(
		'noupdate',
		function(){
			delayAjax = false;
			$('header').removeClass('loading');
		},
		false
	);
	window.applicationCache.addEventListener(
		'error',
		function(){
			//delay ajax calls if there is a new manifest version
			delayAjax = false;
			$('header').removeClass('loading');
			alert('Error while downloading the new version');
		},
		false
	);

	//sometimes a DOM exception is raised by update()...
	try {
		if( !$.browser.opera ) window.applicationCache.update();
	} catch(err){
		if( $.browser.opera ) window.location.reload();
	}
}

//opera mini does not support localStorage...
if( !Modernizr.localstorage ){
	(function(){
		var Storage = function(type){
			function createCookie(name, value, days){
				var date, expires;

				if( days ){
					date = new Date();
					date.setTime(date.getTime()+(days*24*60*60*1000));
					expires = "; expires="+date.toGMTString();
				} else {
					expires = "";
				}
				document.cookie = name+"="+value+expires+"; path=/";
			}

			function readCookie(name){
				var nameEQ = name + "=",
					ca = document.cookie.split(';'),
					i, c;

				for( i=0; i < ca.length; i++ ){
					c = ca[i];
					while( c.charAt(0)==' ' ){
						c = c.substring(1,c.length);
					}

					if( c.indexOf(nameEQ) == 0 ){
						return c.substring(nameEQ.length,c.length);
					}
				}
				return null;
			}

			function setData(data){
				data = JSON.stringify(data);
				if( type == 'session' ){
					window.name = data;
				} else {
					createCookie('localStorage', data, 365);
				}
			}

			function clearData(){
				if( type == 'session' ){
					window.name = '';
				} else {
					createCookie('localStorage', '', 365);
				}
			}

			function getData(){
				var data = type == 'session' ? window.name : readCookie('localStorage');
				return data ? JSON.parse(data) : {};
			}

			// initialise if there's already data
			var data = getData();

			return {
				length: 0,
				clear: function(){
					data = {};
					this.length = 0;
					clearData();
				},
				getItem: function(key){
					return data[key] === undefined ? null : data[key];
				},
				getObject: function(key){
					return data[key] === undefined ? null : JSON.parse( data[key] );
				},
				key: function(i){
					// not perfect, but works
					var ctr = 0;
					for (var k in data) {
						if (ctr == i) return k;
						else ctr++;
					}
					return null;
				},
				removeItem: function(key){
					delete data[key];
					this.length--;
					setData(data);
				},
				setItem: function(key, value){
					data[key] = value+''; // forces the value to a string
					this.length++;
					setData(data);
				},
				setObject: function(key, value){
					data[key] = JSON.stringify(value)+''; // forces the value to a string
					this.length++;
					setData(data);
				}
			};
		};

		if( typeof window.localStorage == 'undefined' ) window.localStorage = new Storage('local');
		if( typeof window.sessionStorage == 'undefined' ) window.sessionStorage = new Storage('session');

	})();
}

//datalist false positive support (no UI)
var isFennec = navigator.userAgent.indexOf('Fennec') != -1;

$(document).ready(function(){

	var $body = $('body'),
		$container = $('#container'),
		$sums = $('#sums'),
		$form = $('#payment_form'),
		$filter = $('#filter'),
		$timeframe = $('#time_frame'),
		$currentOwner = $('#current_owner'),
		filters = {},
		buffer = null,
		chart = null,
		legendCurrency = [];

	/* online - offline modes */
		/* @todo to finish and test */
		window.addEventListener("online", function(){
			$body.removeClass("offline")
				 .data('internet', 'online');

			var deletions = localStorage.getObject('deletions') || [] ,
				modifications = localStorage.getObject('modifications') || [];

			if( deletions.length ){
				var todo = deletions;
				$.each( deletions, function(i, deletion){
					$.post('ajax/payment.php', deletion + '&offline=1', function(data){
						if( data != 'ok' ){
							alert(data);
						} else {
							todo.shift();
						}
					});
				});

				if( todo.length ){
					alert('not all deletions actions have been sent');
					localStorage.setObject('deletions', todo);
				} else {
					localStorage.removeItem('deletions');
				}
			}

			if( modifications.length ){
				var todo = modifications;
				$.each( modifications, function(i, modification){
					$.post('ajax/payment.php', modification + '&offline=1', function(data){
						if( data != 'ok' ){
							alert(data);
						} else {
							todo.shift();
						}
					});
				});

				if( todo.length ){
					alert('not all deletions actions have been sent');
					localStorage.setObject('modifications', todo);
				} else {
					localStorage.removeItem('modifications');
				}
			}

			//reload the parts, will throw a 304 and use localStorage if nothing has changed server-side
			reloadParts();
		}, true);

		window.addEventListener("offline", function(){
			$body.addClass("offline")
				 .data('internet', 'offline');
		}, true);

		if( navigator.onLine ){
			$body.removeClass("offline")
				 .data('internet', 'online');
		} else {
			$body.addClass("offline")
				 .data('internet', 'offline');
		}

	//ajax global management
		$('header').ajaxStart(function(){
			$(this).addClass('loading');
		}).ajaxStop(function(){
			$(this).removeClass('loading');
		}).ajaxError(function(event, xhr, settings, exception){
			$(this).removeClass('loading');
			if( xhr.responseText != '' ) alert("Error requesting page " + settings.url + ", error : " + xhr.responseText, 'error');
		});

		$.ajaxSetup({cache: false});

	//forms actions
		//add button
		$('.form_switch a').click(function(e){
			e.preventDefault();
			e.stopPropagation(); //else it will fire $(document).click() listener

			$form.resetForm()
				 .addClass('deploy');

			//hide the form when clicking outside
			$('body').click(function(e){
				$form.removeClass('deploy').removeClass('submitting');
			});
		});

		$form.submit(function(e){
			e.preventDefault();
			if( $(this)[0].checkValidity() && !$form.hasClass('submitting') ){
				$form.addClass('submitting'); //multiple call protection and visual loading display

				//is payment month present in time frame
				var paymentDate = $('#paymentDate').val().split('/'),
					tmp = new Date(paymentDate[2], parseInt(paymentDate[1], 10) - 1, paymentDate[0]),
					tmp = (tmp.getDate() > 24 ? new Date(tmp.getFullYear(), tmp.getMonth() + 1, 1) : tmp),
					m = tmp.getMonth() + 1, //javascript month index start at 0
					newMonth = tmp.getFullYear() + '-' + ( (''+m).length == 1 ? '0' + m : m );

				var needReload = false,
					$cb = $('#time_frame').find('input[value=' + newMonth + ']');
				if( $cb.length ){
					//make sure the checkbox is checked and trigger the change event to check the corresponding year checkbox if needed
					if( !$cb.is(':checked') ) $('#time_frame').find('input[value=' + newMonth + ']').prop({ checked: true }).change();
				} else {
					needReload = true;
				}

				var tf = $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(','),
					params = $(':input', '#payment_form').serialize() + ( !needReload ? '&timeframe=' + tf : '' );

				if( !Modernizr.input.list || isFennec ){
					params = $('input:not([list]), input[type=checkbox], input[type=radio], textarea', '#payment_form').serialize();

					$('input[list]', '#payment_form').each(function(){
						var fallback = $('select[name="'+ this.name +'"]', '#payment_form'),
							param = '&' + this.name + '=';
						params += param + ( fallback.val() != '' ? fallback.val() : $(this).val() );
					});

					params += ( !needReload ? '&timeframe=' + tf : '' );
				}

				if( $body.data('internet') == 'offline' ){
					var modifications = localStorage.getObject('modifications') || [];
					modifications.push(params + '&owner=' + $currentOwner.val() );
					localStorage.setObject('modifications', modifications);

					//form hide
					$('body').unbind('click');
					$form.removeClass('submitting').removeClass('deploy');

					alert('Cette modification sera prise en compte une fois que vous repasserez en ligne.');

				} else {
					$('header').addClass('loading');
					$.ajax({
						url: 'ajax/payment.php',
						data: params,
						cache: false,
						type: 'POST',
						dataType: 'json',
						complete: function(){
							$form.removeClass('submitting');
						},
						success: function(data, textStatus, jqXHR){
							$form.removeClass('submitting'); //security, sometimes complete() is not called...
							if( data == 'ok' ){
								if( needReload ) window.location.reload();
								else reloadParts();

							} else if( data.payments ){
								$('header').removeClass('loading');
								//form hide
								$('body').unbind('click');
								$form.removeClass('deploy');

								try {
									lastModified = jqXHR.getResponseHeader('Last-Modified');

									var key = $currentOwner.val() + '_' + $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(',');

									localStorage.setObject(key, {'lastModified': lastModified, 'data': data});
								} catch( e ){
									alert(e);
								}

								refreshParts( data );

								$form.find('datalist, select[id]').loadList();
								$filter.find('select').loadList();

								//focus the payment add button
								$('.form_switch:visible a').focus();
							} else {
								//form errors display
								formErrors(data);
							}
						}
					});
				}
			}
		}).delegate('input[name=typeFK]', 'change', function(e){
			if( this.id == 'type_3' ){
				$form.find('.ownerChoice').fadeIn();
			} else {
				$form.find('.ownerChoice:visible').fadeOut();
			}
		}).find('fieldset').click(function(e){
			e.stopPropagation();
		});

		$('#formCancel').click(function(){
			$('body').unbind('click');
			$form.removeClass('deploy').removeClass('submitting').resetForm();
		});

		$(document).unbind('keydown').keydown(function(e){
			//"a" pressed for add
			if( e.which == 65 ){
				$('#payment_form:not(.deploy)')
					.resetForm()
					.addClass('deploy');

			} else if( e.keyCode == 27 ){
				if( $form.hasClass('deploy') ){
					$('body').unbind('click');
					$form.removeClass('deploy').removeClass('submitting');
				}
			}
		}).delegate('#amount', 'keydown', function(e){
			if( e.which == 188 ){ //, pressed (comma)
				e.preventDefault();
				$('#amount').val(function(){ return this.value + '.'; });
			}
		});

		$('#originFK').change(function(e){
			if( this.value != '' && limits[ this.value ] ){
				$('#currency_' + limits[ this.value ]).prop({checked: true});
				$('#amount').focus();
			}
			//selecting "Liquide XXX" set method to "liquide"
			if( this.value.search(/Liquide/) != -1 ){
				$('#methodFK').val('liquide');
			}
		});

		//@todo temporary until datetime is fully supported
		//@todo remove mousewheel polyfill (at the end)
		$('#paymentDate').mousewheel(function(e, delta){
			e.preventDefault();
			e.stopPropagation();

			if( this.value != '' ){
				var paymentDate = this.value.split('/'),
					tmp = new Date(paymentDate[2], parseInt(paymentDate[1], 10) - 1, paymentDate[0]);

				if( delta == -1 ){ //scroll down for next date
					tmp.setDate(tmp.getDate() + 1);
				} else { //scroll up for previous date
					tmp.setDate(tmp.getDate() - 1);
				}

				var m = tmp.getMonth() + 1,
					d = tmp.getDate();
				this.value = ( (''+d).length == 1 ? '0' + d : d ) + '/' + ( (''+m).length == 1 ? '0' + m : m ) + '/' + tmp.getFullYear();
			}
		});

		//@todo temporary, to avoid error on form submit for non datalist supporting browser and Fennec false support
		if( !Modernizr.input.list || isFennec ){
			$form.find('input[list]').each(function(){
				$(this).removeProp('required');
			});
		}

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
			}
		}).delegate('.edit, .fork', 'click', function(e){
			e.preventDefault();
			$form.resetForm()
				 .addClass('deploy');

			var $this = $(e.target);

			//edit is for update
			//fork is for dupplication, so action is "add" an id has no value
			if( $this.hasClass('edit') ) $('#action').val('update');

			//in online mode take the values directly from database
			//in offline mode, get the values from DOM
			if( $body.data('internet') == 'offline' ){
				$('#id').val( $this.attr('href') );

				var $item = $this.closest('.item'),
					classes = $item.attr('class');
				classes.split(' ');
				$.each(classes, function(i, c){
					if( c == 'recurrent' || c == 'punctual' ){
						$('#recurrent_' + ( c == 'recurrent' ? 1 : 0 )).prop({checked: true});

					} else if( c.indexOf('type_') != -1 || c.indexOf('currency_') != -1 || c.indexOf('location_') != -1 ){
						$('#' + c).prop({checked: true});
					}
				});

				$('#label').val( $item.find('dd:eq(1)').text() );

				var d = new Date( $item.data('date') );
				$('#paymentDate').val( ( d.getDate() < 10 ? '0' + d.getDate() : d.getDate() ) + '/' + ( ( d.getMonth() + 1 ) < 10 ? '0' : '' ) + ( d.getMonth() + 1 ) + '/' + d.getFullYear() );

				//comment
				$('#comment').val( $item.data('comment') );

				//originFK
				$('#originFK').val( $('#originList').find('[data-id=' + $item.data('origin') + ']').text() );

				//amount
				$('#amount').val( $item.data('amount') );

				//methodFK
				$('#methodFK').val( $('#methodList').find('[data-id=' + $item.data('method') + ']').text() );

				//recipientFK
				$('#recipientFK').val( $('#recipientList').find('[data-id=' + $item.data('recipient') + ']').text() );

				//statusFK
				$('#' + $item.data('status') ).prop({checked: true});

				if( $this.hasClass('fork') ) $('#id').val('');

			} else {
				$.post('ajax/payment.php', 'action=get&id=' + $this.attr('href'), function(data){
					var decoder = $('textarea'),
						$field = null,
						$radio = null;
					$.each(data, function(key, value){
						$field = $('#' + key);
						if( !isNaN(parseInt(value, 10)) ){
							$radio = $('#' + key.replace(/FK/, '') + '_' + value);
						} else {
							$radio = null;
						}
						if( $field.length ){
							//all datalist are for inputs corresponding to foreign key columns (value is an integer)
							//label column is an exception (value is a string)
							if( key != 'label' && $field.is('[list]') ){ //datalist
								$field.val( $('#' + $field.attr('list') ).find('[data-id=' + value + ']').text() );

							} else if( $field.is('textarea') ){
								$field.val( decoder.html( value ).text() );

							} else if( $field.is('#paymentDate') ){
								var d = value.split('-');
								$field.val( d[2] + '/' + d[1] + '/' + d[0] );

							} else {
								$field.val( decoder.html( value ).text() );
							}
						} else if( $radio.length ){
							$radio.prop({checked: true});
						}
					});

					if( $this.hasClass('fork') ) $('#id').val('');
				});
			}
		}).delegate('.trash', 'click', function(e){
			e.preventDefault();
			if( confirm('Êtes-vous sûr de supprimer ce paiement ?') ){
				var tf = $timeframe.find(':checkbox:not(.year):checked').map(function(){ return this.value; }).get().join(','),
					params = 'action=delete&id=' + $(this).attr('href') + '&timeframe=' + tf;

				if( $body.data('internet') == 'offline' ){
					var deletions = localStorage.getObject('deletions') || [];
					deletions.push(params + '&owner=' + $currentOwner.val() );
					localStorage.setObject('deletions', deletions);

					alert('Cette suppression sera prise en compte une fois que vous repasserez en ligne.');

					$container.isotope('remove', $(this).closest('.item')).isotope('reLayout');
					applyFilters();

				} else {
					$.post('ajax/payment.php', params, function(data){
						if( data.payments ){
							$form.find('datalist, select[id]').loadList();
							$filter.find('select').loadList();

							refreshParts( data );
						} else {
							alert( data );
						}
					});
				}
			}
		});

		$('.sort a').click(function(e){
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
		$('.next_month a').click(function(e){
			e.preventDefault();
			if( confirm("Êtes-vous sûr ?\nAucune vérification ne sera réalisée !") ){

				//is next month present in time frame
				var now = new Date(),
					next = new Date(now.getFullYear(), now.getMonth()+1, 1),
					newMonth = next.getFullYear() + '-' + (next.getMonth() + 1);

				var needReload = false;
				if( $('#time_frame').find('input[value=' + newMonth + ']').length ){
					//make sure the checkbox is checked and trigger the change event to check the corresponding year checkbox if needed
					$('#time_frame').find('input[value=' + newMonth + ']').pro({ checked: true }).change();
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
				$(this).closest('ul').parent().find('.year').prop({ checked: true });
			}

			if( !$form.hasClass('submitting') ){
				//wait 500ms before reloading data, help when user check several checkboxes quickly
				clearTimeout(buffer);
				buffer = setTimeout(function(){ reloadParts(); }, 500);
			}
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
		$('body').delegate('.button', 'click', function(){
			$(this).blur().addClass('primary').parent().find('.primary').not( $(this) ).removeClass('primary');
		});

	//switch between chart and isotope view
		$('.switch_view a').data('view', 'isotope').click(function(e){
			var $this = $(this),
				paymentSections = ['#container', '#filter', '#time_frame', '.sort', '.next_month', '.form_switch', '#calculs'],
				chartSections = ['#chart', '.chart_type'],
				currentView;

			$this.data('view', $(this).data('view') == 'isotope' ? 'chart' : 'isotope' )
				 .toggleClass('isotope chart');
			currentView = $this.data('view');

			$.each(paymentSections, function(index, section){
				currentView == 'isotope' ? $(section).css('display', '') : $(section).css('display', 'none');
			});

			$.each(chartSections, function(index, section){
				currentView == 'chart' ? $(section).css('display', 'block') : $(section).css('display', 'none');
			});

			if( currentView == 'chart' ){
				$('#chart').height( $('body').height() - 200); //force the chart to use all the available viewport height
				reloadChart( null );

			} else if( chart ){ //destroy the chart if present, clean memory and timers
				chart.destroy();
				chart = null;
			}
		});

	//chart type
		$('.chart_type a').click(function(e){
			e.preventDefault();
			reloadChart( this.rel );
		});

	/**
	 * refresh the payments, forecast and sum parts with ajax return
	 * @params json data: array containing payments, forecasts and sums html code
	 */
	function refreshParts( data ){
		$sums.empty().html( data.sums );

		var $forecast = $('#forecasts'),
			title = $forecast.children('h2').detach();
		$forecast.empty().html( data.forecasts ).prepend( title );

		var $balance = $('#balances'),
			title = $balance.children('h2').detach();
		$balance.empty().html( data.balances ).prepend( title );

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

		var cachedData,
			lastModified = 0,
			key = $currentOwner.val() + '_' + tf;

		try {
			cachedData = localStorage.getObject(key);

			if( cachedData ){
				lastModified = cachedData.lastModified;
			}
		} catch( e ){
			alert(e);
		}

		//resfresh sums
		$('header').addClass('loading');
		$.ajax('ajax/payment.php', {
			data: 'action=refresh&timeframe=' + tf + '&owner=' + $currentOwner.val(),
			dataType: 'json',
			type: 'post',
			headers: {
				'If-Modified-Since': lastModified
			},
			success: function(data, textStatus, jqXHR){
				//server will send a 304 status if the list has not changed
				if( jqXHR.status == 200 ){
					try {
						lastModified = jqXHR.getResponseHeader('Last-Modified');

						localStorage.setObject(key, {'lastModified': lastModified, 'data': data});
					} catch( e ){
						alert(e);
					}

				} else {
					data = cachedData.data;
				}

				if( data.payments ){
					refreshParts( data );
				} else {
					alert( data );
				}
				$('header').removeClass('loading');
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

		var sortName = $('.sort:visible a.primary').attr('href').substr(1);
		$container.isotope({ sortBy: sortName });
	}


	/**
	 * Gray theme for Highcharts JS
	 * @author Torstein Hønsi
	 */
		Highcharts.theme = {
			colors: ["#DDDF0D", "#7798BF", "#55BF3B", "#DF5353", "#aaeeee", "#ff0066", "#eeaaee",
				"#55BF3B", "#000066", "#FF9900", "#097054", "#FEB729", "#669966", "#00275E",
				"#BD2031", "#9BE1FB", "#FFFF00", "#C5EFFD", "#990099", "#669900", "#006295", "#FFDE00",
				"#5BC236", "#C7EB6E", "#D75A20", "#F8F2E5", "#00FF00"],
			chart: {
				backgroundColor: {
					linearGradient: [0, 0, 0, 400],
					stops: [
						[0, 'rgb(96, 96, 96)'],
						[1, 'rgb(16, 16, 16)']
					]
				},
				borderWidth: 0,
				borderRadius: 15,
				plotBackgroundColor: null,
				plotShadow: false,
				plotBorderWidth: 0
			},
			lang: {
				decimalPoint: '.',
				thousandsSep: "'",
				loading: 'chargement...',
				months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
				weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
			},
			title: {
				style: {
					color: '#FFF',
					font: '16px Lucida Grande, Lucida Sans Unicode, Verdana, Arial, Helvetica, sans-serif'
				}
			},
			subtitle: {
				style: {
					color: '#DDD',
					font: '12px Lucida Grande, Lucida Sans Unicode, Verdana, Arial, Helvetica, sans-serif'
				}
			},
			xAxis: {
				gridLineWidth: 0,
				lineColor: '#999',
				tickColor: '#999',
				labels: {
					style: {
						color: '#999',
						fontWeight: 'bold'
					}
				},
				title: {
					style: {
						color: '#AAA',
						font: 'bold 12px Lucida Grande, Lucida Sans Unicode, Verdana, Arial, Helvetica, sans-serif'
					}
				}
			},
			yAxis: {
				alternateGridColor: null,
				minorTickInterval: null,
				gridLineColor: 'rgba(255, 255, 255, .1)',
				lineWidth: 0,
				tickWidth: 0,
				labels: {
					style: {
						color: '#999',
						fontWeight: 'bold'
					}
				},
				title: {
					style: {
						color: '#AAA',
						font: 'bold 12px Lucida Grande, Lucida Sans Unicode, Verdana, Arial, Helvetica, sans-serif'
					}
				}
			},
			legend: {
				itemStyle: {
					color: '#CCC'
				},
				itemHoverStyle: {
					color: '#FFF'
				},
				itemHiddenStyle: {
					color: '#333'
				}
			},
			labels: {
				style: {
					color: '#CCC'
				}
			},
			tooltip: {
				backgroundColor: {
					linearGradient: [0, 0, 0, 50],
					stops: [
						[0, 'rgba(96, 96, 96, .8)'],
						[1, 'rgba(16, 16, 16, .8)']
					]
				},
				borderWidth: 0,
				style: {
					color: '#FFF'
				}
			},
			plotOptions: {
				line: {
					dataLabels: {
						color: '#CCC'
					},
					marker: {
						lineColor: '#333'
					}
				},
				spline: {
					marker: {
						lineColor: '#333'
					}
				},
				scatter: {
					marker: {
						lineColor: '#333'
					}
				}
			},

			toolbar: {
				itemStyle: {
					color: '#CCC'
				}
			},

			navigation: {
				buttonOptions: {
					backgroundColor: {
						linearGradient: [0, 0, 0, 20],
						stops: [
							[0.4, '#606060'],
							[0.6, '#333333']
						]
					},
					borderColor: '#000000',
					symbolStroke: '#C0C0C0',
					hoverSymbolStroke: '#FFFFFF'
				}
			},

			exporting: {
				buttons: {
					exportButton: {
						symbolFill: '#55BE3B'
					},
					printButton: {
						symbolFill: '#7797BE'
					}
				}
			},

			// special colors for some of the demo examples
			legendBackgroundColor: 'rgba(48, 48, 48, 0.8)',
			legendBackgroundColorSolid: 'rgb(70, 70, 70)',
			dataLabelsColor: '#444',
			textColor: '#E0E0E0',
			maskColor: 'rgba(255,255,255,0.3)'
		};

		// Apply the theme
		var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

	/** charts options **/
		var expenseOptions = {
			chart: {
				renderTo: 'chart',
				defaultSeriesType: 'column'
			},

			title: {
				text: 'Sommes selon monnaie, récurrence et type par mois'
			},

			xAxis: {
				categories: []
			},

			yAxis: {
				title: {
					text: 'Montant'
				}
			},

			tooltip: {
				formatter: function() {
					return '<b>'+ this.x +'</b><br/>'+
						'<span style="color:' + this.series.color + '">' + this.series.name + '</span>: ' + this.y + '<br/>' +
						'Total: '+ this.point.stackTotal;
				}
			},

			plotOptions: {
				column: {
					stacking: 'normal'
				}
			},
			series: []
		};

		var evolutionOptions = {
			chart: {
				renderTo: 'chart',
				zoomType: 'xy',
				spacingRight: 20
			},
			title: {
				text: 'Evolution des comptes'
			},
			subtitle: {
				text: document.ontouchstart === undefined ?
					'Sélectionner une partie de la courbe pour zoomer' :
					'Faite glisser votre doigt sur une partie de la courbe pour zoomer'
			},
			xAxis: {
				type: 'datetime',
				maxZoom: 14 * 24 * 3600 * 1000, // fourteen days
				title: {
					text: null
				},
				dateTimeLabelFormats: {
					day: '%e %b'
				}
			},
			yAxis: {
				title: {
					text: 'Montant'
				},
				startOnTick: false,
				showFirstLabel: false
			},
			legend: {
				align: 'right',
				verticalAlign: 'top',
				y: 20,
				floating: true,
				borderWidth: 0
			},
			tooltip: {
				shared: true,
				formatter: function() {
					var s = '<b>'+ Highcharts.dateFormat('%A %d %B', this.x) +'</b>';

					$.each(this.points, function(i, point) {
						s += '<br/><span style="color:' + point.series.color + ';">' + point.series.name +'</span>: '+ Highcharts.numberFormat(point.y, 2, '.', '\'') + ' ' + legendCurrency[point.series.name];
					});

					return s;
				},
			},
			plotOptions: {
				series: {
					lineWidth: 1,
					marker: {
						enabled: false,
						states: {
							hover: {
								enabled: true,
								radius: 5
							}
						}
					},
					states: {
						hover: {
							lineWidth: 1
						}
					}
				}
			},
			series: []
		};

		var recipientOptions = {
			chart: {
				renderTo: 'chart',
				defaultSeriesType: 'column'
			},
			title: {
				text: 'Dépenses par bénéficiaire en pourcentage'
			},
			xAxis: {
				categories: []
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Pourcentage'
				}
			},
			tooltip: {
				formatter: function(){
					return '<b>' + this.series.name + '</b>: ' + this.y + ' ' + legendCurrency[ this.series.stackKey.substr(-1) ] + ' (' + Math.round(this.percentage) + '%)';
				}
			},
			plotOptions: {
				column: {
					stacking: 'percent'
				}
			},
			series: []
		};

	function reloadChart( type ){
		//chart type
		if( type == null ) type = $('.chart_type a.primary').attr('rel');

		var cachedData,
			lastModified = 0,
			key = $currentOwner.val() + '_' + type;

		try {
			cachedData = localStorage.getObject(key);
			if( cachedData ){
				lastModified = cachedData.lastModified;
			}
		} catch( e ){
			alert(e);
		}

		//get graph data
		$('header').addClass('loading');
		$.ajax('ajax/payment.php', {
			data: 'action=chart&type=' + type,
			dataType: 'json',
			type: 'post',
			headers: {
				'If-Modified-Since': lastModified
			},
			success: function(data, textStatus, jqXHR){
				//server will send a 304 status if the list has not changed
				if( jqXHR.status == 200 ){
					try {
						lastModified = jqXHR.getResponseHeader('Last-Modified');

						localStorage.setObject(key, {'lastModified': lastModified, 'data': data});
					} catch( e ){
						alert(e);
					}

				} else {
					data = cachedData.data;
				}

				if( data.sums ){
					try{
						if( chart ) chart.destroy();
						switch( type ){
							default:
							case 'expense':
									expenseOptions.xAxis.categories = data.months;

									//json encode transform the sums in string, float are needed
									$.each( data.sums, function(i, sum){
										data.sums[i].data = $.map(sum.data, function(s){ return parseFloat(s); });
									});
									expenseOptions.series = data.sums;

									chart = new Highcharts.Chart(expenseOptions);
								break;
							case 'evolution':
									legendCurrency = [];
									evolutionOptions.series = [];

									$.each(data.sums, function(origin, infos){
										evolutionOptions.series.push({
											name: origin,
											pointInterval: 24 * 3600 * 1000,
											pointStart: Date.UTC(2011, 01, 01), //javascript month start at 0
											data: $.map(infos.amounts, function(a){ return parseFloat(a); })
										});

										legendCurrency[ origin ] = infos.symbol;
									});

									chart = new Highcharts.Chart(evolutionOptions);
								break;
							case 'recipient':
									legendCurrency = data.currencies;

									recipientOptions.xAxis.categories = data.months;

									//json encode transform the sums in string, float are needed
									$.each( data.sums, function(i, infos){
										data.sums[i].data = $.map(infos.data, function(s){ return parseFloat(s); });
									});
									recipientOptions.series = data.sums;

									chart = new Highcharts.Chart(recipientOptions);
								break;
						}
					} catch(e) {
						alert(e.message);
					}
				} else {
					alert( data );
				}
				$('header').removeClass('loading');
			}
		});
	}


	//timeout function for loadList() and reloadParts();
	function ajaxCalls(){
		if( !delayAjax ){
			if( delayTimeout ) clearTimeout(delayTimeout);
			$form.find('datalist, select[id]').loadList();
			reloadParts();
		}
		else delayTimeout = setTimeout(function(){ ajaxCalls(); }, 1000);
	}

	//"onload" ajax call for data
	ajaxCalls();
});



/**
 * ajax load for <datalist> and <select> options
 * use localStorage to cache results
 * use 'If-Modified-Since' / 'Last-Modified' header and 200 / 304 statuses pairs to get only fresh data when needed
 */
$.fn.loadList = function(){
	return this.each(function(){
		var $list = $(this),
			key = $list.attr('id').replace(/FK$/g, 'List'),
			decoder = $('<textarea>'),
			cachedData,
			lastModified = 0;

		if( isFennec ){
			$list.siblings('select').remove();
		}

		try {
			cachedData = localStorage.getObject(key);
			if( cachedData ){
				lastModified = cachedData.lastModified;
			}
		} catch( e ){
			alert(e);
		}

		//ask the list values to the server and create the <option>s with it
		$('header').addClass('loading');
		$.ajax('ajax/loadList.php', {
			data: 'field=' + key,
			dataType: 'json',
			cache: false,
			headers: {
				'If-Modified-Since': lastModified
			},
			success: function(data, textStatus, jqXHR){
				//server will send a 304 status if the list has not changed
				if( jqXHR.status == 200 ){
					try {
						lastModified = jqXHR.getResponseHeader('Last-Modified');

						localStorage.setObject(key, {'lastModified': lastModified, 'data': data});
					} catch( e ){
						alert(e);
					}

				} else {
					data = cachedData.data;

					if( $list.find('option:gt(0)').length ){
						//options already present, no need to fill the field
						return;
					}
				}

				var isDatalist = $list.is('datalist');

				if( isDatalist ){
					$list.empty();

					if( !Modernizr.input.list || isFennec ){
						var fallback = $('<select>'),
							field = $list.closest('form').find('input[list="'+ $list.attr('id') +'"]');
						if( field.length ) fallback.attr('name', field.attr('name'));
						fallback.append('<option value="">');

						if( isFennec ) fallback.addClass('tmp').insertBefore( $list );
						else $list.append( fallback );
					}

				} else {
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
						if( !Modernizr.input.list ){
							$('<option>', { "value": name, text: name, 'data-id': id }).appendTo( $list.children('select') );
						} else if( isFennec ){
							$('<option>', { "value": name, text: name, 'data-id': id }).appendTo( $list.siblings('select') );
						} else {
							$('<option>', { "value": name, text: name, 'data-id': id }).appendTo( $list );
						}
					} else {
						$('<option>', { "value": id, text: name }).appendTo( $list );
					}
				});

				if( isFilter ) $list.val( $list.data('sav') );

				if( $list.data('selectedId') ){
					$list.val( list.data('selectedId') );
					$list.removeData('selectedId');
				}

				$('header').removeClass('loading');
			}
		});
	});
}

/**
 * reset the payment form fields
 */
$.fn.resetForm = function(){
	return this.each(function(){
		var $f = $(this),
			d = new Date();

		$f.removeClass('deploy')
			.find(':input:visible, #id').each(function(i, field){
				if( field.type == 'radio' ) field.checked = false;
				else field.value = '';
			});
		$f.find('.ownerChoice').hide();
		$('#paymentDate').val( ( d.getDate() < 10 ? '0' + d.getDate() : d.getDate() ) + '/' + ( ( d.getMonth() + 1 ) < 10 ? '0' : '' ) + ( d.getMonth() + 1 ) + '/' + d.getFullYear() );
		$('#recurrent_0').prop({ checked: true });
		$('#type_2').prop({ checked: true });
		$('#status_3').prop({ checked: true });
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

/**
 * localStorage method for caching javascript objects
 */
if( typeof Storage != "undefined" ){
	Storage.prototype.setObject = function(key, value){
		this.setItem(key, JSON.stringify(value));
	}

	Storage.prototype.getObject = function(key){
		return this.getItem(key) && JSON.parse( this.getItem(key) );
	}
}

/* jquery template getter functions */
function getValue( data, index ){
	return data[index];
}

function getSymbol( data, index ){
	return data[index].symbol;
}

/*! Copyright (c) 2010 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * https://github.com/brandonaaron/jquery-mousewheel
 *
 * Version: 3.0.4
 *
 * Requires: 1.2.2+
 */
(function($) {
	var types = ['DOMMouseScroll', 'mousewheel'];

	$.event.special.mousewheel = {
		setup: function() {
			if ( this.addEventListener ) {
				for ( var i=types.length; i; ) {
					this.addEventListener( types[--i], handler, false );
				}
			} else {
				this.onmousewheel = handler;
			}
		},

		teardown: function() {
			if ( this.removeEventListener ) {
				for ( var i=types.length; i; ) {
					this.removeEventListener( types[--i], handler, false );
				}
			} else {
				this.onmousewheel = null;
			}
		}
	};

	$.fn.extend({
		mousewheel: function(fn) {
			return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
		},

		unmousewheel: function(fn) {
			return this.unbind("mousewheel", fn);
		}
	});


	function handler(event) {
		var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
		event = $.event.fix(orgEvent);
		event.type = "mousewheel";

		// Old school scrollwheel delta
		if ( event.wheelDelta ) { delta = event.wheelDelta/120; }
		if ( event.detail     ) { delta = -event.detail/3; }

		// New school multidimensional scroll (touchpads) deltas
		deltaY = delta;

		// Gecko
		if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
			deltaY = 0;
			deltaX = -1*delta;
		}

		// Webkit
		if ( orgEvent.wheelDeltaY !== undefined ) { deltaY = orgEvent.wheelDeltaY/120; }
		if ( orgEvent.wheelDeltaX !== undefined ) { deltaX = -1*orgEvent.wheelDeltaX/120; }

		// Add event and delta to the front of the arguments
		args.unshift(event, delta, deltaX, deltaY);

		return $.event.handle.apply(this, args);
	}
})(jQuery);