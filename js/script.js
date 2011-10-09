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

		setInterval(function(){ cache.update(); }, 10000);
	}

	//just force reload of the page if an update is available
	window.applicationCache.addEventListener('updateready', function(){
		if( confirm('Une nouvelle version est disponible, voulez-vous recharger la page ?') ){
			window.applicationCache.swapCache();
			window.location.reload();
		} else {
			delayAjax = false;
		}
	}, false);

	window.applicationCache.addEventListener('checking', function(){
		//delay ajax calls if there is a new manifest version
		delayAjax = true;
	}, false);

	/*window.applicationCache.addEventListener('downloading', function(){
	}, false);*/

	window.applicationCache.addEventListener('noupdate', function(){
		delayAjax = false;
	}, false);

	window.applicationCache.addEventListener('error', function(){
		//delay ajax calls if there is a new manifest version
		delayAjax = false;
		alert('Error while downloading the new version');
	}, false);

	//sometimes a DOM exception is raised by update() on opera...
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

				for( i = 0; i < ca.length; i++ ){
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

/*
 * jQuery each2 - v0.2 - 8/02/2010
 * http://benalman.com/projects/jquery-misc-plugins/
 *
 * Inspired by James Padolsey's quickEach
 * http://gist.github.com/500145
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function(a){var b=a([1]);a.fn.each2=function(d){var c=-1;while((b.context=b[0]=this[++c])&&d.call(b[0],c,b)!==false){}return this}})(jQuery);

$(document).ready(function(){

	var $body		   = $('body'),
		$container	   = $('#container'),
		$sums		   = $('#sums'),
		$forecast	   = $('#forecasts'),
		$balance	   = $('#balances'),
		$form		   = $('#payment_form'),
		$filter		   = $('#filter'),
		$timeframe	   = $('#time_frame'),
		$currentOwner  = $('#current_owner'),
		filters		   = {},
		buffer		   = null,
		chart		   = null,
		legendCurrency = [],
		currentDate	   = new Date();

		if( currentDate.getDate() > 24 ){
			currentDate.setMonth(currentDate.getMonth() + 1 );
			var currentMonth = currentDate.getFullYear() + '-' + (currentDate.getMonth()+1);
		} else {
			var currentMonth = currentDate.getFullYear() + '-' + (currentDate.getMonth()+1);
		}

		currentDate.setMonth(currentDate.getMonth() + 1 );
		var nextMonth = currentDate.getFullYear() + '-' + (currentDate.getMonth()+1);

	/* online - offline modes */
		/* @todo to finish and test */
		/*window.addEventListener("online", function(){
			$body.removeClass("offline")
				 .data('internet', 'online');

			var deletions = localStorage.getObject('deletions') || [] ,
				modifications = localStorage.getObject('modifications') || [],
				i;

			if( deletions.length ){
				var chore = deletions;
				for( i = 0; i < deletions.length; i++ ){
					$.post('ajax/payment.php', deletions[i] + '&offline=1', function(data){
						if( data != 'ok' ){
							alert(data);
						} else {
							chore.shift();
						}
					});
				}

				if( chore.length ){
					alert('not all deletions actions have been sent');
					localStorage.setObject('deletions', chore);
				} else {
					localStorage.removeItem('deletions');
				}
			}

			if( modifications.length ){
				var chore = modifications;
				for( i = 0; i < modifications.length; i++ ){
					$.post('ajax/payment.php', modifications[i] + '&offline=1', function(data){
						if( data != 'ok' ){
							alert(data);
						} else {
							chore.shift();
						}
					});
				}

				if( chore.length ){
					alert('not all deletions actions have been sent');
					localStorage.setObject('modifications', chore);
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
		}*/

	//ajax global management
		$('header').ajaxStart(function(){
			$(this).addClass('loading');
		}).ajaxStop(function(){
			$(this).removeClass('loading');
		}).ajaxError(function(event, xhr, settings, exception){
			$(this).removeClass('loading');
			if( xhr.responseText != '' ) alert("Error requesting page " + settings.url + ", error : " + xhr.responseText, 'error');
		});

		//cache is managed via Last-Modified headers and localeStorage
		$.ajaxSetup({ 'cache': false });

	//forms actions
		//add button
		$('.form_switch').find('a').click(function(e){
			e.preventDefault();
			e.stopPropagation(); //else it will fire $(document).click() listener

			$form.resetForm()
				 .addClass('deploy');

			//hide the form when clicking outside
			$body.click(function(e){
				$form.removeClass('deploy').removeClass('submitting');
			});
		});

		$form
			.submit(function(e){
				e.preventDefault();
				if( $(this)[0].checkValidity() && !$form.hasClass('submitting') ){
					$form.addClass('submitting'); //multiple call protection and visual loading display

					//is payment month present in time frame
					var paymentDate = $('#paymentDate').val().split('/'),
						tmp = new Date(paymentDate[2], parseInt(paymentDate[1], 10) - 1, paymentDate[0]),
						tmp = (tmp.getDate() > 24 ? new Date(tmp.getFullYear(), tmp.getMonth() + 1, 1) : tmp),
						m = tmp.getMonth() + 1, //javascript month index start at 0
						newMonth = tmp.getFullYear() + '-' + ( (''+m).length == 1 ? '0' + m : m ); //format YYYY-MM

					var isUpdate = ( $('#id').val() > 0 ? true : false );
					if( isUpdate ){
						var $item = $('#payment_' + $('#id').val());
						var oldDate = $item.attr('data-date'), //unix timestamp in seconds
							oldMonth = $item.attr('data-month'); //format YYYY-MM
					}

					/**
					 * in which case are we ?
					 * delta (&d=1)
					 *		payments list -> delta only
					 *		balance -> only if current month
					 *		sums -> payment month data
					 *		forecasts -> only if status 2 or 4 and current month or next
					 * timeframe change (&timeframe=)
					 *		payments list -> payment month data, delete payment in list if update
					 *		balance -> only if payment date (new or old one) <= today
					 *		sums -> payment month data (new and old one)
					 *		forecasts -> only if current month or next
					 * reload
					 *		payments list -> not needed
					 *		balance -> not needed
					 *		sums -> not needed
					 *		forecasts ->  not needed
					 *		save new timeframe and use it after reload
					 */
					var actionCase = false,
						$gotMonth = $timeframe.find('input').filter('[value=' + newMonth + ']'),
						params = $form.find('input, textarea, select').serialize();

					if( $gotMonth.length ){
						//make sure the checkbox is checked and trigger the change event to check the corresponding year checkbox if needed
						if( !$gotMonth.is(':checked') ){
							$timeframe.find('input').filter('[value=' + newMonth + ']').prop({ checked: true }).trigger('change');

							timeframeSnapshot.push(newMonth);
							actionCase = 'timeframe-change';
						} else {
							actionCase = 'delta';
						}
					} else {
						actionCase = 'reload';
					}

					if( !Modernizr.input.list ){
						params = $form.find('input, textarea').filter(':not([list])').serialize();

						$form.find('input').filter('[list]').each(function(){
							var $fallback = $form.find('select').filter('[name="'+ this.name +'"]'),
								param = '&' + this.name + '=';
							params += param + ( $fallback.val() != '' ? $fallback.val() : this.value );
						});
					}

					//add the case needed info
					if( actionCase == 'timeframe-change' ){
						params += '&timeframe=' + newMonth;
					} else if( actionCase == 'delta' ){
						params += '&d=1';
					}

					try{
						var originParam = 0;
						var originCache = localStorage.getObject( 'originList' );
						if( originCache ){
							originParam = new Date(originCache.lastModified).getTime() / 1000;
						}

						var methodParam = 0;
						var methodCache = localStorage.getObject( 'methodList' );
						if( methodCache ){
							methodParam = new Date(methodCache.lastModified).getTime() / 1000;
						}

						var recipientParam = 0;
						var recipientCache = localStorage.getObject( 'recipientList' );
						if( recipientCache ){
							recipientParam = new Date(recipientCache.lastModified).getTime() / 1000;
						}

						var labelParam = 0;
						var labelCache = localStorage.getObject( 'labelList' );
						if( labelCache ){
							labelParam = new Date(labelCache.lastModified).getTime() / 1000;
						}

						var locationParam = 0;
						var locationCache = localStorage.getObject( 'locationList' );
						if( locationCache ){
							locationParam = new Date(locationCache.lastModified).getTime() / 1000;
						}
					} catch( e ){
						alert(e);
					}
					/*if( $body.data('internet') == 'offline' ){
						var modifications = localStorage.getObject('modifications') || [];
						modifications.push(params + '&owner=' + $currentOwner.val() );
						localStorage.setObject('modifications', modifications);

						//form hide
						$body.unbind('click');
						$form.removeClass('submitting').removeClass('deploy');

						alert('Cette modification sera prise en compte une fois que vous repasserez en ligne.');
					} else {*/
						$('header').addClass('loading');
						$.ajax({
							url: 'ajax/payment.php',
							data: params
								+ '&tsOrigin=' + originParam
								+ '&tsMethod=' + methodParam
								+ '&tsRecipient=' + recipientParam
								+ '&tsLabel=' + labelParam
								+ '&tsLocation=' + locationParam,
							cache: false,
							type: 'POST',
							dataType: 'json',
							complete: function(){
								$form.removeClass('submitting');
							},
							success: function(data, textStatus, jqXHR){
								$form.removeClass('submitting'); //security, sometimes complete() is not called...

								if( actionCase == 'reload' && data == 'ok' ){
									//save the new timeframe for use after the reload
									try {
										timeframeSnapshot.push( newMonth );
										localStorage.setObject( $currentOwner.val() + '_timeframe', timeframeSnapshot );
										localStorage.setObject('filters', filters);
										localStorage.setObject('period_dates', [$('#date_from').val(), $('#date_to').val()]);
									} catch( e ){
										alert(e);
									}

									//the time frame is missing a month, need to reload the page
									window.location.reload();

								} else if( actionCase == 'timeframe-change' && data.payments ){
									$('header').removeClass('loading');
									//form hide
									$body.unbind('click');
									$form.removeClass('deploy');

									//store localy the new data
									store( data );

									if( isUpdate ){
										$container.isotope('remove', $item); //relayout will be done by refreshParts()
									} else {
										//add missing sums for refreshParts
										for( var i = 0; i < timeframeSnapshot.length; i++ ){
											try {
												var month = timeframeSnapshot[i];
												if( !data.sums[month] ){
													data.sums[month] = localStorage.getObject( $currentOwner.val() + '_sums_' + month );
												}
											} catch( e ){
												alert(e);
											}
										}
									}

									refreshParts( data );

									//focus the payment add button
									$('.form_switch:visible').find('a').focus();
								} else if( data.delta ){
									$('header').removeClass('loading');
									//form hide
									$body.unbind('click');
									$form.removeClass('deploy');

									//no need to remove the localStorage for payment month data as lastModified date is obsolete
									store( data );

									refreshParts( data );
									refreshWithDelta( data );
								} else {
									//form errors display
									formErrors(data);
									return;
								}

								//at least one of the filter or form list has changed
								if( data.origins ){
									$('#origin_filter, #originList').loadList();
								}
								if( data.recipients ){
									$('#recipient_filter, #recipientList').loadList();
								}
								if( data.methods ){
									$('#methodList').loadList();
								}
								if( data.locations ){
									$('#location_filter, #locationList').loadList();
								}
								if( data.labels ){
									$('#labelList').loadList();
								}
							}
						});
					//}
				}
			})
			.delegate('input[name=typeFK]', 'change', function(e){
				if( this.id == 'type_3' ){
					$form.find('.ownerChoice').fadeIn();
				} else {
					$form.find('.ownerChoice').fadeOut();
				}
			})
			.find('fieldset').click(function(e){
				e.stopPropagation();
			});

		$('#formCancel').click(function(){
			$body.unbind('click');
			$form.removeClass('deploy').removeClass('submitting').resetForm();
		});

		//swap comma for dot in the amount field
		$('#amount').keydown(function(e){
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

		/**
		 * date modification shortcuts
		 * use up and down arrow keys
		 * shift for changes by 10, ctrl for months and alt for years
		 */
		$('#paymentDate, #date_from, #date_to').keydown(function(e){
			if( this.value != '' && ( e.keyCode == 40 || e.keyCode == 38 ) ){
				var paymentDate = this.value.split('/'),
					tmp = new Date(paymentDate[2], parseInt(paymentDate[1], 10) - 1, paymentDate[0]);

				var delta = 0;
				if( e.keyCode == 38 ){
					delta = 1;
				} else {
					delta = -1;
				}

				//per 10 days
				if( e.shiftKey ){
					delta *= 10;
				}

				//year change
				if( e.altKey ){
					tmp.setFullYear(tmp.getFullYear() + delta);

				//month change
				} else if( e.ctrlKey ){
					tmp.setMonth(tmp.getMonth() + delta);

				//day change
				} else {
					tmp.setDate(tmp.getDate() + delta);
				}

				var m = tmp.getMonth() + 1,
					d = tmp.getDate();
				this.value = ( (''+d).length == 1 ? '0' + d : d ) + '/' + ( (''+m).length == 1 ? '0' + m : m ) + '/' + tmp.getFullYear();
			}
		});

		//@todo temporary, to avoid error on form submit for non datalist supporting browser
		if( !Modernizr.input.list ){
			$form.find('input').filter('[list]').each2(function(i, $list){
				$list.removeProp('required');
			});
		}

	//application shortcuts
		$(document).unbind('keydown').keydown(function(e){
			//"a" pressed for add
			if( e.which == 65 ){
				$form.filter(':not(.deploy)')
					.resetForm()
					.addClass('deploy');

			//escape pressed
			} else if( e.keyCode == 27 ){
				if( $form.hasClass('deploy') ){
					$form.removeClass('deploy').removeClass('submitting');
					$('.form_switch').find('a').focus(); //remove the focus from any field or button of the form

				//close any opened filter dropdown
				} else if( $filter.find('section').find('.switch.active').length ){
					$filter.find('section').find('.switch.active').removeClass('active')
					$filter.find('.dropdown.deploy').removeClass('deploy');
				}
			}
		});

	//isotope
		$container.isotope({
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
		})
		.delegate('.edit, .fork', 'click', function(e){
			e.preventDefault();
			$form.resetForm()
				 .addClass('deploy');

			var $this = $(e.target);

			//edit is for update
			//fork is for dupplication, so action is "add" an id has no value
			if( $this.hasClass('edit') ) $('#action').val('update');

			//in online mode take the values directly from database
			//in offline mode, get the values from DOM
			/*if( $body.data('internet') == 'offline' ){
				$('#id').val( $this[0].href );

				var $item = $this.closest('.item'),
					classes = $item.attr('class');
				classes.split(' ');
				for( var i = 0; i < classes.length; i++ ){
					var c = classes[i];
					if( c == 'recurrent' || c == 'punctual' ){
						$('#recurrent_' + ( c == 'recurrent' ? 1 : 0 )).prop({checked: true});

					} else if( c.indexOf('type_') != -1 || c.indexOf('currency_') != -1 || c.indexOf('location_') != -1 ){
						$('#' + c).prop({checked: true});
					}
				}

				$('#label').val( $item.find('dd').eq(1).text() );

				var d = new Date( $item.data('date') );
				$('#paymentDate').val( ( d.getDate() < 10 ? '0' + d.getDate() : d.getDate() ) + '/' + ( ( d.getMonth() + 1 ) < 10 ? '0' : '' ) + ( d.getMonth() + 1 ) + '/' + d.getFullYear() );

				//comment
				$('#comment').val( $item.data('comment') );

				//originFK
				$('#originFK').val( $('#originList').find('option').filter('[data-id=' + $item.data('origin') + ']').text() );

				//amount
				$('#amount').val( $item.data('amount') );

				//methodFK
				$('#methodFK').val( $('#methodList').find('option').filte('[data-id=' + $item.data('method') + ']').text() );

				//recipientFK
				$('#recipientFK').val( $('#recipientList').find('option').filter('[data-id=' + $item.data('recipient') + ']').text() );

				//statusFK
				$('#' + $item.data('status') ).prop({checked: true});

				if( $this.hasClass('fork') ) $('#id').val('');

			} else {*/
				$.post('ajax/payment.php', 'action=get&id=' + $this[0].href, function(data){
					var decoder = $('<textarea>'),
						$field = null,
						$radio = null;
					for( var key in data ){
						if( data.hasOwnProperty(key) ){
							var value = data[key];
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
									$field.val( $('#' + $field.attr('list') ).find('option').filter('[data-id=' + value + ']').text() );

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
						}
					}

					if( $this.hasClass('fork') ) $('#id').val('');
				});
			//}
		})
		.delegate('.trash', 'click', function(e){
			e.preventDefault();
			if( confirm('Êtes-vous sûr de supprimer ce paiement ?') ){
				var $item = $(this).closest('.item'),
					itemMonth = $item.attr('data-month'),
					itemStatus = $item.attr('data-status'),
					owner = $currentOwner.val();

				//remove the payment
				$container.isotope('remove', $item).isotope('reLayout');
				//applyFilters();

				//need balance ?
				//yes if payment date <= today
				//sent back by the server if needed

				//need forecast ?
				//yes if for current month or the next and if status is 2 or 4 (foreseen or to pay)
				//sent back by the server if needed

				//need sum ?
				//always, will be sent back by the server

				//prepare the ajax call parameters
				var params = 'action=delete&id=' + this.href;

				/*if( $body.data('internet') == 'offline' ){
					var deletions = localStorage.getObject('deletions') || [];
					deletions.push(params + '&owner=' + $currentOwner.val() );
					localStorage.setObject('deletions', deletions);

					alert('Cette suppression sera prise en compte une fois que vous repasserez en ligne.');


				} else {*/
					$.post('ajax/payment.php', params, function(data){
						//the month payments list has changed, remove the corresponding localStorage lastModified
						//@todo remove the corresponding storage completely when API provide the method to
						localStorage.setObject(owner + '_payments_' + itemMonth, {'lastModified': 0, 'html': null});

						if( !$.isEmptyObject(data) ){
							store( data );

							//replace only the updated sums
							if( data.sums ){
								for( var month in data.sums ){
									if( data.sums.hasOwnProperty(month) ){
										$sums.find('div').filter('[data-month='+ month +']').replaceWith(data.sums[month].html);
									}
								}

								data.sums = null; //so refreshParts does not proceed the sum part
							}

							refreshParts( data );
						}
					});
				//}
			}
		});

	//right column toggle
		$('#calculs').delegate('.toggler', 'click', function(e){
			$(this).parent().toggleClass('fold');
			$container.toggleClass('widder');

			setTimeout(function(){ $container.isotope('reLayout'); }, 50);
		});

	//sort
		$('#sort').delegate('a', 'click', function(e){
			e.preventDefault();
			// get href attribute, minus the '#'
			var sortName = this.href.substring(this.href.indexOf('#') + 1);
			var sortAscending = true;
			if( sortName == 'date' ){
				sortAscending = false;
			}
			$container.isotope({ 'sortBy': sortName, 'sortAscending': sortAscending });
		});

	//filters
		var $filters_switches = $filter.find('section').find('.switch'),
			$filters_dropdowns = $filter.find('.dropdown');

		$filter
			//ternary filter (frequency and currency)
			.delegate('input[type=radio]', 'change', function(e){
				var group = this.name,
					filter = this.value;

				// store filter value in object as an array
				filters[ group ] = [filter];

				applyFilters();
			})
			//multi-value filter
			.delegate('input[type=checkbox]', 'change', function(e){
				var $this = $(this).blur(), //blur() to remove the focus style (like -moz-focusring)
					group = this.name,
					$div = $this.closest('div'),
					$quickUl = $div.find('ul').eq(0),
					$limitedUl = $div.find('ul.limited'),
					$firstCheckbox = $quickUl.find('input').eq(0);

				//manage the "all" value
				if( $this.val() == '*' ){
					//put back the moved <lifind tag + class vs>s
					if( $limitedUl.length ) $quickUl.find('li').filter(':gt(0)').swapIn( $limitedUl );
					//uncheck any checked checkboxes in the second <ul>
					$div.find('input').filter('[type=checkbox]').prop('checked', false);

					$firstCheckbox.prop('checked', true);
				} else {
					//uncheck the "all" checkbox
					$firstCheckbox.prop('checked', false);

					if( $limitedUl.length ){
						if( $this.prop('checked') ){
							//put the checkbox <li> in the first <ul>
							$this.closest('li').swapIn( $quickUl );
						} else {
							//put back the checkbox <li> in the second <ul>
							$this.closest('li').swapIn( $limitedUl );
						}
					}
				}

				//force the "all" value if none checked
				if( !$div.find('input').filter('[type=checkbox]:checked').length ){
					$firstCheckbox.prop('checked', true);
				}

				// store filter value in object
				//.get() to always get an array
				filters[ group ] = $div.find('input').filter('[type=checkbox]:checked').map(function(){ return this.value; }).get();

				applyFilters();

				//output the current value
				$this.updateFiltersOutputs();
			})
			//deploy toggle on title click
			.delegate('h2', 'click', function(e){
				e.preventDefault();
				$filters_dropdowns.filter('.deploy').removeClass('deploy');
				$filters_switches.filter('.active').removeClass('active');
				$(this).find('span').toggleClass('active').closest('section').toggleClass('deploy');
			})
			//dropdown toggle on filter switch
			.delegate('section .switch', 'click', function(e){
				e.preventDefault();
				var isActive = $(this).hasClass('active');
				//close other opened dropdown and deactivate activated switches
				$filters_dropdowns.filter('.deploy').removeClass('deploy');
				$filters_switches.filter('.active').removeClass('active');
				if( !isActive ){
					$(this).addClass('active').siblings('.dropdown').addClass('deploy');
				}
			})
			//dynamic dropdown filtering
			.delegate('input[type=search]', 'keyup', function(){
				var query = $.trim( $(this).val() );

				if( query == '' ){
					$(this).siblings('.limited').find('li').show();
					return;
				}

				query = query.replace(/ /gi, '|'); //add OR for regex query

				var $ul = $(this).siblings('ul').eq(0);
				$(this).siblings('.limited').find('li').each2(function(i, $li){
					$li.find('label').text().search(new RegExp(query, 'i')) == -1 ? $li.hide() : $li.show();
				});
			})
			//period default value at today when focused and empty
			.delegate('#date_from, #date_to', 'focus', function(){
				if( this.value == '' ){
					var d = new Date();
					$(this).val( ( d.getDate() < 10 ? '0' + d.getDate() : d.getDate() ) + '/' + ( ( d.getMonth() + 1 ) < 10 ? '0' : '' ) + ( d.getMonth() + 1 ) + '/' + d.getFullYear() )
						.trigger('keyup');
				}
			})
			//period filtering
			.delegate('#date_from, #date_to', 'keyup', function(){
				var dfrom = document.getElementById('date_from'),
					dto = document.getElementById('date_to'),
					date_from = null,
					date_to = null,
					tmp;
				filters['period'] = ["*"]; //reset
				if( dfrom.checkValidity() && dfrom.value != '' ){
					date_from = dfrom.value.split('/');
					date_from = new Date(date_from[2], parseInt(date_from[1], 10) - 1, date_from[0]);
					date_from = date_from.getTime() / 1000;
				}

				if( dto.checkValidity() && dto.value != '' ){
					date_to = dfrom.value.split('/');
					date_to = new Date(date_to[2], parseInt(date_to[1], 10) - 1, date_to[0]);
					date_to = date_to.getTime() / 1000;
				}

				if( date_from != null || date_to != null ){
					var ids = [];
					$container.find('.item').each2(function(i, $item){
						if( ( date_from != null ? $item.data('date') >= date_from : true )
							&& ( date_to != null ? $item.data('date') <= date_to : true )
						){
							ids.push( '#' + $item[0].id );
						}
					});

					if( ids.length ){
						filters['period'] = ids;
					}
				}

				applyFilters();
			})
			//period input reset
			.delegate('.reset', 'click', function(){
				$('#' + $(this).attr('rel')).val('').trigger('keyup');
			})
			//close any open dropdown for an outside click inside $filter
			.click(function(e){
				var $t = $(e.target);
				if( !$t.closest('.dropdown').length && !$t.is('h2') && !$t.hasClass('switch') && !$t.closest('ul').length ){
					$filters_dropdowns.filter('.deploy').removeClass('deploy');
					$filters_switches.filter('.active').removeClass('active');
				}
			});

		//close any open dropdown for a click outside $filter
			$('body').click(function(e){
				var $t = $(e.target);
				if( !$t.closest($filter).length ){
					$filters_dropdowns.filter('.deploy').removeClass('deploy');
					$filters_switches.filter('.active').removeClass('active');
				}
			});

	//next month recurrent payments generation
		$('.next_month').find('a').click(function(e){
			e.preventDefault();
			if( confirm("Êtes-vous sûr ?\nAucune vérification ne sera réalisée !") ){

				//is next month present in time frame
				var now = new Date(),
					next = new Date(now.getFullYear(), now.getMonth()+1, 1),
					newMonth = next.getFullYear() + '-' + (next.getMonth() + 1);

				var needReload = false;
				if( $timeframe.find('input').filter('[value=' + newMonth + ']').length ){
					//make sure the checkbox is checked and trigger the change event to check the corresponding year checkbox if needed
					$timeframe.find('input').filter('[value=' + newMonth + ']').prop({ checked: true }).change();
				} else {
					needReload = true;
				}

				var tf = $timeframe.find('input').filter(':not(.year):checked').map(function(){ return this.value; }).get().join(',');

				$.post('ajax/payment.php', 'action=initNextMonth' + ( !needReload ? '&timeframe=' + tf : '' ), function(data){
					if( data == 'ok' ){
						if( needReload ){
							//save the new timeframe for use after the reload
							try {
								timeframeSnapshot.push( newMonth );
								localStorage.setObject( $currentOwner.val() + '_timeframe', timeframeSnapshot );
								localStorage.setObject('filters', filters);
								localStorage.setObject('period_dates', [$('#date_from').val(), $('#date_to').val()]);
							} catch( e ){
								alert(e);
							}

							window.location.reload();
						}

					} else if( data.payments ){
						refreshParts( data );

					} else {
						alert( data );
					}
				});
			}
		});

	//time frame chekboxes
		$timeframe
			.delegate('input', 'change', function(e){
				clearTimeout(buffer);
				var $this = $(this);

				//toggle the months checkboxes if the event target is a year checkbox
				if( $this.hasClass('year') ){
					$this.siblings('ul').find('input').prop('checked', $this.prop('checked'));

				//update the year checkbox checked state
				} else {
					$this.closest('li').toggleClass('checked', $this.prop('checked'));
					var $ul = $this.closest('ul');
					$ul.parent().find('.year').prop('checked', $ul.find('input').filter(':checked').length ? true : false);
				}

				//when submitting an add or update the new data will be in the request response
				if( !$form.hasClass('submitting') ){
					//wait 500ms before reloading data, help when user check several checkboxes quickly
					buffer = setTimeout(function(){ reloadParts(false, false); }, 500);
				}
			})
			.delegate('.switch', 'click', function(e){
				$(this).toggleClass('active').siblings('ul').toggleClass('deploy');
			})
			.find('li li input').each2(function(i, $input){
				$input.closest('li').toggleClass('checked', $input.prop('checked'));
			});

	//sums cells hover
		$sums
			.delegate('tbody td', 'mouseenter', function(){
				if( $(this).hasClass('type') ) return;

				var $this = $(this),
					index = $this.parent().find('td').filter(':gt(0)').index($this);

				$this.siblings('.type').addClass('highlight');
				$this.closest('table').find('th.fromto').eq(index).addClass('highlight'); //thead
			})
			.delegate('tbody td', 'mouseleave', function(){
				if( $(this).hasClass('type') ) return;

				var $this = $(this),
					index = $this.parent().find('td').filter(':gt(0)').index($this);

				$this.siblings('.type').removeClass('highlight');
				$this.closest('table').find('th.fromto').eq(index).removeClass('highlight'); //thead
			})
			.delegate('tfoot td', 'mouseenter', function(){
				var $this = $(this),
					index = $this.parent().find('td').index($this);

				$this.siblings('th').addClass('highlight');
				$this.closest('table').find('th.fromto').eq(index).addClass('highlight'); //thead
			})
			.delegate('tfoot td', 'mouseleave', function(){
				var $this = $(this),
					index = $this.parent().find('td').index($this);

				$this.siblings('th').removeClass('highlight');
				$this.closest('table').find('th.fromto').eq(index).removeClass('highlight'); //thead
			});

	//remove <a.button> active state after click
	//and add the primary class accordingly
		$body.delegate('.button', 'click', function(){
			$(this).blur().addClass('primary').parent().find('.primary').not( $(this) ).removeClass('primary');
		});

	//switch between chart and isotope view
		$('.switch_view').find('a').data('view', 'isotope').click(function(e){
			var $this = $(this),
				paymentSections = ['#container', '#time_frame', '.next_month', '.form_switch', '#calculs'],
				chartSections = ['#chart', '.chart_type'],
				currentView, i;

			$this.data('view', $(this).data('view') == 'isotope' ? 'chart' : 'isotope' )
				 .toggleClass('isotope chart');
			currentView = $this.data('view');

			for( i = 0; i < paymentSections.length; i++ ){
				currentView == 'isotope' ? $(paymentSections[i]).css('display', '') : $(paymentSections[i]).css('display', 'none');
			}

			for( i = 0; i < chartSections.length; i++ ){
				currentView == 'chart' ? $(chartSections[i]).css('display', 'block') : $(chartSections[i]).css('display', 'none');
			}

			if( currentView == 'chart' ){
				$('#chart').height( $body.height() - 200); //force the chart to use all the available viewport height
				reloadChart( null );

			} else if( chart ){ //destroy the chart if present, clean memory and timers
				chart.destroy();
				chart = null;
			}
		});

	//chart type
		$('.chart_type').delegate('a', 'click', function(e){
			e.preventDefault();
			reloadChart( this.rel );
		});

	/**
	 * refresh the payments, forecast and sum parts with ajax return
	 * @params json data: array containing payments, forecasts and sums html code
	 */
	function refreshParts( data ){
		if( data.sums ){
			//need to reorder the months
			var months = [];
			for( var month in data.sums ){
				if( data.sums.hasOwnProperty(month) ){
					months.push(month);
				}
			}
			months.sort();

			$sums.find('div').remove();
			for( var i = 0; i < months.length; i++ ){
				$sums.append( data.sums[ months[i] ].html );
			}
		}

		if( data.forecasts ){
			var title = $forecast.find('h2').detach();
			$forecast.empty().html( data.forecasts.html ).prepend( title );
		}

		if( data.balances ){
			var title = $balance.find('h2').detach();
			$balance.empty().html( data.balances.html ).prepend( title );
		}

		if( data.forecasts || data.balances ){
			$balance.css('width', '');
			$forecast.css('width', '');

			//force the blocks width
			var fixWidth = ( $balance.outerWidth() > $forecast.outerWidth() ? $balance.outerWidth() : $forecast.outerWidth());
			$balance.css('width', fixWidth);
			$forecast.css('width', fixWidth);
		}

		if( data.payments ){
			//prepare jQuery Template
			if( !$('#paymentListTemplate').data('tmpl') ){
				$('#paymentListTemplate').template('paymentList');
			}

			//add the new elements for each month
			//prepare payments for jQuery templating
			var tmp = [];
			for( var month in data.payments ){
				if( data.payments.hasOwnProperty(month) && month.length == 7 ){ //good format
					for( var i = 0; i < data.payments[month].list.length; i++ ){
						tmp.push(data.payments[month].list[i]);
					}
				}
			}
			data.payments = null; //memory cleaning
			data.payments = tmp;

			//get lists from cache if missing
			try {
				if( !data.origins ) data.origins = localStorage.getObject('originList');
				if( !data.recipients ) data.recipients = localStorage.getObject('recipientList');
				if( !data.methods ) data.methods = localStorage.getObject('methodList');
			} catch( e ){
				alert(e);
			}

			//add fixed lists
			data.statuses = { 'data': statuses };
			data.types = { 'data': types };
			data.currenciesWSymbol = { 'data': currenciesWSymbol };

			//today's date
			var now = new Date(),
				m = now.getMonth() + 1,
				d = now.getDate();
			data.now = now.getFullYear() + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);

			//generate the new items via templating
			var $items = $.tmpl('paymentList', data);

			//test if there is any cached filters, which are saved before a forced reload
			try {
				var cachedDates = localStorage.getObject('period_dates');
				var cachedFilters = localStorage.getObject('filters');
				if( cachedFilters ){
					localStorage.removeItem('filters');

					for( var group in cachedFilters ){
						if( cachedFilters.hasOwnProperty(group) ){
							var f = cachedFilters[group],
								$inputs = $filter.find('input').filter('[name='+ group +']').prop('checked', false),
								$div = $inputs.closest('div'),
								$quickUl = $div.find('ul').eq(0);
							//check the cached values filter checkboxes and swap them in the first <ul>
							for( var i = 0; i < f.length; i++ ){
								if( f[i] == '*' ){
									var $checked = $inputs.filter('[id$="-all"]');
								} else {
									var $checked = $inputs.filter('[value="'+ f[i] +'"]');
								}
								$checked.prop('checked', true);

								//swapIn only for "limited" <li>s
								if( $checked.closest('ul').hasClass('limited') ) $checked.closest('li').swapIn( $quickUl );
							}
						}
					}

					filters = cachedFilters; //update the filters list for applyFilters()

					$filter.find('.dropdown').each2(function(i, $dropdown){
						$dropdown.find('input').eq(0).updateFiltersOutputs();
					});

					if( !cachedDates ) applyFilters();
				}

				if( cachedDates ){
					localStorage.removeItem('period_dates');

					$('#date_from').val( cachedDates[0] );
					$('#date_to').val( cachedDates[1] )
						.trigger('keyup'); //will call applyFilters()
				}

				//append the new items to isotope
				$container.isotope('insert', $items);
			} catch( e ){
				alert(e);
			}
		}
	}

	/**
	 * refresh the payments with only the delta from ajax return
	 * @param json data : array containing the payments delta
	 */
	function refreshWithDelta( data ){
		if( data.delta ){
			//prepare jQuery Template
			if( !$('#paymentListTemplate').data('tmpl') ){
				$('#paymentListTemplate').template('paymentList');
			}

			data.payments = data.delta; //for the template

			//get lists from cache if missing
			try {
				if( !data.origins ) data.origins = localStorage.getObject('originList');
				if( !data.recipients ) data.recipients = localStorage.getObject('recipientList');
				if( !data.methods ) data.methods = localStorage.getObject('methodList');
			} catch( e ){
				alert(e);
			}

			//add fixed lists
			data.statuses = { 'data': statuses };
			data.types = { 'data': types };
			data.currenciesWSymbol = { 'data': currenciesWSymbol };

			//today's date
			var now = new Date(),
				m = now.getMonth() + 1,
				d = now.getDate();
			data.now = now.getFullYear() + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d);

			var $items = $.tmpl('paymentList', data);
			var deltaIds = $.map(data.delta, function(payment){ return '#payment_' + payment['id']; }).join(', ');


			//updating the list
			$container
				.isotope('remove', $container.find( deltaIds ))
				.isotope('insert', $items);
		}
	}

	/**
	 * get the changes between to arrays
	 * @param o : old array
	 * @param n : new array
	 * @return object {added, removed}
	 */
	function diff( o, n ){
		var a = [],
			r = [];

		for( var i = 0; i < o.length; i++ ){
			if( $.inArray(o[i], n) == -1 ) r.push(o[i]);
		}
		for( var i = 0; i < n.length; i++ ){
			if( $.inArray(n[i], o) == -1 ) a.push(n[i]);
		}

		return {'added': a, 'removed': r};
	}

	/**
	 * reload the payments, sums and forecasts according to the selected months
	 */
	var timeframeSnapshot = [];
	function reloadParts( needBalance, needForecast ){
		//get the changes between the snapshot and the current timeframe
		var timeframe = $timeframe.find('input').filter(':not(.year):checked').map(function(){ return this.value; }).get();
		if( !timeframe.length ) return; //no month to manage, do nothing by default
		var changes = diff( timeframeSnapshot, timeframe );

		//remove payments and parts for removed month
		if( changes.removed.length ){
			for( var i = 0; i < changes.removed.length; i++ ){
				$container.isotope('remove', $container.find('.item').filter('[data-month='+ changes.removed[i] +']') );
				$sums.find('div').filter('[data-month='+ changes.removed[i] +']').remove();
			}
			if( !changes.added.length ) $container.isotope('reLayout'); //to reLayout only once and only if changes.added is empty
		}

		//no added month so no need to request data from the server
		if( !changes.added.length ){
			timeframeSnapshot = timeframe; //update the snapshot
			return;
		}

		var paymentsTimeframe = {},
			sumsTimeframe = {};
		try {
			for( var i = 0; i < changes.added.length; i++){
				var month = changes.added[i];
				var monthCache = localStorage.getObject( $currentOwner.val() + '_payments_' + month );
				if( monthCache ){
					paymentsTimeframe[month] = new Date(monthCache.lastModified).getTime() / 1000; //transform to unix timestamp (in seconds not milliseconds)
				} else paymentsTimeframe[month] = 0;

				var monthCache = localStorage.getObject( $currentOwner.val() + '_sums_' + month );
				if( monthCache ){
					sumsTimeframe[month] = new Date(monthCache.lastModified).getTime() / 1000; //transform to unix timestamp (in seconds not milliseconds)
				} else sumsTimeframe[month] = 0;
			}

			if( needBalance ){
				var balanceParam = 0;
				var balanceCache = localStorage.getObject( $currentOwner.val() + '_balance' );
				if( balanceCache ){
					balanceParam = new Date(balanceCache.lastModified).getTime() / 1000;
				}
			}

			if( needForecast ){
				var forecastParam = 0;
				var forecastCache = localStorage.getObject( $currentOwner.val() + '_forecast' );
				if( forecastCache ){
					forecastParam = new Date(forecastCache.lastModified).getTime() / 1000;
				}
			}

			var originParam = 0;
			var originCache = localStorage.getObject( 'originList' );
			if( originCache ){
				originParam = new Date(originCache.lastModified).getTime() / 1000;
			}

			var methodParam = 0;
			var methodCache = localStorage.getObject( 'methodList' );
			if( methodCache ){
				methodParam = new Date(methodCache.lastModified).getTime() / 1000;
			}

			var recipientParam = 0;
			var recipientCache = localStorage.getObject( 'recipientList' );
			if( recipientCache ){
				recipientParam = new Date(recipientCache.lastModified).getTime() / 1000;
			}

			var labelParam = 0;
			var labelCache = localStorage.getObject( 'labelList' );
			if( labelCache ){
				labelParam = new Date(labelCache.lastModified).getTime() / 1000;
			}

			var locationParam = 0;
			var locationCache = localStorage.getObject( 'locationList' );
			if( locationCache ){
				locationParam = new Date(locationCache.lastModified).getTime() / 1000;
			}
		} catch( e ){
			alert(e);
		}

		$.ajax('ajax/payment.php', {
			data: 'action=refresh'
				+ '&timeframe=' + $.map(paymentsTimeframe, function(timestamp, month){ return month + '|' + timestamp; }).join(',')
				+ '&sumsTimeframe=' + $.map(sumsTimeframe, function(timestamp, month){ return month + '|' + timestamp; }).join(',')
				+ '&owner=' + $currentOwner.val()
				+ '&tsOrigin=' + originParam
				+ '&tsMethod=' + methodParam
				+ '&tsRecipient=' + recipientParam
				+ '&tsLabel=' + labelParam
				+ '&tsLocation=' + locationParam
				+ ( needBalance ? '&tsBalance=' + balanceParam : '' )
				+ ( needForecast ? '&tsForecast=' + forecastParam : '' ),
			dataType: 'json',
			type: 'post',
			success: function(data, textStatus, jqXHR){
				//when nothing has changed, data is empty and will transform into an array when filled, we need an object for jQuery template
				if( $.isEmptyObject(data) ) data = {};

				//server will send a 304 status if the parts have not changed
				if( jqXHR.status == 200 ){
					store( data );

					//form selects and datalists are empty, happens only on page load
					if( !$('#labelList').find('option').length ){
						$form.find('datalist').loadList();
						$form.find('select').filter('[id]').loadList();
						$filter.find('.dropdown').filter('[id]').loadList();

					//at least one of the filter or form list has changed
					} else {
						if( data.origins ){
							$('#origin_filter, #originList').loadList();
						}
						if( data.recipients ){
							$('#recipient_filter, #recipientList').loadList();
						}
						if( data.methods ){
							$('#methodList').loadList();
						}
						if( data.locations ){
							$('#location_filter, #locationList').loadList();
						}
						if( data.labels ){
							$('#labelList').loadList();
						}
					}

				//form selects and datalists are empty, happens only on page load
				} else if( !$('#labelList').find('option').length ){
					$form.find('datalist').loadList();
					$form.find('select').filter('[id]').loadList();
					$filter.find('.dropdown').filter('[id]').loadList();
				}

				if( needBalance && !data.balance ){ //balance is needed but none returned (no changes)
					try {
						data.balances = localStorage.getObject( $currentOwner.val() + '_balance' );
					} catch( e ){
						alert(e);
					}
				}

				if( needForecast && !data.forecasts ){ //forecast is needed but none returned (no changes)
					try {
						data.forecasts = localStorage.getObject( $currentOwner.val() + '_forecast' );
					} catch( e ){
						alert(e);
					}
				}

				//location and label lists are not needed for payments template
				if( data.locations ) data.locations = null;
				if( data.labels ) data.labels = null;

				//create the objects if missing
				if( !data.payments ) data.payments = {};
				if( !data.sums ) data.sums = {};

				//check payments list availability for each added months and retrieve cached data if missing
				for( var i = 0; i < changes.added.length; i++ ){
					try {
						var month = changes.added[i];
						if( !data.payments[month] ){
							data.payments[month] = localStorage.getObject( $currentOwner.val() + '_payments_' + month );
						}
					} catch( e ){
						alert(e);
					}
				}

				//check sums list availability for each months and retrieve cached data if missing
				//refreshParts() need all the sums because it has to reorder them
				for( var i = 0; i < timeframe.length; i++ ){
					try {
						var month = timeframe[i];
						if( !data.sums[month] ){
							data.sums[month] = localStorage.getObject( $currentOwner.val() + '_sums_' + month );
						}
					} catch( e ){
						alert(e);
					}
				}

				timeframeSnapshot = timeframe; //update the snapshot
				refreshParts( data );
			}
		});
	}

	/**
	 * store the data localy using LocalStorage
	 *
	 */
	function store( data ){
		try {
			if( data.balances ){
				localStorage.setObject($currentOwner.val() + '_balance', data.balances);
			}

			if( data.forecasts ){
				localStorage.setObject($currentOwner.val() + '_forecast', data.forecasts);
			}

			if( data.origins ){
				localStorage.setObject('originList', data.origins);
			}

			if( data.recipients ){
				localStorage.setObject('recipientList', data.recipients);
			}

			if( data.methods ){
				localStorage.setObject('methodList', data.methods);
			}

			if( data.labels ){
				localStorage.setObject('labelList', data.labels);
			}

			if( data.locations ){
				localStorage.setObject('locationList', data.locations);
			}

			if( data.sums ){
				for( var month in data.sums ){
					if( data.sums.hasOwnProperty(month) ){
						localStorage.setObject($currentOwner.val() + '_sums_' + month, data.sums[month]);
					}
				}
			}

			if( data.payments ){
				for( var month in data.payments ){
					if( data.payments.hasOwnProperty(month) ){
						localStorage.setObject($currentOwner.val() + '_payments_' + month, data.payments[month]);
					}
				}
			}
		} catch( e ){
			alert(e);
		}
	}

	/**
	 * apply given filters to isotope
	 */
	function applyFilters(){
		var product = [],
			group;

		for( group in filters ){
			if( filters[group].join(',') != '*' ){
				product.push( filters[group] );
			}
		}

		if( !product.length ){
			$container.isotope({ filter: '*' });
			return;
		}

		//cartesian product
		product = product.reduce(function(previousValue, currentValue, index, array){
			var tmp = [];
			for( var i = 0; i < previousValue.length; i++ ){
				for( var j = 0; j < currentValue.length; j++ ){
					tmp.push(previousValue[i].concat(currentValue[j]));
				}
			}
			return tmp;
		});

		/* use of * as "all" selector can cause error with 2 or more consecutive * */
		$container.isotope({ filter: ($.isArray(product) ? product.join(',') : product).replace(/\*/g, '') });
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

					for( var i = 0; i < this.points.length; i++ ){
						var point = this.points[i];
						s += '<br/><span style="color:' + point.series.color + ';">' + point.series.name +'</span>: '+ Highcharts.numberFormat(point.y, 2, '.', '\'') + ' ' + legendCurrency[point.series.name];
					}

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
		if( type == null ) type = $('.chart_type').find('.primary').attr('rel');

		var cachedData,
			lastModified = 0,
			key = $currentOwner.val() + '_charts_' + type;

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
									for( var i = 0; i < data.sums.length; i++ ){
										data.sums[i].data = $.map(data.sums[i].data, function(s){ return parseFloat(s); });
									}
									expenseOptions.series = data.sums;

									chart = new Highcharts.Chart(expenseOptions);
								break;
							case 'evolution':
									legendCurrency = [];
									evolutionOptions.series = [];

									for( var origin in data.sums ){
										if( data.sums.hasOwnProperty(origin) ){
											evolutionOptions.series.push({
												name: origin,
												pointInterval: 24 * 3600 * 1000,
												pointStart: Date.UTC(2011, 01, 01), //javascript month start at 0
												data: $.map(data.sums[origin].amounts, function(a){ return parseFloat(a); })
											});

											legendCurrency[ origin ] = data.sums[origin].symbol;
										}
									}

									chart = new Highcharts.Chart(evolutionOptions);
								break;
							case 'recipient':
									legendCurrency = data.currencies;

									recipientOptions.xAxis.categories = data.months;

									//json encode transform the sums in string, float are needed
									for( var i = 0; i < data.sums.length; i++ ){
										data.sums[i].data = $.map(data.sums[i].data, function(s){ return parseFloat(s); });
									}
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

	//timeout function for reloadParts();
	function ajaxCalls(){
		if( !delayAjax ){
			if( delayTimeout ) clearTimeout(delayTimeout);

			//try to get the last timeframe updated before a forced reload
			try {
				var persistentTimeframe = localStorage.getObject($currentOwner.val() + '_timeframe');
				if( persistentTimeframe && persistentTimeframe.length ){
					//remove the stored timeframe
					localStorage.removeItem($currentOwner.val() + '_timeframe');

					//uncheck all checkboxes, by default at least one is checked, for the current month
					$timeframe.find('input').filter(':checked').prop('checked', false).closest('li').removeClass('checked');

					//update the timeframe checkboxes
					for( var i = 0; i < persistentTimeframe.length; i++ ){
						$timeframe.find('input').filter('[value='+ persistentTimeframe[i] +']').prop('checked', true)
							.closest('li').addClass('checked')
							.closest('ul').siblings('input[type=checkbox]').prop('checked', true); //check the month and update the year
					}
				}
			} catch( e ){
				alert(e);
			}
			reloadParts(true, true);
		}
		else delayTimeout = setTimeout(function(){ ajaxCalls(); }, 1000);
	}

	//"onload" ajax call for data
	ajaxCalls();
});


/**
 * swap a <li> into the given target
 * respect the <li>s order (from data-order value)
 */
$.fn.swapIn = function( $target ){
	return this.each(function(){
		var $this = $(this).blur(), //remove the ":active" outline
			order = $this.data('order');

		//try to find the previous <li> based on the order
		var $prev = $target.find('li').filter('[data-order='+ (order-1) +']');
		if( $prev.length ){
			$this.insertAfter( $prev );
		} else {
			//try to find the next <li> based on the order
			var $next = $target.find('li').filter('[data-order='+ (order+1) +']');
			if( $next.length ){
				$this.insertBefore( $next );
			} else {
				//parse the $target <li> to find where to insert
				var inserted = false;
				$target.find('li').each2(function(i, $li){
					if( $li.data('order') > order ){
						$this.insertBefore( $li );
						inserted = true;
						return false; //break out of the each loop
					}
				});
				//the <li> is still not inserted, appending it to the target
				if( !inserted ){
					$this.appendTo( $target );
				}
			}
		}
	});
}

/**
 * update the output for filters
 */
$.fn.updateFiltersOutputs = function(){
	return this.each(function(){
		//output the current value
		var output = $(this).closest('div').find('input').filter(':checked').map(function(){ return $(this).parent().text(); }).get();
		var title = '';
		if( output.length > 1 ){
			output = output.join(', ');
		} else {
			output = output[0];
		}

		$(this).closest('section').find('output').text( output );
	});
}

/**
 * data load for <datalist> and <select> options
 * use localStorage to get data
 */
$.fn.loadList = function(){
	return this.each(function(){
		var $list = $(this),
			key = $list[0].id.replace(/_filter/g, 'List'),
			filterClass = key.replace(/List/g, ''),
			decoder = $('<textarea>'),
			cache;

		try {
			cache = localStorage.getObject(key);
			if( cache ){
				var isDatalist = $list.is('datalist');

				if( isDatalist ){
					$list.empty();

					if( !Modernizr.input.list ){
						var fallback = $('<select>'),
							field = $list.closest('form').find('input').filter('[list="'+ $list[0].id +'"]');
						if( field.length ) fallback.name = field[0].name;
						fallback.append('<option value="">');

						$list.append( fallback );
					}

				} else {
					//save the checked values
					$list.data('sav', $list.find('input').filter(':checked').map(function(){ return this.value }));

					//remove any <ul> after the first
					$list.find('ul.limited').remove();

					//keep the first option aka "placeholder" in the remaining <ul>
					$list.find('li').filter(':gt(0)').remove();

					//create a new ul after the first one
					var $ul = $('<ul>', { 'class': 'limited' }).appendTo($list);

					var $li = $list.find('li').eq(0).clone();
				}

				var i = 1;
				for( var id in cache.data ){
					if( cache.data.hasOwnProperty(id) ){
						var name = decoder.html(cache.data[id]).val();
						if( isDatalist ){
							if( !Modernizr.input.list ){
								$('<option>', { "value": name, text: name, 'data-id': id }).appendTo( $list.find('select') );
							} else {
								$('<option>', { "value": name, text: name, 'data-id': id }).appendTo( $list );
							}
						} else {
							var $newLi = $li.clone();
							var $input = $newLi.find('input').val( "." + filterClass + '_' + id ).attr('id', filterClass + '_' + id).prop('checked', false).detach();
							$newLi.find('label')
									.attr('for', filterClass + '_' + id)
									.text( name )
									.prepend( $input );

							$newLi.attr('data-order', i).appendTo( $ul );
							i++;
						}
					}
				}

				//recheck previously checked values
				if( !isDatalist && $list.data('sav') ){
					var sav = $list.data('sav');
					for( var i = 0; i < sav.length; i++ ){
						//[value=*] cause an error
						if( sav[i] == '*' ){
							$list.find('input').filter('[type=checkbox]').eq(0).prop('checked', true);
						} else {
							$list.find('input').filter('[value='+ sav[i] +']').prop('checked', true);
						}
					}
					$list.data('sav', null);
				}
			}
		} catch( e ){
			alert(e);
		}
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
			.find('input, textarea, select').each(function(i, field){
				if( field.type == 'radio' || field.type == 'checkbox' ) field.checked = false;
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
	for( var i = 0; i < data.length; i++ ){
		var error = data[i];
		$('#' + error[0]).addClass(error[2]).siblings('.tip').remove(); //remove previous error message if present

		$('#' + error[0]).parent().append( $('<span>', { 'class': 'tip', 'text': error[1] }) ); //add error message
	}
}

//functions for payment list jquery template
String.prototype.formatDate = function(){
	var tmp = this.substr(0, 10).split('-');
	return tmp[2] + '-' + tmp[1] + '-' + tmp[0];
}

String.prototype.timestamp = function(){
	var d = this.substr(0, 10).split('-'),
		tmp = new Date(d[0], (parseInt(d[1], 10) - 1), d[2]);
	return tmp.getTime() / 1000; //only need the timestamp in seconds
}

String.prototype.getMonth = function(){
	var d = this.substr(0, 10).split('-');
	if( d[2] >= 25 ) tmp = new Date(d[0], d[1], d[2])
	else tmp = new Date(d[0], d[1]+1, d[2]);
	return tmp.getFullYear() + '-' + (tmp.getMonth() + 1);
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
function getValue( object, index ){
	return object.data[index];
}

function getSymbol( object, index ){
	return object.data[index].symbol;
}

