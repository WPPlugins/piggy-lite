<ul class="main-list">
	<h1 class="this-month"><?php _e( 'Best This Month', 'piggy' ); ?></h1>
	<?php piggy_populate_this_product_data_month(); ?>
	<?php while ( piggy_has_data() ) { ?>
		<?php piggy_the_data(); ?>
		<li>
			<span class="stat-title"><?php piggy_the_data_count(); ?>.) <?php piggy_the_data_title(); ?></span>
			<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
			<span class="stat-total this-month"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
		</li>
	<?php } ?>
</ul>
	
<ul class="main-list">
	<h1 class="this-year"><?php _e( 'Best This Year', 'piggy' ); ?></h1>
	<?php piggy_populate_this_product_data_year(); ?>
	<?php while ( piggy_has_data() ) { ?>
	<?php piggy_the_data(); ?>
		<li>
			<span class="stat-title"><?php piggy_the_data_count(); ?>.) <?php piggy_the_data_title(); ?></span>
			<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
			<span class="stat-total this-year"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
		</li>
	<?php } ?>
</ul>
	    
<ul class="main-list">
	<h1 class="all-time"><?php _e( 'Best All Time', 'piggy' ); ?></h1>
	<?php piggy_populate_this_product_all_time(); ?>
	<?php while ( piggy_has_data() ) { ?>
	<?php piggy_the_data(); ?>
		<li>
			<span class="stat-title"><?php piggy_the_data_count(); ?>.) <?php piggy_the_data_title(); ?></span>
			<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
			<span class="stat-total all-time"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
		</li>
	<?php } ?>
</ul>

<?php if ( false ) { ?>
	<?php piggy_populate_overview_product_data(); ?>
	<?php if ( piggy_has_data() ) { ?>
		<p class="list-title"><?php _e( 'Best Sellers', "piggy" ); ?></p>
		<ul class="main-list">
			<?php while ( piggy_has_data() ) { ?>
				<?php piggy_the_data(); ?>	
				<li>
					<a href="#<?php piggy_the_data_class_name(); ?>">
						<?php piggy_the_data_title(); ?>
						<span class="stat-info"><?php piggy_the_data_product_name(); ?></span>
						<span class="stat-total"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
					</a>
				</li>									
			<?php } ?>
		</ul>
	<?php } ?>
<?php } ?>
