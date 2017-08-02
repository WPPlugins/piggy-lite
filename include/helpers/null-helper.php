<?php

require_once( 'base-helper.php' );

class NullCommerceHelper extends BaseCommerceHelper {
	var $commerce_type;
	
	function NullCommerceHelper() {
		parent::BaseCommerceHelper( 'null' );	
	}
	
	function get_sales_between_dates( $start_date, $end_date, $name = false ) {
		return array();
	}
	
	function get_summary_between_dates( $start_date, $end_date ) {
		return array();
	}
	
	function get_product_summary_between_dates( $start_date, $end_date, $name = false ) {
		return array();	
	}

	function get_last_purchase_hash() {
		return 0;
	}	
	
	function is_detected() {
		return true;
	}
}



