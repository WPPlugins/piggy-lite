<?php

define( 'PIGGY_SECONDS_PER_DAY', 3600*24 );

class Piggy {
	var $settings;
	var $tabs;
	var $post;
	var $get;
	var $has_cleaned_up;
	var $date_time_helper;
	var $transient_set;
	var $locale;

	function Piggy() {
		$this->settings = false;
		$this->tabs = array();
		$this->post = false;
		$this->get = false;
		$this->has_cleaned_up = false;
		$this->date_time_helper = array();
		$this->transient_set = false;
		$this->locale = false;
	}

	// Initialize Piggy variables
	function initialize() {
		define( 'PIGGY_PRODUCT_NAME', 'Piggy' );

		// Messaging
		require_once( PIGGY_DIR . '/include/prowl.php' );
		require_once( PIGGY_DIR . '/include/howl.php' );

		do_action( 'piggy_pro_version' );

		add_action( 'init', array( &$this, 'piggy_init' ) );
		add_action( 'wp', array( &$this, 'piggy_wp' ) );

		if ( is_admin() && ( strpos( $_SERVER['REQUEST_URI'], PIGGY_ROOT_DIR . '/' ) !== false ) ) {
			add_action( 'admin_init', array( &$this, 'piggy_admin_css' ) );
		}

		add_action( 'piggy_head', array( &$this, 'piggy_head' ) );
		add_action( 'piggy_footer', array( &$this, 'piggy_footer' ) );

		$is_piggy_page = ( ( strpos( $_SERVER['REQUEST_URI'], 'piggy.php' ) !== false ) || ( strpos( $_SERVER['REQUEST_URI'], PIGGY_ROOT_DIR . '/admin/admin-panel.php' ) !== false ) );

		// Setup languages
		$settings = $this->get_settings();
		if ( $settings->piggy_language != 'en_EN' ) {
			// load language file
			$lang_file = PIGGY_DIR . '/lang/' . $settings->piggy_language . '.mo';
			if ( file_exists( $lang_file ) ) {
				load_textdomain( 'piggy', apply_filters( 'piggy_language_file', $lang_file ) );
			}
		}

		if ( $settings->supported_platform == 'woo-commerce' ) {
			require_once( PIGGY_DIR . '/include/helpers/woo-notifications.php' );
		}

		$this->locale = $settings->piggy_language;

		$this->check_directories();
	}

	function make_directory( $dir_name ) {
		@wp_mkdir_p( $dir_name );
	}

	function check_directories() {
		$this->make_directory( PIGGY_TEMP_DIR );
	}

	/* Pro Functions */
	function piggy_handle_prowl_howl_test( $settings ) {
		if ( isset( $this->post[ 'send_test_prowl_msg' ] ) ) {
			if ( count( $settings->prowl_api_keys ) ) {
				foreach( $settings->prowl_api_keys as $api_key ) {
					$p = new PiggyProwl;
					$p->set_api_key( $api_key );
					$p->send_message(
						__( "Test Notification", "piggy" ),
						__( "Looks like it's working!", "piggy" )
					);
				}
			}

			$settings->send_test_prowl_msg = false;
		} else if ( isset( $this->post[ 'send_test_howl_msg' ] ) ) {
			for( $i = 0; $i < count( $settings->howl_usernames ); $i++ ) {
				$howl = new Howl;

				$howl->set_username( $settings->howl_usernames[$i] );
				$howl->set_password( $settings->howl_passwords[$i] );

				$howl->send_message(
					__( "Test Notification", "piggy" ),
					__( "Looks like it's working!", "piggy" ),
					"default-howl.png"
				);
			}

			$settings->send_test_howl_msg = false;
		}

		return $settings;
	}

	function set_transient() {
		$this->transient_set = true;
	}

	function set_latest_version_info( $latest_info ) {
		$this->latest_version_info = $latest_info;
	}

	// month is 1 based
	function days_in_each_month( $month, $year = false ) {
		if ( !$year ) {
			$year = piggy_date( 'Y' );
		}

		$is_leap_year = ( $year % 4 == 0 ) && ( $year % 100 == 0 ) && ( $year % 400 == 0 );

		switch( $month ) {
			case 1:
				// January
				return 31;
			case 2:
				// February
				if ( $is_leap_year ) {
					return 29;
				} else {
					return 28;
				}
			case 3:
				// March
				return 31;
			case 4:
				// April
				return 30;
			case 5:
				// May
				return 31;
			case 6:
				// June
				return 30;
			case 7:
				// July
				return 31;
			case 8:
				// August
				return 31;
			case 9:
				// September
				return 30;
			case 10:
				// October
				return 31;
			case 11:
				// November
				return 30;
			case 12:
				// December
				return 31;
		}
	}

	function get_absolute_piggy_url() {
		$settings = $this->get_settings();

		return rtrim( home_url(), '/' ) . '/' . ltrim( $settings->display_url, '/' );
	}

	function get_absolute_piggy_ajax_url() {
		return $this->get_absolute_piggy_url() . '?piggy_ajax=1';
	}

	function setup_date_time() {
		$settings = $this->get_settings();

		date_default_timezone_set( $settings->timezone );

		// rescales everything so Monday is 0
		$day_of_week = ( ( piggy_date( 'w' ) + 6 ) % 7 );

		$this->date_time_helper['today'] = array( piggy_mktime( 0, 0, 0 ), piggy_mktime( 23, 59, 59 ) );
		$this->date_time_helper['yesterday'] = array( $this->date_time_helper['today'][0] - PIGGY_SECONDS_PER_DAY, $this->date_time_helper['today'][1] - PIGGY_SECONDS_PER_DAY );

		$this->date_time_helper['this-month'] = array( piggy_mktime( 0, 0, 0, piggy_date( 'n' ), 1 ), piggy_mktime(23, 59, 59, piggy_date( 'n' ), $this->days_in_each_month( piggy_date( 'n' ) ) ) );

		$this->date_time_helper['this-year'] = array( piggy_mktime(0, 0, 0, 1, 1 ), piggy_mktime(23, 59, 59, 12, 31 ) );

		$month = piggy_date( 'n' );
		$year = piggy_date( 'Y' );

		$this->date_time_helper['last-year'] = array( piggy_mktime(0, 0, 0, 1, 1, $year - 1 ), piggy_mktime(23, 59, 59, 12, 31, $year - 1 ) );

		$month = $month - 1;
		if ( $month == 0 ) {
			$month = 12;
			$year = $year - 1;
		}

		$this->date_time_helper['last-month'] = array( piggy_mktime(0, 0, 0, $month, 1, $year ), piggy_mktime(23, 59, 59, $month, $this->days_in_each_month( $month, $year ), $year ) );

		$this->date_time_helper['this-week'] = array( $this->date_time_helper['today'][0] - $day_of_week*PIGGY_SECONDS_PER_DAY, $this->date_time_helper['today'][0] + ( 7 - $day_of_week )*PIGGY_SECONDS_PER_DAY - 1 );
		$this->date_time_helper['last-week'] = array( $this->date_time_helper['this-week'][0] - PIGGY_SECONDS_PER_DAY*7, $this->date_time_helper['this-week'][1] - PIGGY_SECONDS_PER_DAY*7 );
		$this->date_time_helper['two-weeks-ago'] = array( $this->date_time_helper['this-week'][0] - PIGGY_SECONDS_PER_DAY*14, $this->date_time_helper['this-week'][1] - PIGGY_SECONDS_PER_DAY*14 );

		// Normalize Datas and Times
		foreach( $this->date_time_helper as $key => $value ) {
			$this->date_time_helper[ $key ] = array( ( $value[0] ), ( $value[1] ) );
		}
	}

	function cleanup_post_and_get() {
		if ( !$this->has_cleaned_up ) {
			if ( count( $_GET ) ) {
				foreach( $_GET as $key => $value ) {
					if ( get_magic_quotes_gpc() ) {
						$this->get[ $key ] = @stripslashes( $value );
					} else {
						$this->get[ $key ] = $value;
					}
				}
			}

			if ( count( $_POST ) ) {
				foreach( $_POST as $key => $value ) {
					if ( get_magic_quotes_gpc() ) {
						$this->post[ $key ] = @stripslashes( $value );
					} else {
						$this->post[ $key ] = $value;
					}
				}
			}

			$this->has_cleaned_up = true;
		}
	}

	function verify_post_nonce() {
		$nonce = $this->post['piggy-admin-nonce'];
		if ( !wp_verify_nonce( $nonce, 'piggy-post-nonce' ) ) {
			echo 'Security Failure';
			die;
		}
	}

	function process_submitted_settings() {
		// Check to make sure it's a real POST request
		if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( isset( $this->post['piggy-submit'] ) ) {
			$this->verify_post_nonce();

			$settings = $this->get_settings();

			foreach( (array)$settings as $name => $value ) {
				if ( isset( $this->post[ $name ] ) ) {

					// Remove slashes if they exist
					if ( is_string( $this->post[ $name ] ) ) {
						$this->post[ $name ] = htmlspecialchars_decode( $this->post[ $name ] );
					}

					$settings->$name = apply_filters( 'piggy_setting_filter_' . $name, $this->post[ $name ] );
				} else {
					// Remove checkboxes if they don't exist as data
					if ( isset( $this->post[ $name . '-hidden' ] ) ) {
						$settings->$name = false;
					}
				}
			}

			// Check for Prowl settings
			$settings->prowl_api_keys = array();
			foreach( $this->post as $key => $value ) {
				if ( preg_match( '#prowl_api_keys_(.*)#i', $key, $matches ) ) {
					if ( strlen( $this->post[ $matches[0] ] ) ) {
						$settings->prowl_api_keys[] = $this->post[ $matches[0] ];
					}
				}
			}

			// Check for Howl settings
			$settings->howl_usernames = array();
			$settings->howl_passwords = array();
			foreach( $this->post as $key => $value ) {
				if ( preg_match( '#howl_username_(.*)#i', $key, $matches ) ) {
					if ( strlen( $this->post[ $matches[0] ] ) ) {
						$settings->howl_usernames[] = $this->post[ $matches[0] ];
					}
				}
			}

			foreach( $this->post as $key => $value ) {
				if ( preg_match( '#howl_password_(.*)#i', $key, $matches ) ) {
					if ( strlen( $this->post[ $matches[0] ] ) ) {
						$settings->howl_passwords[] = $this->post[ $matches[0] ];
					}
				}
			}

			$settings = apply_filters( 'piggy_pre_settings_save', $settings );

			$this->save_settings( $settings );

			do_action( 'piggy_settings_saved' );

		} else if ( isset( $this->post['piggy-submit-reset'] ) ) {
			$this->verify_post_nonce();

			// Remove the setting from the DB
			update_option( PIGGY_SETTING_NAME, false );

			// Force a reload of settings
			$this->settings = false;
		}
	}

	function piggy_head() {
		$settings = $this->get_settings();

		echo "<meta name='apple-mobile-web-app-capable' content='yes' />\n";
		echo "<meta name='apple-mobile-web-app-status-bar-style' content='black' />\n";
//		echo '<meta name="apple-mobile-web-app-status-bar-style" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" content="black-translucent">' . "\n";
		echo "<meta name='viewport' content='initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width' />\n";
		// iPhone 5
		echo "<meta name='viewport' content='initial-scale=1.0, maximum-scale=1.0, user-scalable=no' media='(device-height: 568px)' />\n";

		echo "<link href='//fonts.googleapis.com/css?family=Lato:300,700' rel='stylesheet' type='text/css'>\n";

		/* App Icon */
		echo "<link rel='apple-touch-icon-precomposed' href='" . PIGGY_URL  . "/images/webapp/app-icon.png' />\n";

		/* Startup Images */

		// iPhone 2G/3G/3GS
		echo '<link href="' . PIGGY_URL . '/images/webapp/startup-320x460.png" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 1)" rel="apple-touch-startup-image">' . "\n";
		// iPhone 4/4S
		echo '<link href="' . PIGGY_URL . '/images/webapp/startup-640x920.png" media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">' . "\n";
		// iPhone 5/5S
		echo '<link href="' . PIGGY_URL . '/images/webapp/startup-640x1096.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">' . "\n";

		// CSS
		echo "<link href='" . piggy_get_fontawesome_url() . "' rel='stylesheet' />\n";
		echo "<link href='" . piggy_get_add2home_stylesheet_url() . "' rel='stylesheet' />\n";
		echo "<link href='" . piggy_get_stylesheet_url() . "' rel='stylesheet' />\n";

		// JS
		echo "<script type='text/javascript'>\n";
		echo "\tvar purchaseHash = '" . piggy_get_last_purchase_hash() . "';\n";
		echo "\tvar piggyAjaxUrl = '" . $this->get_absolute_piggy_ajax_url() . "';\n";
		echo "\tvar piggyWordPressURL = '" . get_bloginfo( 'wpurl' ) . "';\n";
		echo "\tvar passKeyNumber = '" . $settings->passcode_length . "';\n";
		if ( $settings->always_require_passcode ) {
			echo "\tvar requirePasscode = 1;\n";
		} else {
			echo "\tvar requirePasscode = 0;\n";
		}

		$localize_params = array(
		//	'install_message' => __( 'To install this web app on your %device: tap %icon then "<strong>Add to Home Screen</strong>".', 'piggy' ),
			'try_again_msg' => __( 'Try again.', 'piggy' ),
			'last_checked' => __( 'Last checked for updates:', 'piggy' ),
			'external_link_msg' => __( 'This link will open in your browser.', 'piggy' ),
			'enter_code' => __( 'Your 4 or 5-digit passcode', 'piggy' ),
			'wrong_code' => __( 'Fat thumbs?', 'piggy' ),
			'right_code' => __( 'Access Granted!', 'piggy' )

		);

		foreach( $localize_params as $key => $value ) {
			echo "\tvar piggy_" . $key . " = '" . $value . "';\n";
		}
		echo "</script>\n";

	}

	function piggy_footer(){
		// JS
		echo "<script src='" . piggy_get_jquery_url() . "'></script>\n";
		echo "<script src='" . piggy_get_framework_js_url() . "'></script>\n";
		echo "<script src='" . piggy_get_piggy_js_url() . "'></script>\n";
		echo "<script src='" . piggy_get_add2home_js_url() . "'></script>\n";
	}

	function piggy_admin_css() {
		wp_enqueue_style( 'piggy-admin-css', PIGGY_URL . '/admin/css/piggy-admin.css', '', md5( PIGGY_VERSION ) );
		wp_enqueue_script( 'piggy-plugins', PIGGY_URL . '/admin/js/piggy-plugins.js', array( 'jquery' ), md5( PIGGY_VERSION ) );
		wp_enqueue_script( 'piggy-admin', PIGGY_URL . '/admin/js/piggy-admin.js', array( 'jquery', 'piggy-plugins' ), md5( PIGGY_VERSION ) );

		$piggy_localize = array(
			'admin_nonce' => wp_create_nonce( 'piggy_admin' ),
			'prowl_api_text' => __( 'Prowl API Key', 'piggy' ),
			'reset_settings_message' => __( 'Reset all Piggy admin settings? This operation cannot be undone.', 'piggy' ),
			'activate_message' => __( 'Activating license, please wait...', 'piggy' )
		);

		wp_localize_script( 'piggy-admin', 'PiggyCustom', $piggy_localize );
	}

	function piggy_init() {
		$is_piggy_page = ( strpos( $_SERVER['REQUEST_URI'], PIGGY_ROOT_DIR . '/' ) !== false ) && is_admin();

		// Only process POST settings on piggy pages
		if ( $is_piggy_page ) {
			add_filter( 'piggy_pre_settings_save', array( &$this, 'piggy_handle_prowl_howl_test' ) );

			$this->cleanup_post_and_get();

			$this->process_submitted_settings();
		}

		if ( isset( $_GET['piggy_dynamic'] ) ) {
			include( PIGGY_DIR . '/css/dynamic-styles.php' );
			die;
		}

		if ( isset( $_GET['piggy_purchase_hash'] ) ) {
			echo piggy_get_last_purchase_hash();
			die;
		}

		if ( isset( $_POST['piggyPassKey'] ) ) {
			$settings = $this->get_settings();
			if ( $_POST['piggyPassKey'] == $settings->passcode ) {
				$rand = mt_rand();
				echo "{ 'result': 'pass', 'ip': '" . md5( $rand ) . "', 'hash': '" . md5( md5( $rand ) . $_POST['piggyPassKey'] ) . "' }";
			} else {
				echo "{ 'result': 'fail' }";
			}
			die;
		}

		if ( is_admin() ) {
			require_once( PIGGY_DIR . '/admin/admin-panel.php' );
		}
	}

	function piggy_wp() {
		if ( !is_admin() ) {
			$settings = $this->get_settings();

			if ( piggy_should_be_shown() ) {
				$this->setup_date_time();

				header( 'HTTP/1.1 200 OK' );
				include( PIGGY_DIR . '/templates/piggy.php' );
				die;
			}

		}
	}

	function get_settings() {
		if ( $this->settings ) {
			return apply_filters( 'piggy_settings', $this->settings );
		}

		$this->settings = get_option( PIGGY_SETTING_NAME, false );
		if ( !is_object( $this->settings ) ) {
			$this->settings = unserialize( $this->settings );
		}

		if ( !$this->settings ) {
			// Return default settings
			$this->settings = new PiggySettings;
			$defaults = apply_filters( 'piggy_default_settings', new PiggyDefaultSettings );

			foreach( (array)$defaults as $name => $value ) {
				$this->settings->$name = $value;
			}

			return apply_filters( 'piggy_settings', $this->settings );
		} else {
			// first time pulling them from the database, so update new settings with defaults
			$defaults = apply_filters( 'piggy_default_settings', new PiggyDefaultSettings );

			// Merge settings with defaults
			foreach( (array)$defaults as $name => $value ) {
				if ( !isset( $this->settings->$name ) ) {
					$this->settings->$name = $value;
				}
			}

			return apply_filters( 'piggy_settings', $this->settings );
		}
	}

	function save_settings( $settings ) {
		$settings = apply_filters( 'piggy_update_settings', $settings );

		$serialized_data = serialize( $settings );

		update_option( PIGGY_SETTING_NAME, $serialized_data );

		$this->settings = $settings;
	}
}
