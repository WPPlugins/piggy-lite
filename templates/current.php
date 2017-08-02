<div class="view" id="current">
	<ul class="main-list">
		<?php piggy_populate_overview_data(); ?>
		<?php while ( piggy_has_data() ) { ?>
			<?php piggy_the_data(); ?>
			<li class="<?php piggy_the_data_class_name(); ?>" id="<?php piggy_the_data_class_name(); ?>" rel="#<?php piggy_the_data_class_name(); ?>-view">
				<span class="stat-total">
					<?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?> <i class="toggle icon-angle-right"></i>
				</span>
				<span class="stat-title"><?php piggy_the_data_title(); ?></span>
				<span class="stat-info">
					<?php echo sprintf( __('%s sales / %s%0.2f', 'piggy' ), number_format( piggy_get_data_sales() ), piggy_get_currency_symbol(), (float)piggy_get_data_average_price() ); ?>
				</span>
			</li>
		<?php } ?>
	</ul>
</div>

<!-- Today Sub View -->
<div class="view sub-view" id="today-view">
	<h1><?php _e( 'Sales Today', 'piggy' ); ?></h1>
	<?php piggy_populate_this_day_data(); ?>	
    <ul class="main-list">
		<?php if ( piggy_has_data() ) { ?>		
        	<?php while ( piggy_has_data() ) { ?>
				<?php piggy_the_data(); ?>
				<?php if ( piggy_get_sales_count() > 1 ) { ?>
					<li>
						<span class="stat-title">
							<?php echo (piggy_get_today_data_count() - piggy_get_data_count() + 1); ?>.) 
							<?php echo date( 'g:i a', piggy_get_data_date() ); ?> 
							(<?php echo sprintf( _n( '%d', '%d', piggy_get_sales_count(), 'piggy' ), piggy_get_sales_count() ); ?>)
						</span>
						
						<span class="stat-info">
						<?php while ( piggy_data_while_has_sales_data() ) { ?>
							<?php piggy_data_the_sales_data(); ?>
							<?php echo piggy_data_get_sales_data_name(); ?> (<?php echo piggy_data_get_sales_data_quantity(); ?>)
						<?php } ?>
						</span>
						<span class="stat-total">
							<?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_total_sale_price(), 2 ); ?>
						</span>
					</li>
				<?php } else { ?>
					<li>
						<span class="stat-title">
							<?php echo (piggy_get_today_data_count() - piggy_get_data_count() + 1); ?>.) <?php echo date( 'g:i a', piggy_get_data_date() ); ?>
						</span>
						<?php while ( piggy_data_while_has_sales_data() ) { ?>
							<?php piggy_data_the_sales_data(); ?>
							<span class="stat-info"><?php echo piggy_data_get_sales_data_name(); ?></span>
						<?php } ?>
						<span class="stat-total"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_total_sale_price(), 2 ); ?>
					</li>
				<?php } ?>
			<?php } ?>
		<?php } else { ?>
			<li class="center"><?php _e( 'No sales yet today.', 'piggy' ); ?></li>
		<?php } ?>
	</ul>
</div>
		
<!-- This Week Sub View -->
<div class="view sub-view" id="this-week-view">
	<h1><?php _e( 'Sales This Week', 'piggy' ); ?></h1>
	<?php piggy_populate_this_week_data(); ?>
    <ul class="main-list">
	<?php if ( piggy_has_data() ) { ?>
		<?php while ( piggy_has_data() ) { ?>
			<?php piggy_the_data(); ?>
			<li>
				<span class="stat-title"><?php piggy_the_data_title(); ?></span>
				<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
				<span class="stat-total"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
			</li>
		<?php } ?>
    <?php } else { ?>
    	<li class="center"><?php _e( 'No sales this week.', 'piggy' ); ?></li>
    <?php } ?>
    </ul>
</div>
		
<!-- This Month View -->
<div class="view sub-view" id="this-month-view">
	<h1><?php _e( 'Sales This Month', 'piggy' ); ?></h1>
	<?php piggy_populate_this_month_data(); ?>
    <ul class="main-list">
	<?php while ( piggy_has_data() ) { ?>
		<?php piggy_the_data(); ?>
		<li>
			<span class="stat-title"><?php piggy_the_data_title(); ?></span>
			<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
			<span class="stat-total"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
		</li>
	<?php } ?>
    </ul>
</div>
		
<!-- This Year Sub View -->
<div class="view sub-view" id="this-year-view">
	<h1><?php _e( 'Sales This Year', 'piggy' ); ?></h1>
	<?php piggy_populate_this_year_data(); ?>
	<ul class="main-list">
	<?php while ( piggy_has_data() ) { ?>
		<?php piggy_the_data(); ?>
		<li>
			<span class="stat-title"><?php piggy_the_data_title(); ?></span>
			<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
			<span class="stat-total"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
		</li>
	<?php } ?>
    </ul>
</div>
		    
<!-- All Time Sub View -->
<div class="view sub-view" id="all-time-view">
	<h1><?php _e( 'All Time Sales', 'piggy' ); ?></h1>
	<?php piggy_populate_this_all_time_data(); ?>
	<ul class="main-list">
	<?php while ( piggy_has_data() ) { ?>
		<?php piggy_the_data(); ?>
		<li>
			<span class="stat-title"><?php piggy_the_data_title(); ?></span>
			<span class="stat-info"><?php piggy_the_data_sales(); ?> sales</span>
			<span class="stat-total"><?php piggy_the_currency_symbol(); ?><?php echo number_format( piggy_get_data_value(), 2 ); ?></span>
		</li>
	<?php } ?>
	</ul>
</div>