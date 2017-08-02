<div id="info-pane">
	<div class="info-inner">
		<h1><?php _e( 'Piggy', "piggy" ); ?> <?php echo PIGGY_VERSION; ?></h1>
			<?php _e( 'A web app for viewing real-time sales data from WordPress E-Commerce plugins.', "piggy" ); ?><br />
			<?php _e( 'By BraveNewCode Inc.', "piggy" ); ?><br /><br />

		<ul class="info-links">
			<li><a href="http://www.bravenewcode.com" target="_blank"><?php _e( 'bravenewcode.com', "piggy" ); ?></a></li>
			<li><a href="http://wordpress.org/support/plugin/piggy-lite" target="_blank"><?php _e( 'WordPress support forums', "piggy" ); ?></a></li>
			<li><?php if ( piggy_is_user_logged_in() ) { ?><a href="#" class="logout"><?php _e( 'Piggy log out', "piggy" ); ?></a><?php } ?></li>
		</ul>
		<br />
		<h1><?php _e( 'FAQ', "piggy" ); ?></h1>
	    <ul class="faq-list">
			<li>
				<strong>What do the thumbs mean in projections?</strong>
				'Thumbs up' means the total is higher than the previous day/week/month etc. 'Thumbs down' means the projected total is down.
			</li>
			<li>
				<strong>I don't think Piggy's data is correct— what can I do to fix it?</strong>
				First, check your sales logs and verify that Piggy is reporting incorrect sales stats. If you've checked and verified that Piggy's numbers are incorrect, please report the problems to the Piggy support forums.
			</li>
			<li>
			<strong>How do I know that Piggy's data is up to date? Do I have to manually refresh it?</strong>
			Piggy loads the most current sales data available when it starts. As you view Piggy, it periodically polls your server efficiently to determine if new purchases have been made. If new sales are found, it will fetch the new sales data and load it automatically for you. You can also tap the refresh icon in the upper right corner of the main view to refresh the data. The time Piggy was last updated or checked for updates is always shown in the footer.
			</li>
			<li>
				<strong>How can I set up a custom icon and startup image?</strong>
				At this time Piggy cannot be configured for custom icons or startup images.
			</li>
			<li>
				<strong>How did you make this very cool web app?</strong>
				Our team is well versed in mobile— we're the people behind <a href="http://www.bravenewcode.com/wptouch/">WPtouch</a>. We used a sprinkle of cool Javascript, mixed in some CSS3 and HTML5 with mind-busting math and out popped Piggy!
			</li>
		</ul>
	</div>
</div>