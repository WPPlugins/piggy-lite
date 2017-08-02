
function piggyDoReady() {
	piggySetupTabs();
	piggySetupTooltips();
	piggySetupAdminPanel();
}

function piggySetupTabs() {
	// Top menu tabs
	jQuery( '#piggy-top-menu li a' ).unbind( 'click' ).click( function( e ) {
		var tabId = jQuery( this ).attr( 'id' );
		
		jQuery.cookie( 'piggy-tab', tabId );
		
		jQuery( '.pane-content' ).hide();
		jQuery( '#pane-content-' + tabId ).show();
		
		jQuery( '#pane-content-' + tabId + ' .left-area li a:first' ).click();
		
		jQuery( '#piggy-top-menu li a' ).removeClass( 'active' );
		jQuery( '#piggy-top-menu li a' ).removeClass( 'round-top-6' );
		
		jQuery( this ).addClass( 'active' );
		jQuery( this ).addClass( 'round-top-6' );

		e.preventDefault();
	});

	// Left menu tabs
	jQuery( '#piggy-admin-form .left-area li a' ).unbind( 'click' ).click( function( e ) {
		var relAttr = jQuery( this ).attr( 'rel' );
		
		jQuery.cookie( 'piggy-list', relAttr );

		jQuery( '.setting-right-section' ).hide();
		jQuery( '#setting-' + relAttr ).show();
		
		jQuery( '#piggy-admin-form .left-area li a' ).removeClass( 'active' );
		
		jQuery( this ).addClass( 'active' );
		
		e.preventDefault();
	});
	
	// Cookie saving for tabs
	var tabCookie = jQuery.cookie( 'piggy-tab' );
	if ( tabCookie ) {
		var tabLink = jQuery( "#piggy-top-menu li a[id='" + tabCookie + "']" ); 
		jQuery( '.pane-content' ).hide();
		jQuery( '#pane-content-' + tabCookie ).show();	
		tabLink.addClass( 'active' );
		tabLink.addClass( 'round-top-6' );
		
		var listCookie = jQuery.cookie( 'piggy-list' );
		if ( listCookie ) {
			var menuLink = jQuery( "#piggy-admin-form .left-area li a[rel='" + listCookie + "']");
			jQuery( '.setting-right-section' ).hide();
			jQuery( '#setting-' + listCookie ).show();	
			jQuery( '#piggy-admin-form .left-area li a' ).removeClass( 'active' );	
			menuLink.click();			
		} else {
			jQuery( '#piggy-admin-form .left-area li a:first' ).click();
		}
	} else {
		jQuery( '#piggy-top-menu li a:first' ).click();
	}	
}

function piggySetupTooltips() {
	var tipDivs = '<div id="tiptip_holder"><div id="tiptip_content"><div id="tiptip_arrow"><div id="tiptip_arrow_inner"></div></div></div></div>';
	jQuery( 'body' ).append( tipDivs );

	// Admin Tooltips
	jQuery( '.piggy-tooltip' ).tipTip( { defaultPosition: 'right', maxWidth: '100px' } );
}

function piggyCheckForInt( evt ) {
	var charCode = ( evt.which ) ? evt.which : event.keyCode;
	return ( charCode >= 48 && charCode <= 57 );
}

function piggySetupAdminPanel() {
	
	jQuery( 'input.numeric' ).keypress( function( event ) { return piggyCheckForInt( jQuery( event ).get(0) ); } );
	
	jQuery( '#notification_service' ).live( 'change', function( e ) { 
		var currentValue = jQuery( '#notification_service' ).val();
		
		if ( currentValue == 'prowl' ) {
			jQuery( '#setting_prowl-section' ).show();
			jQuery( '#setting_howl-section' ).hide();
			jQuery( '#setting_notification_section' ).show();
		} else if ( currentValue == 'howl' ) {
			jQuery( '#setting_prowl-section' ).hide();
			jQuery( '#setting_howl-section' ).show();
			jQuery( '#setting_notification_section' ).show();
		} else {
			jQuery( '#setting_prowl-section' ).hide();
			jQuery( '#setting_howl-section' ).hide();		
			jQuery( '#setting_notification_section' ).hide();	
		}
	});
	
	jQuery( '#notification_service' ).change();
	
	jQuery( 'a.remove-prowl' ).live( 'click', function( e ) { 
		var currentItems = jQuery( 'input.prowl' );
		if ( currentItems.length > 1 ) {
			jQuery( this ).parent().remove();
		} else {
			jQuery( this ).parent().find( 'input' ).attr( 'value', '' );	
		}
		
		e.preventDefault();	
	});
	
	jQuery( 'a.add-prowl' ).live( 'click', function( e ) {
		var currentItems = jQuery( 'input.prowl' );
		if ( currentItems ) {
			var nextItem = currentItems.length + 1;
			
			jQuery( '#add-prowl-setting-area' ).append( '<div class="prowl-setting"><input type="text" autocomplete="off" class="text prowl" id="prowl_api_keys_' + nextItem + '" name="prowl_api_keys_' + nextItem + '" value="" /> <label for="prowl_api_keys_' + nextItem + '">' + PiggyCustom.prowl_api_text + '</label> <a href="#" class="add-prowl">+</a> <a href="#" class="remove-prowl">-</a><br /></div>' );
		}
			
		e.preventDefault();
	});
	
	jQuery( 'a.remove-howl' ).live( 'click', function( e ) { 
		var currentItems = jQuery( 'input.howl' );
		if ( currentItems.length > 1 ) {
			jQuery( this ).parent().remove();
		} else {
			jQuery( this ).parent().find( 'input' ).attr( 'value', '' );	
		}
		
		e.preventDefault();	
	});
	
	jQuery( 'a.add-howl' ).live( 'click', function( e ) {
		var currentItems = jQuery( 'input.howl' );
		if ( currentItems ) {
			var nextItem = currentItems.length + 1;
			
			jQuery( '#add-howl-setting-area' ).append( '<div class="howl-setting"><input type="text" autocomplete="off" class="text howl-user" id="howl_username_' + nextItem + '" name="howl_username_' + nextItem + '" value="" /> <label class="text" for="howl_username_' + nextItem + '">Username</label> <input type="password" autocomplete="off" class="text howl" id="howl_password_" name="howl_password_' + nextItem + '" value="" /> <label class="text" for="howl_password_' + nextItem + '">Password</label> <a href="#" class="add-howl">+</a> <a href="#" class="remove-howl">-</a><br /></div>' );
		}
			
		e.preventDefault();
	});	
	
	/* Reset confirmation */
	jQuery( '#piggy-submit-reset' ).click( function() {
		var answer = confirm( PiggyCustom.reset_settings_message );
		if ( answer ) {
			jQuery.cookie( 'piggy-tab', '' );
			jQuery.cookie( 'piggy-list', '' );
		} else {
			return false;	
		}
	});
	
	jQuery( 'select#passcode_length' ).change( function() {
		var result = jQuery( 'select#passcode_length' ).val();
		if ( result ) {
			jQuery( '#passcode' ).attr( 'maxlength', result );
			
			var passcode = jQuery( '#passcode' ).val();
			if ( passcode.length > result ) {
				jQuery( '#passcode' ).val( passcode.substring( 1, passcode.length ) );	
			}
		}
	});
	
	jQuery( 'select#passcode_length' ).change();
	
}

jQuery( document ).ready( function() { piggyDoReady(); } );