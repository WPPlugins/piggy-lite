<?php

require_once( 'base-helper.php' );

class WooCommerceHelper extends BaseCommerceHelper {
	var $summary_cache;
	var $order_total_cache;
	var $completed_posts;
	var $all_post_items;

	function WooCommerceHelper( $deep_init = false ) {
		parent::BaseCommerceHelper( 'WooCommerce' );
		$this->summary_cache = array();
		$this->order_total_cache = array();
		$this->all_post_items = array();

		if ( $deep_init ) {
			global $wpdb;
			$sql = "SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE term_taxonomy_id IN (SELECT term_taxonomy_id FROM " . $wpdb->prefix . "term_taxonomy WHERE taxonomy = 'shop_order_status' AND term_id IN (SELECT term_id FROM " . $wpdb->prefix . "terms WHERE name = 'refunded' OR name = 'pending' OR name = 'cancelled' ) );";
			$results = $wpdb->get_results( $sql );
			foreach( $results as $result ) {
				$this->completed_posts[] = $result->object_id;
			}

			$sql = "SELECT post_ID,meta_value FROM " . $wpdb->prefix . "postmeta WHERE post_ID NOT IN " . $this->get_completed_posts_string() . " AND meta_key = '_order_items'";
			$results = $wpdb->get_results( $sql );
			foreach( $results as $result ) {
				$this->all_post_items[ $result->post_ID ] = unserialize( $result->meta_value );
			}
		}
		
	}

	function get_completed_posts_string() {
		$completed_posts_string = '';
		if ( $this->completed_posts ) {
			$completed_posts_string = implode( ',', $this->completed_posts );
		}

		return '(' . $completed_posts_string . ')';
	}

	
	function is_detected() {
		return function_exists( 'woocommerce_init' ) || class_exists( 'Woocommerce' );
	}		

	function was_item_refunded( $post_id ) {
		$terms = wp_get_object_terms( $post_id, 'shop_order_status', array( 'fields' => 'slugs' ) );
		$status = ( isset($terms[0])) ? $terms[0] : 'pending';
		return ( $status == 'refunded' );
	}
	
	function get_items_for_post( $post_id ) {
		if ( isset( $this->all_post_items[$post_id] ) ) {
			return $this->all_post_items[ $post_id ];
		}

		$items = false;
		
		$post = get_post( $post_id );		
		
		if ( $post->post_status == 'publish' && get_post_meta( $post_id, '_recorded_sales', 'no' ) == 'yes' ) { 
			$items = get_post_meta( $post_id, '_order_items', true );
		}

		$this->all_post_items[ $post_id ] = $items;
		
		return $items;
	}
	
	function get_total_amount_of_order( $post_id ) {
		if ( isset( $this->order_total_cache[ $post_id ] ) ) {
			return $this->order_total_cache[ $post_id ];
		}

		$amount = false;
		
		$post = get_post( $post_id );
		
		if ( $post->post_status == 'publish' && get_post_meta( $post_id, '_recorded_sales', 'no' ) == 'yes' ) { 
			$amount = get_post_meta( $post_id, '_order_total', true );
		}

		$this->order_total_cache[ $post_id ] = $amount;
		
		return $amount;
	}
		
	// This is used for the "Current" summary sections, near the top		
	function get_summary_between_dates( $start_date, $end_date ) {
		global $wpdb;

		$actual_start_date = $wpdb->get_row( "SELECT post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_type = 'shop_order' AND post_date_gmt >= '" . piggy_mysql_time_from_gmt_timestamp( $start_date ) . "' ORDER BY post_date_gmt ASC LIMIT 1" );
		
		if ( $actual_start_date ) {
			$start_date = $actual_start_date->post_date_gmt;
		} else {
			$start_date = piggy_mysql_time_from_gmt_timestamp( $start_date );
		}
		
		$actual_end_date = $wpdb->get_row( "SELECT post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_type = 'shop_order' AND post_date_gmt <= '" . piggy_mysql_time_from_gmt_timestamp( $end_date ) . "' ORDER BY post_date_gmt DESC LIMIT 1" );
		
		if ( $actual_end_date ) {
			$end_date = $actual_end_date->post_date_gmt;
		} else {
			$end_date = piggy_mysql_time_from_gmt_timestamp( $end_date );
		}

		$cache_key = md5( $start_date . $end_date );
		if ( isset( $this->summary_cache[ $cache_key ] ) ) {
			return $this->summary_cache[ $cache_key ];
		}		
		
		$purchase_info = new stdClass;
		$purchase_info->total = 0;
		$purchase_info->count = 0;
		

		$sql = "SELECT count(ID) as c,sum(meta_value) as total FROM " . $wpdb->prefix . "posts," . $wpdb->prefix . "postmeta WHERE post_type = 'shop_order' AND post_date_gmt >= '" . $start_date . "' AND post_date_gmt <= '" . $end_date . "' AND " . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "postmeta.post_ID and meta_key = '_order_total' AND " . $wpdb->prefix . "posts.ID NOT IN " . $this->get_completed_posts_string() . ";";
		$results = $wpdb->get_row( $sql );
		if ( $results ) {

			$purchase_info->count = $results->c;
			$purchase_info->total = $results->total;
	
			if ( $purchase_info->count ) {
				$total_days = ( strtotime( $end_date ) - strtotime( $start_date ) ) / 86400;	
				if ( $total_days ) {
					$purchase_info->amount_per_day = $purchase_info->total / $total_days;
				}
			}			
		}		
		$total_time = microtime( true ) - $start_time;

		$this->summary_cache[ $cache_key ] = $purchase_info;
		
		return $purchase_info;
	}			

	// This is used to popular the 'Today' information area
	function get_sales_between_dates( $start_date, $end_date, $name = false ) {		
		
		global $wpdb;
		$purchases = array();
		
		$actual_start_date = $wpdb->get_row( "SELECT post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_type = 'shop_order' AND post_date_gmt >= '" . piggy_mysql_time_from_gmt_timestamp( $start_date ) . "' ORDER BY post_date_gmt ASC LIMIT 1" );
		
		if ( $actual_start_date ) {
			$start_date = $actual_start_date->post_date_gmt;
		} else {
			$start_date = piggy_mysql_time_from_gmt_timestamp( $start_date );
		}
		
		$actual_end_date = $wpdb->get_row( "SELECT post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_type = 'shop_order' AND post_date_gmt <= '" . $end_date . "' ORDER BY post_date_gmt DESC LIMIT 1" );
		
		
		if ( $actual_end_date ) {
			$end_date = $actual_end_date->post_date_gmt;
		} else {
			$end_date = piggy_mysql_time_from_gmt_timestamp( $end_date );
		}
		
		$start = microtime( true );
		$sql = "SELECT ID,post_date_gmt,meta_value FROM " . $wpdb->prefix . "posts," . $wpdb->prefix . "postmeta WHERE post_type = 'shop_order' AND post_date_gmt >= '" . $start_date . "' AND post_date_gmt <= '" . $end_date . "' AND ID NOT IN " . $this->get_completed_posts_string() . " AND meta_key = '_order_total' AND " . $wpdb->prefix ."posts.ID = " . $wpdb->prefix . "postmeta.post_ID ORDER BY post_date_gmt DESC";
		$results = $wpdb->get_results( $sql );
		if ( $results ) {
			foreach( $results as $post ) {
				$purchase_info = new stdClass;
		
				$amount = $post->meta_value;
				
				if ( $amount ) {
					$purchase_info->total_price = $amount;
					$purchase_info->date = strtotime( $post->post_date_gmt . ' GMT' );
					$purchase_info->sales = array();
					$purchase_info->id = $post->ID;	
					
					$items = $this->get_items_for_post( $post->ID );
					if ( $items ) {
						foreach( $items as $item ) {
							$one_sale = new stdClass;
							$one_sale->product = $item['name'];
							
							if ( isset( $item['item_meta'] ) && count( $item['item_meta'] ) ) {
								$meta_array = array();
								foreach( $item['item_meta'] as $key => $value ) {
									$meta_array[] = $value;
								}
								
								$one_sale->product = $one_sale->product;
							}
							
							$one_sale->value = $item['cost'];
							$one_sale->quantity = $item['qty'];
							
							$purchase_info->sales[] = $one_sale;					
						}
					}
					
					$purchases[] = $purchase_info;		
				}
			}
		}
		//echo microtime( true ) - $start;
		
		
		return $purchases;
	}	

	// This is used for the Product 'Best Seller's area
	function get_product_summary_between_dates( $start_date, $end_date, $name = false ) {
		
		global $wpdb;
		$purchases = array();
		
		$actual_start_date = $wpdb->get_row( "SELECT post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_type = 'shop_order' AND post_date_gmt >= '" . piggy_mysql_time_from_gmt_timestamp( $start_date ) . "' ORDER BY post_date_gmt ASC LIMIT 1" );
		
		if ( $actual_start_date ) {
			$start_date = $actual_start_date->post_date_gmt;
		} else {
			$start_date = piggy_mysql_time_from_gmt_timestamp( $start_date );
		}
		
		$actual_end_date = $wpdb->get_row( "SELECT post_date_gmt FROM " . $wpdb->prefix . "posts WHERE post_type = 'shop_order' AND post_date_gmt <= '" . $end_date . "' ORDER BY post_date_gmt DESC LIMIT 1" );
		if ( $actual_end_date ) {
			$end_date = $actual_end_date->post_date_gmt;
		} else {
			$end_date = piggy_mysql_time_from_gmt_timestamp( $end_date );
		}
		
		$sql = "SELECT ID,meta_value as total FROM " . $wpdb->prefix . "posts," . $wpdb->prefix . "postmeta WHERE post_type = 'shop_order' AND post_date_gmt >= '" . $start_date . "' AND post_date_gmt <= '" . $end_date . "' AND " . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "postmeta.post_ID and meta_key = '_order_total' AND " . $wpdb->prefix . "posts.ID NOT IN " . $this->get_completed_posts_string();
		$results = $wpdb->get_results( $sql );
		
		$start_time = microtime( true );
		$products = array();
		if ( $results ) {
			foreach( $results as $result ) {
				$amount = $result->total;
				
				if ( !$amount ) {
					continue;
				}
				
				$items = $this->get_items_for_post( $result->ID );
				
				if ( is_array( $items ) && count( $items ) ) {
					foreach( $items as $item ) {
						$product_name = $item['name'];
						
						if ( !isset( $products[ $product_name ] ) ) {
							$products[ $product_name ] = new stdClass;
						}
						
						$products[ $product_name ]->count++;
						$products[ $product_name ]->total += $item['line_subtotal'];
					}
				}
			}
		}

		return ( $products );
	}
}
