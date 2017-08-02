/* Piggy JS */
/* This file holds all the good sauce for Piggy */
/* Description: JavaScript for the Piggy Web-App */

function isIphone5() {
	function iOSVersion() {
		var agent = window.navigator.userAgent,
		start = agent.indexOf( 'OS ' );
		if( (agent.indexOf( 'iPhone' ) > -1) && start > -1)
			return window.Number( agent.substr( start + 3, 3 ).replace( '_', '.' ) );
		else return 0;
	}
	return iOSVersion() >= 6 && window.devicePixelRatio >= 2 && screen.availHeight==548 ? true : false;
} 

/* Setup Add To Homescreen */
if ( piggyIsAppleDevice() ) {
	var addToHomeConfig = {
		animationIn: 'bubble',
		startDelay: 500,
		lifespan: 1000 * 60 * 24,	// 24 hours!
		touchIcon: true
//		message: piggy_install_message
	};
}

/* When we're on a supported iDevice, we're adding secret sauce */
function piggyIsAppleDevice() {
	// dev:
	//	return true;
	return ( 
		( 
		navigator.platform == 'iPhone Simulator' || 
		navigator.platform == 'iPhone' || 
		navigator.platform == 'iPod' 
		) 
		&& typeof orientation != 'undefined' 
		);
}

function piggySetHeights(){
	var browserHeight = jQuery( window ).height();
	var adjustedBrowserHeight = ( browserHeight - 40 );
	jQuery( 'body' ).css( 'min-height', browserHeight );
	jQuery( '.info-inner' ).css( 'max-height', adjustedBrowserHeight );
}

/* Will do reload of data on new sales */

function piggyNewPurchaseCheck() {
	jQuery( '#refresh' ).addClass( 'spin' );

	var currentTime = new Date();
	var militaryHours = currentTime.getHours();
	var hours = currentTime.getHours();
	var minutes = currentTime.getMinutes();
	if ( hours > 12 ) { 
		hours = hours - 12 
	}
	if ( minutes < 10 ){ 
		minutes = "0" + minutes 
	}
	
	if( militaryHours > 11 ) {
		var meridian = 'pm';
	} else {
		var meridian = 'am';
	}
	var updatedTime = hours + ':' + minutes + ' ' + meridian;

	jQuery.get( piggyWordPressURL + '/?piggy_purchase_hash=1', function( response, status, xhr ) {
		if ( status == 'error' ) {
			if ( typeof console === 'undefined' || typeof console.log === 'undefined' ) {
				console.log( xhr.status + ' ' + xhr.statusText );
			}
		}
		
		if ( response != purchaseHash ) {
			console.log( 'New purchase(s) found, clearing purchase check interval, refreshing piggy.' );
			piggyDoRefresh();
			purchaseHash = response;			
		} else {
			setTimeout(function(){ 
				jQuery( '#refresh' ).removeClass( 'spin' );
				jQuery( '.last-updated' ).html( piggy_last_checked + ' ' + updatedTime );
				if ( typeof console === 'undefined' || typeof console.log === 'undefined' ) {
					console.log( 'No new purchases.' );
				}
			}, 2000);
		}
	});
}

var purchaseChecker = '';
function piggyStartInterval() {
	if ( purchaseChecker == '' ) {
		purchaseChecker = window.setInterval( 'piggyNewPurchaseCheck()', 1000 * 20 );  // check for new purchases every 20 seconds
		if ( typeof console === 'undefined' || typeof console.log === 'undefined' ) {
			console.log( 'Puchase check interval set (20 seconds).' );
			}
	} else {
		if ( typeof console === 'undefined' || typeof console.log === 'undefined' ) {
			console.log( 'Puchase check interval already started.' );
		}
	}
}

function piggyDoRefresh() {
	jQuery( '#refresh' ).addClass( 'active' );
	window.location.reload();
//	jQuery( 'body' ).load( piggyAjaxUrl + 'body > *', function( response, status, xhr ) {
//		if ( status == "error" ) {
//			alert( xhr.status + " " + xhr.statusText );	
//		} else {
//			console.log( 'Manual ajax content reload complete.' );
//		}
//
//		doPiggyReady();		
//	});
}

function piggyLoadFastClick(){
	jQuery( function() {
    	FastClick.attach( document.body );
	});
}

function piggyLoadOffCanvas(){
	var offMenuLeft = jQuery.offCanvasMenu({
	  direction : 'left',
	  coverage  : '260px',
	  trigger   : '#info-trigger',
	  menu      : '#info-pane',
	  duration  : 300,
	  container : 'body'
	});

	// Fire!
	offMenuLeft.on();
}

function piggyLoadSlideView(){
	var slideView = jQuery( '#current-pane' ).simpleSlideView({ 
		duration: 300, 
		use3D: true,
		deferHeightChange: true,
		scrollOnStart: false, 
		scrollToContainerTop: false,
		maintainViewportHeight: true 
	});
	jQuery( '.view h1' ).each( function(){
		jQuery( this ).on( 'click', function(){
			slideView.popView( '#current' );
		});
	});

	jQuery( '#current .main-list li' ).each( function(){
		var targetView = jQuery( this ).attr( 'rel' );
		jQuery( this ).on( 'click', function(){
			slideView.pushView( targetView );
		});
	});
}

function piggyBindSelected(){
	jQuery( '#current ul.main-list li, .info-links li' ).tappable({
		touchDelay: 100 // 100ms
	});
}

function piggyDisableTouchMove(){
	if ( piggyIsAppleDevice() ) {
		/* No touchmove, please */
		jQuery( '#home > .toolbar' ).each( function(){
			jQuery( this ).on( 'touchmove', function( e ){ e.preventDefault(); } );
		});
	}
}

/*  On Document Ready */	
function doPiggyReady() {

	piggyLoadOffCanvas();
	piggyLoadSlideView();
	piggySetHeights();
	piggyLoadFastClick();
	piggyBindSelected();
	piggyDisableTouchMove();
	piggyStartInterval();

	/*  Refresh button code */
	jQuery( '#refresh' ).on( 'click', function(){
		jQuery( this ).addClass( 'spin' );
		jQuery( document ).off();
		setTimeout( function(){ 
			piggyDoRefresh();
		}, 500 );		
	});

	/* External Links */
	jQuery( 'body' ).on( 'click', 'a[target="_blank"]', function() {
	    if ( confirm( piggy_external_link_msg ) ) {
	        return true;
	    } else {
			return false;
	    }
	});
	
	/* Logout Link */
	jQuery( 'body' ).on( 'click', 'a.logout', function() {
		jQuery.cookie( 'piggy_data', '', { path: '/', expires: -1 } );
		jQuery.cookie( 'piggy_hash', '', { path: '/', expires: -1 } );
		setTimeout(function(){ 
			window.location.reload();
		}, 220);		
		return false;
	});

	jQuery( '#passcode' ).on( 'keyup', 'input', function() {		
		var inputField = jQuery( this );
		var passcodeIcon = jQuery( '#passcode i' );
		var passcodeText = jQuery( 'p.formtext' );
		if ( inputField.val().length == passKeyNumber ) {
			var passkey = inputField.val();

			inputField.blur();

			var currentTimeout = 1;
			jQuery.post( piggyAjaxUrl, { piggyPassKey: passkey }, function( response ) {
				var jsonResponse = eval('(' + response + ')');
				if ( jsonResponse.result == 'pass' ) {
					//pass			
					
					if ( requirePasscode ) {
						// Use session cookies only
						jQuery.cookie( 'piggy_data', jsonResponse.ip );
						jQuery.cookie( 'piggy_hash', jsonResponse.hash );		
					} else {
						// Use permanent cookies
						jQuery.cookie( 'piggy_data', jsonResponse.ip, { path: '/', expires: 365 } );
						jQuery.cookie( 'piggy_hash', jsonResponse.hash, { path: '/', expires: 365 } );						
					}
					passcodeIcon.addClass( 'icon-unlock green' );
					passcodeText.text( piggy_right_code );
					inputField.addClass( 'correct' );
					
					setTimeout(function(){ jQuery( '#passcode' ).toggleClass( 'ok' ); }, 400);					
					setTimeout(function(){ window.location.reload(); }, 600);					
				} else {
					// slight delay to make password guessing impractical
					setTimeout( function() {
						// failure
						inputField.addClass( 'incorrect animated shake' );
						passcodeText.text( piggy_wrong_code );
						passcodeIcon.addClass( 'red' );
						setTimeout(function(){ 
							inputField.removeClass().val( '' );
							passcodeIcon.removeClass( 'red' );
							passcodeText.text( piggy_enter_code );
						}, 1250 );
						
						// Remove cookies for good measure
						jQuery.cookie( 'piggy_data', '', { path: '/', expires: -1 } );
						jQuery.cookie( 'piggy_hash', '', { path: '/', expires: -1 } );
						
						currentTimeout = currentTimeout + 1;
					},
					currentTimeout * 300
					);
				}
			});
		}
	});
	
	/* Header Filter Tabs */	
	var tabContainers = jQuery( 'div.tab-wrap' );	
    jQuery( '.filterbar li' ).on( 'click', function( e ) {
    	var thisID = jQuery( this ).attr( 'id' );
    	var thisRel = jQuery( this ).attr( 'rel' );

        tabContainers.hide().filter( thisRel ).show();
    	
    	jQuery( '.filterbar' ).find( 'li' ).removeClass( 'selected' );
   		jQuery( this ).addClass( 'selected' );
    	jQuery.cookie( 'piggy_tab_id', thisID, { path: '/', expires: 365} );

    });	
	
	/* Active tab cookie */
	var currentTab = jQuery.cookie( 'piggy_tab_id' );
	if ( currentTab && currentTab.length ) {
		jQuery( '#' + currentTab ).trigger( 'click' );
	} else {
		jQuery( '.filterbar li:first' ).trigger( 'click' );
	}
	
	jQuery( '.sub-view h1' ).each( function(){
		jQuery( this ).prepend( '<i class="icon-angle-left"></i>' );
	});
} 
/* End Document Ready */

jQuery( document ).ready( function() { doPiggyReady(); });