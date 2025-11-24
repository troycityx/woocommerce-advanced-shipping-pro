<?php
/*
 * Plugin Name: 	WooCommerce Advanced Shipping Pro
 * Plugin URI: 		https://your-site.com/woocommerce-advanced-shipping-pro/
 * Description: 	WooCommerce Advanced Shipping Pro is a powerful plugin which allows you to set up advanced shipping conditions and rules.
 * Version: 		1.1.7
 * Author: 			Your Name
 * Author URI: 		https://your-site.com/
 * Text Domain: 	woocommerce-advanced-shipping-pro
 * WC requires at least: 6.0.0
 * WC tested up to:      8.4

 * Copyright Your Name
 *
 *     This file is part of WooCommerce Advanced Shipping Pro,
 *     a plugin for WordPress.
 *
 *     WooCommerce Advanced Shipping Pro is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 3 of the License, or (at your option)
 *     any later version.
 *
 *     WooCommerce Advanced Shipping Pro is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WooCommerce_Advanced_Shipping_Pro.
 *
 * Main WCASP class, add filters and handling all other files.
 *
 * @class       WooCommerce_Advanced_Shipping_Pro
 * @version     1.0.0
 * @author      Your Name
 */
class WooCommerce_Advanced_Shipping_Pro {


	/**
	 * Version.
	 *
	 * @since 1.0.4
	 * @var string $version Plugin version number.
	 */
	public $version = '1.1.7';


	/**
	 * File.
	 *
	 * @since 1.0.8
	 * @var string $file Main plugin file path.
	 */
	public $file = __FILE__;


	/**
	 * Instance of WooCommerce_Advanced_Shipping_Pro.
	 *
	 * @since 1.0.3
	 * @access private
	 * @var object $instance The instance of WCASP.
	 */
	private static $instance;

	/**
	 * @var Wcasp_Match_Conditions
	 */
	public $matcher;

	/**
	 * @var WCASP_post_type
	 */
	public $post_type;

	/**
	 * @var WCASP_Ajax
	 */
	public $ajax;

	/**
	 * @var WCASP_Admin
	 */
	public $admin;

	/**
	 * @var Wcasp_Shipping_Method
	 */
	public $shipping_method;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Check if WooCommerce is active
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && ! function_exists( 'WC' ) ) {
			return;
		}

		$this->init();

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.1.0
	 *
	 * @return  object  Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}


	/**
	 * Init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( version_compare( PHP_VERSION, '7.0', 'lt' ) ) {
			return add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
		}

		// Add hooks/filters
		$this->hooks();

		// Load textdomain
		$this->load_textdomain();

		// Updater
		$this->update();

		require_once plugin_dir_path( __FILE__ ) . '/libraries/wp-conditions/functions.php';

		// Functions
		require_once plugin_dir_path( __FILE__ ) . 'includes/core-functions.php';

		/**
		 * Require matching conditions hooks.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcasp-match-conditions.php';
		$this->matcher = new Wcasp_Match_Conditions();

		/**
		 * Require file with settings.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcasp-post-type.php';
		$this->post_type = new WCASP_post_type();

		/**
		 * Load ajax methods
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcasp-ajax.php';
		$this->ajax = new WCASP_Ajax();

		/**
		 * Admin class
		 */
		if ( is_admin() ) :
			require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-wcasp-admin.php';
			$this->admin = new WCASP_Admin();
		endif;

		// Declare HPOS compatibility
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );
	}


	/**
	 * Update.
	 *
	 * Runs when the plugin is updated and checks if there should be
	 * any data updated to be compatible for the new version.
	 *
	 * @since 1.0.3
	 */
	public function update() {

		$db_version = get_option( 'wcasp_plugin_version', '1.0.0' );

		// Stop current version is up to date
		if ( $db_version >= $this->version ) :
			return;
		endif;

		// Update functions for 1.0.3/1.0.5
		if ( version_compare( '1.0.3', $db_version ) || version_compare( '1.0.5', $db_version ) ) :

			$wcasp_method_settings = get_option( 'woocommerce_advanced_shipping_pro_settings' );
			if ( isset( $wcasp_method_settings['hide_other_shipping_when_available'] ) ) :
				$wcasp_method_settings['hide_other_shipping'] = $wcasp_method_settings['hide_other_shipping_when_available'];
				update_option( 'woocommerce_advanced_shipping_pro_settings', $wcasp_method_settings );
			endif;

		endif;

		update_option( 'wcasp_plugin_version', $this->version );

	}


	/**
	 * Hooks.
	 *
	 * Initialize all class hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		// Initialize shipping method class
		add_action( 'woocommerce_shipping_init', array( $this, 'wcasp_shipping_method' ) );

		// Add shipping method
		add_filter( 'woocommerce_shipping_methods', array( $this, 'wcasp_add_shipping_method' ) );

	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-advanced-shipping-pro', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Shipping method.
	 *
	 * Include the WooCommerce shipping method class.
	 *
	 * @since 1.0.0
	 */
	public function wcasp_shipping_method() {

		/**
		 * WCASP shipping method
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcasp-method.php';
		$this->shipping_method = new Wcasp_Shipping_Method();

	}


	/**
	 * Add shipping method.
	 *
	 * Add shipping method to WooCommerce.
	 *
	 * @since 1.0.0
	 */
	public function wcasp_add_shipping_method( $methods ) {

		if ( class_exists( 'Wcasp_Shipping_Method' ) ) :
			$methods[] = 'Wcasp_Shipping_Method';
		endif;

		return $methods;

	}


	/**
	 * Display PHP 7.0 required notice.
	 *
	 * Display a notice when the required PHP version is not met.
	 *
	 * @since 1.0.6
	 */
	public function php_version_notice() {

		?><div class='updated'>
			<p><?php echo sprintf( __( 'WooCommerce Advanced Shipping Pro requires PHP 7.0 or higher and your current PHP version is %s. Please (contact your host to) update your PHP version.', 'woocommerce-advanced-shipping-pro' ), PHP_VERSION ); ?></p>
		</div><?php

	}

}


if ( ! function_exists( 'WCASP' ) ) :

	/**
	 * The main function responsible for returning the WooCommerce_Advanced_Shipping_Pro object.
	 *
	 * Use this function like you would a global variable, except without needing to declare the global.
	 *
	 * Example: <?php WCASP()->method_name(); ?>
	 *
	 * @since 1.1.0
	 *
	 * @return  object  WooCommerce_Advanced_Shipping_Pro class object.
	 */
	function WCASP() {

		return WooCommerce_Advanced_Shipping_Pro::instance();

	}


endif;

WCASP();