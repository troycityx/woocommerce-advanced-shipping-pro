<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WCASP_Admin.
 *
 * Admin class handles all admin related business.
 *
 * @class		WCASP_Admin
 * @version		1.0.0
 * @author		Your Name
 */
class WCASP_Admin {


	/**
	 * Constructor.
	 *
	 * @since 1.0.8
	 */
	public function __construct() {
		// Initialize class
		add_action( 'admin_init', array( $this, 'init' ) );
	}


	/**
	 * Initialize class parts.
	 *
	 * @since 1.0.8
	 */
	public function init() {

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Add to WC Screen IDs to load scripts.
		add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_ids' ) );

		// Keep WC menu open while in WCASP edit screen
		add_action( 'admin_head', array( $this, 'menu_highlight' ) );

		global $pagenow;
		if ( 'plugins.php' == $pagenow ) :
			add_filter( 'plugin_action_links_' . plugin_basename( WCASP()->file ), array( $this, 'add_plugin_action_links' ), 10, 2 );
		endif;

	}


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue javascript and stylesheets to the admin area.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {

		wp_register_style( 'woocommerce-advanced-shipping-pro', plugins_url( 'assets/css/woocommerce-advanced-shipping-pro.min.css', WCASP()->file ), array(), WCASP()->version );

		if (
			( isset( $_REQUEST['post'] ) && 'wcasp' == get_post_type( $_REQUEST['post'] ) ) ||
			( isset( $_REQUEST['post_type'] ) && 'wcasp' == $_REQUEST['post_type'] ) ||
			( isset( $_REQUEST['section'] ) && 'advanced_shipping_pro' == $_REQUEST['section'] )
		) :

			wp_localize_script( 'wp-conditions', 'wpc2', array(
				'action_prefix' => 'wcasp_',
			) );

			wp_enqueue_style( 'woocommerce-advanced-shipping-pro' );
			wp_enqueue_script( 'wp-conditions' );

			wp_dequeue_script( 'autosave' );

		endif;

	}


	/**
	 * Screen IDs.
	 *
	 * Add 'wcasp' to the screen IDs so the WooCommerce scripts are loaded.
	 *
	 * @since 1.0.8
	 *
	 * @param   array  $screen_ids  List of existing screen IDs.
	 * @return  array               List of modified screen IDs.
	 */
	public function add_screen_ids( $screen_ids ) {

		$screen_ids[] = 'wcasp';

		return $screen_ids;

	}


	/**
	 * Keep menu open.
	 *
	 * Highlights the correct top level admin menu item for post type add screens.
	 *
	 * @since 1.0.0
	 */
	public function menu_highlight() {

		global $parent_file, $submenu_file, $post_type;

		if ( 'wcasp' == $post_type ) :
			$parent_file  = 'woocommerce';
			$submenu_file = 'wc-settings';
		endif;

	}


	/**
	 * Plugin action links.
	 *
	 * Add links to the plugins.php page below the plugin name
	 * and besides the 'activate', 'edit', 'delete' action links.
	 *
	 * @since 1.0.10
	 *
	 * @param   array   $links  List of existing links.
	 * @param   string  $file   Name of the current plugin being looped.
	 * @return  array           List of modified links.
	 */
	public function add_plugin_action_links( $links, $file ) {

		if ( $file == plugin_basename( WCASP()->file ) ) :
			$links = array_merge( array(
				'<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_pro' ) ) . '">' . __( 'Settings', 'woocommerce-advanced-shipping-pro' ) . '</a>'
			), $links );
		endif;

		return $links;

	}


}