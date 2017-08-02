<?php

require_once( 'base-helper.php' );

class WPECHelper extends BaseCommerceHelper {
	function WPECHelper() {
		parent::BaseCommerceHelper( 'WPEC' );
	}
	
	function is_detected() {
		global $wpdb;
		
		$result = $wpdb->get_results( 'SHOW TABLES LIKE "' . $wpdb->prefix . 'wpsc_purchase_logs"' );
		if ( $result ) {
			if ( is_array( $result ) && count( $result ) ) {
				return true;
			}
		}
		
		return false;
	}	

	// This is used for the "Current" summary sections, near the top		
	function get_summary_between_dates( $start_date, $end_date ) {
		global $wpdb;

		$actual_start_date = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE date >= ' . $start_date . ' ORDER BY date ASC LIMIT 1' );
		if ( $actual_start_date ) {
			$start_date = $actual_start_date->date;
		}
	
		$actual_end_date = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE date <= ' . $end_date . ' ORDER BY date DESC LIMIT 1' );
		if ( $actual_end_date ) {
			$end_date = $actual_end_date->date;
		}
					
		$purchase_info = new stdClass;
		
		$sales = $wpdb->get_row( 'SELECT count(*) AS count,SUM(totalprice) AS total FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE processed IN (2,3,4) AND date >= ' . $start_date . ' AND date <=' . $end_date );
		
		if ( $sales ) {
			$purchase_info->total = $sales->total;
			$purchase_info->count = $sales->count;
			
			if ( $purchase_info->count ) {
				$total_days = ( $end_date - $start_date ) / 86400;	
				if ( $total_days ) {
					$purchase_info->amount_per_day = $purchase_info->total / $total_days;
				}
			}
		} else {
			$purchase_info->total = 0;
			$purchase_info->count = 0;	
		}
		
		return $purchase_info;		
	}		
	
	// This is used to popular the 'Today' information area		
	function get_sales_between_dates( $start_date, $end_date, $name = false ) {
		global $wpdb;

		$actual_start_date = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE date >= ' . $start_date . ' ORDER BY date ASC LIMIT 1' );
		if ( $actual_start_date ) {
			$start_date = $actual_start_date->date;
		}
	
		$actual_end_date = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE date <= ' . $end_date . ' ORDER BY date DESC LIMIT 1' );
		if ( $actual_end_date ) {
			$end_date = $actual_end_date->date;
		}
					
		$purchase_info = array();
	
		
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE processed IN (2,3,4) AND date >= ' . $start_date . ' AND date <= ' . $end_date . ' ORDER BY date DESC';
		
		$sales = $wpdb->get_results( $sql );

		if ( $sales ) {
			foreach( $sales as $sale ) {			
				$info = new stdClass;
			
				$info->date = $sale->date;
				$info->total_price = 0;				
				$info->id = $sale->id;
				$info->sales = array();
	
				$sql = 'SELECT * FROM ' . $wpdb->prefix . 'wpsc_cart_contents WHERE purchaseid = ' . $sale->id;
				$these_sales = $wpdb->get_results( $sql );
				
				if ( $these_sales ) {
					foreach( $these_sales as $this_sale ) {
						$one_sale = new stdClass;
						$one_sale->product = $this_sale->name;
						$one_sale->value = $this_sale->price;
						$one_sale->quantity = $this_sale->quantity;
						$info->total_price = $info->total_price + $one_sale->value;
						
						$info->sales[] = $one_sale;
					}
				}
				
				$purchase_info[] = $info;
			}
		} 

		return $purchase_info;		
	}	

	// This is used to populate the 'Best Sellers' area
	function get_product_summary_between_dates( $start_date, $end_date, $name = false ) {
		global $wpdb;
		$purchases = array();
		
		$actual_start_date = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE date >= ' . $start_date . ' ORDER BY date ASC LIMIT 1' );
		if ( $actual_start_date ) {
			$start_date = $actual_start_date->date;
		}
		
		$actual_end_date = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE date <= ' . $end_date . ' ORDER BY date DESC LIMIT 1' );
		if ( $actual_end_date ) {
			$end_date = $actual_end_date->date;
		}
		
		$purchase_info = new stdClass;
		
		$extra_where = '';
		if ( $name ) {
			$extra_where = ' AND b.name = \'' . $name . '\'';	
		}                
		
		$sales = $wpdb->get_results( 'SELECT SUM(price) AS p,count(*) AS c,name FROM ' . $wpdb->prefix . 'wpsc_purchase_logs AS a INNER JOIN ' . $wpdb->prefix . 'wpsc_cart_contents AS b ON a.id = b.purchaseid WHERE processed IN (2,3,4) AND date >= ' . $start_date . ' AND date <=' . $end_date . $extra_where . ' GROUP BY name ORDER BY p DESC LIMIT 10' ); 
		
		if ( $sales ) {
			foreach( $sales as $sale ) {
				$purchase_info = new stdClass;
		
				$purchase_info->total = $sale->p;
				$purchase_info->count = $sale->c;
		
				$purchases[ $sale->name ] = $purchase_info;
			}	
		}
		
		return $purchases;
	}
	
	function get_last_purchase_hash() {
		global $wpdb;
		
		$result = $wpdb->get_row( 'SELECT date FROM ' . $wpdb->prefix . 'wpsc_purchase_logs WHERE processed = 2 ORDER BY date DESC LIMIT 1' );		
		if ( $result ) {
			return $result->date;	
		} else {
			return 0;
		}
	}
}

		
add_action( 'wpsc_transaction_result_cart_item', 'piggy_wpec_txn_result' );

function piggy_wpec_txn_result( $cart_data ) {
	$settings = piggy_get_settings();
	
	if ( $cart_data['purchase_log']['email_sent'] == 0 ) {
		piggy_send_notification_message( '$' . $cart_data['cart_item']['price'] . ' - ' . $cart_data['cart_item']['name'] );
	}
}
