<?php global $piggy; $current_scheme = get_user_option('admin_color'); $settings = piggy_get_settings(); ?>

<form method="post" action="" id="bnc-form" class="<?php if ( $piggy->locale ) echo 'locale-' . strtolower( $piggy->locale ); ?>">
	<div id="bnc" class="<?php echo $current_scheme; ?> normal wrap">

		<div id="piggy-admin-top">
			<h2>
				<?php echo PIGGY_PRODUCT_NAME . ' <span class="version">' . PIGGY_VERSION; ?></span>
			</h2>
			<div id="piggy-api-server-check"></div>
		</div>		
			
		<div id="piggy-admin-form">		
			<ul id="piggy-top-menu">
			
				<?php do_action( 'piggy_pre_menu' ); ?>
				
				<?php $pane = 1; ?>
				<?php foreach( $piggy->tabs as $name => $value ) { ?>
					<li><a id="pane-<?php echo $pane; ?>" class="pane-<?php echo piggy_string_to_class( $name ); ?>" href="#"><?php echo $name; ?></a></li>
					<?php $pane++; ?>
				<?php } ?>
		
				<?php do_action( 'piggy_post_menu' ); ?>
				
				<li>
					<div class="piggy-ajax-results blue-text" id="ajax-loading" style="display:none"><?php _e( "Loading...", "piggy" ); ?></div>
					<div class="piggy-ajax-results blue-text" id="ajax-saving" style="display:none"><?php _e( "Saving...", "piggy" ); ?></div>
					<div class="piggy-ajax-results green-text" id="ajax-saved" style="display:none"><?php _e( "Done", "piggy" ); ?></div>
					<div class="piggy-ajax-results red-text" id="ajax-fail" style="display:none"><?php _e( "Oops! Try saving again.", "piggy" ); ?></div>
					<br class="clearer" />
				</li>
			</ul>
					
			<div id="piggy-tabbed-area"  class="round-3">
				<?php piggy_show_tab_settings(); ?>
			</div>
			
			<br class="clearer" />
			
			<input type="hidden" name="piggy-admin-tab" id="piggy-admin-tab" value="" />
			<input type="hidden" name="piggy-admin-menu" id="piggy-admin-menu" value="" />
		</div>
		<input type="hidden" name="piggy-admin-nonce" value="<?php echo wp_create_nonce( 'piggy-post-nonce' ); ?>" />

		<p class="submit" id="bnc-submit">
			<input class="button-primary" type="submit" name="piggy-submit" tabindex="1" value="<?php _e( "Save Changes", "piggy" ); ?>" />
		</p>
		
		<p class="submit" id="bnc-submit-reset">
			<input class="button" type="submit" id="piggy-submit-reset" name="piggy-submit-reset" tabindex="2" value="<?php _e( "Reset Settings", "piggy" ); ?>" />
		</p>
		
		<ul id="link-menu">
			<li><a href="http://wordpress.org/support/plugin/piggy-lite"><?php _e( "WordPress Support Forums", "piggy" ); ?></a> |</li>
			<li><a href="http://twitter.com/bravenewcode"><?php echo sprintf( __( "%s on Twitter", "piggy" ), "BraveNewCode" ); ?></a> |</li>
			<li><a href="http://www.facebook.com/bravenewcode"><?php echo sprintf( __( "%s on Facebook", "piggy" ), "BraveNewCode" ); ?></a></li>
		</ul>


</div> <!-- piggy-admin-area -->
</form>