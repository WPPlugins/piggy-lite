<!doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo PIGGY_PRODUCT_NAME; ?></title>
	<?php do_action( 'piggy_head' ); ?>
</head>
<?php flush(); ?>
<body id="piggy" class="<?php piggy_the_body_classes(); ?>">

	<?php if ( piggy_is_supported_device() ) { ?>

		<?php if ( !piggy_is_webapp_mode() ) { ?>
			<div id='web-app-notice'>&nbsp;</div>
		<?php } else { ?>

			<?php if ( piggy_is_user_logged_in() && piggy_can_view_stats() ) { ?>
				<div id="home">
					<div class="toolbar">
						<i id="info-trigger" class="icon-reorder"></i>
						<h1><?php bloginfo( 'title' ); ?></h1>
						<i id="refresh" class="icon-refresh"></i>
					</div>
					<div class="filterbar">
						<ul>
							<li id="current-pane-filter" class="selected" rel="#current-pane"><span><?php _e( 'Current', 'piggy' ); ?></span></li>
							<li id="projected-pane-filter" rel="#projected-pane"><span><?php _e( 'Projected', 'piggy' ); ?></span></li>
							<li id="bestsellers-pane-filter" rel="#bestsellers-pane"><span><?php _e( 'Best Sellers', 'piggy' ); ?></span></li>
						</ul>
					</div>
					<div class="tab-wrap" id="current-pane">
						<?php  require_once( PIGGY_DIR . '/templates/current.php' ); ?>
					</div>

					<div class="tab-wrap" id="projected-pane">
						<?php require_once( PIGGY_DIR . '/templates/projected.php' ); ?>
					</div>

					<div class="tab-wrap" id="bestsellers-pane">
						<?php require_once( PIGGY_DIR . '/templates/best-sellers.php' ); ?>
					</div>
				</div><!-- home -->

				<div class="last-updated">
					<p><?php _e( 'Updated', "piggy" ); ?>: <?php echo date( get_option( 'time_format' ) ); ?></p>
				</div>

			<?php } else {  // if user not logged in ?>
				<?php include_once( 'passcode.php' ); ?>
			<?php } ?>

			<?php include_once( 'info-pane.php' ); ?>

		<?php } // is piggy webapp ?>

	<?php } else { ?>
		<div id="web-app-device-notice">
			<?php echo piggy_unsupported_message(); ?>
		</div>
	<?php } ?>

	<?php do_action( 'piggy_footer' ); ?>

</body>
</html>