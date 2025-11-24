<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class WCASP_post_type.
 *
 * Initialize the WCASP post type.
 *
 * @class       WCASP_post_type
 * @author     	Your Name
 * @package		WooCommerce Advanced Shipping Pro
 * @version		1.0.0
 */
class WCASP_post_type {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Add/save meta boxes
		add_action( 'add_meta_boxes', array( $this, 'post_type_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

		// Edit user messages
		add_filter( 'post_updated_messages', array( $this, 'custom_post_type_messages' ) );

		// Redirect after delete
		add_action( 'load-edit.php', array( $this, 'redirect_after_trash' ) );

	}


	/**
	 * Post type.
	 *
	 * Register 'wcasp' post type.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Advanced Shipping Pro rates', 'woocommerce-advanced-shipping-pro' ),
			'singular_name'      => __( 'Advanced Shipping Pro rate', 'woocommerce-advanced-shipping-pro' ),
			'add_new'            => __( 'Add New', 'woocommerce-advanced-shipping-pro' ),
			'add_new_item'       => __( 'Add New Advanced Shipping Pro rate', 'woocommerce-advanced-shipping-pro' ),
			'edit_item'          => __( 'Edit Advanced Shipping Pro rate', 'woocommerce-advanced-shipping-pro' ),
			'new_item'           => __( 'New Advanced Shipping Pro rate', 'woocommerce-advanced-shipping-pro' ),
			'view_item'          => __( 'View Advanced Shipping Pro rate', 'woocommerce-advanced-shipping-pro' ),
			'search_items'       => __( 'Search Advanced Shipping Pro rates', 'woocommerce-advanced-shipping-pro' ),
			'not_found'          => __( 'No Advanced Shipping Pro rates', 'woocommerce-advanced-shipping-pro' ),
			'not_found_in_trash' => __( 'No Advanced Shipping Pro rates found in Trash', 'woocommerce-advanced-shipping-pro' ),
		);

		register_post_type( 'wcasp', array(
			'label'              => 'wcasp',
			'show_ui'            => true,
			'show_in_menu'       => false,
			'public'             => false,
			'publicly_queryable' => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'rewrite'            => false,
			'_builtin'           => false,
			'query_var'          => true,
			'supports'           => array( 'title' ),
			'labels'             => $labels,
		) );

	}


	/**
	 * Messages.
	 *
	 * Modify the notice messages text for the 'wcasp' post type.
	 *
	 * @since 1.0.0
	 *
	 * @param   array  $messages  Existing list of messages.
	 * @return  array             Modified list of messages.
	 */
	function custom_post_type_messages( $messages ) {

		$post      = get_post();
		$post_type = get_post_type( $post );

		$messages['wcasp'] = array(
			0  => '',
			1  => __( 'Shipping rate updated.', 'woocommerce-advanced-shipping-pro' ),
			2  => __( 'Custom field updated.', 'woocommerce-advanced-shipping-pro' ),
			3  => __( 'Custom field deleted.', 'woocommerce-advanced-shipping-pro' ),
			4  => __( 'Shipping rate updated.', 'woocommerce-advanced-shipping-pro' ),
			6  => __( 'Shipping rate published.', 'woocommerce-advanced-shipping-pro' ),
			7  => __( 'Shipping rate saved.', 'woocommerce-advanced-shipping-pro' ),
			8  => __( 'Shipping rate submitted.', 'woocommerce-advanced-shipping-pro' ),
			9  => sprintf(
				__( 'Shipping method scheduled for: <strong>%1$s</strong>.', 'woocommerce-advanced-shipping-pro' ),
				date_i18n( __( 'M j, Y @ G:i', 'woocommerce-advanced-shipping-pro' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Shipping rate draft updated.', 'woocommerce-advanced-shipping-pro' ),
		);

		if ( 'wcasp' == $post_type ) :
			$overview_link = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_pro' );

			$overview                    = sprintf( ' <a href="%s">%s</a>', esc_url( $overview_link ), __( 'Return to overview.', 'woocommerce-advanced-shipping-pro' ) );
			$messages[ $post_type ][1]  .= $overview;
			$messages[ $post_type ][6]  .= $overview;
			$messages[ $post_type ][9]  .= $overview;
			$messages[ $post_type ][8]  .= $overview;
			$messages[ $post_type ][10] .= $overview;

		endif;

		return $messages;

	}


	/**
	 * Add meta boxes.
	 *
	 * Add two meta boxes to WCASP with conditions and settings.
	 *
	 * @since 1.0.0
	 */
	public function post_type_meta_box() {

		add_meta_box( 'wcasp_conditions', __( 'Advanced Shipping Pro conditions', 'woocommerce-advanced-shipping-pro' ), array( $this, 'render_wcasp_conditions' ), 'wcasp', 'normal' );
		add_meta_box( 'wcasp_settings', __( 'Shipping settings', 'woocommerce-advanced-shipping-pro' ), array( $this, 'render_wcasp_settings' ), 'wcasp', 'normal' );
		add_meta_box( 'wcasp_resources', __( 'Useful links', 'woocommerce-advanced-shipping-pro' ), array( $this, 'render_wcasp_resources' ), 'wcasp', 'side' );

	}


	/**
	 * Render meta box.
	 *
	 * Render and display the condition meta box contents.
	 *
	 * @since 1.0.0
	 */
	public function render_wcasp_conditions() {
		require_once plugin_dir_path( __FILE__ ) . 'admin/views/meta-box-conditions.php';
	}


	/**
	 * Render meta box.
	 *
	 * Render and display the settings meta box conditions.
	 *
	 * @since 1.0.0
	 */
	public function render_wcasp_settings() {
		require_once plugin_dir_path( __FILE__ ) . 'admin/views/meta-box-settings.php';
	}


	/**
	 * Show resources MB contents.
	 *
	 * @since 1.1.3
	 */
	function render_wcasp_resources() {

		?><ul>
			<li><a href="https://your-site.com/how-the-advanced-plugin-conditions-work?utm_source=WCASP-plugin&utm_medium=website&utm_campaign=WCASP-helpful-links" target="_blank"><?php _e( 'How the conditions work', 'woocommerce-advanced-shipping-pro' ); ?></a></li>
			<li><a href="https://your-site.com/apply-shipping-for-specific-products-in-woocommerce?utm_source=WCASP-plugin&utm_medium=website&utm_campaign=WCASP-helpful-links" target="_blank"><?php _e( 'Applying shipping to specific products', 'woocommerce-advanced-shipping-pro' ); ?></a></li>
			<li><a href="https://your-site.com/shipping-notices?utm_source=WCASP-plugin&utm_medium=website&utm_campaign=WCASP-helpful-links" target="_blank"><?php _e( 'Showing a shipping message', 'woocommerce-advanced-shipping-pro' ); ?></a></li>
			<li><a href="https://your-site.com/shipping-debug-mode?utm_source=WCASP-plugin&utm_medium=website&utm_campaign=WCASP-helpful-links" target="_blank"><?php _e( 'Disabling the shipping cache', 'woocommerce-advanced-shipping-pro' ); ?></a></li>
			<li><a href="https://your-site.com" target="_blank"><?php _e( 'Apply shipping cost using conditions', 'woocommerce-advanced-shipping-pro' ); ?></a></li>
			<hr />
			<li><a href="https://your-site.com/contact?utm_source=WCASP-plugin&utm_medium=website&utm_campaign=WCASP-helpful-links" target="_blank"><?php _e( 'Get support for custom condition development', 'woocommerce-advanced-shipping-pro' ); ?></a></li>
		</ul><?php

	}


	/**
	 * Save settings meta box.
	 *
	 * Validate and save post meta from settings meta box.
	 *
	 * @since 1.0.0
	 */
	public function save_meta( $post_id ) {

		if ( ! isset( $_POST['wcasp_settings_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wcasp_settings_meta_box_nonce'], 'wcasp_settings_meta_box' ) ) :
			return $post_id;
		endif;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) :
			return $post_id;
		endif;

		if ( ! current_user_can( 'manage_woocommerce' ) ) :
			return $post_id;
		endif;

		$shipping_method = array_map( 'sanitize_text_field', $_POST['_wcasp_shipping_method'] );
		update_post_meta( $post_id, '_wcasp_shipping_method', $shipping_method );

		// Save sanitized conditions
		update_post_meta( $post_id, '_wcasp_shipping_method_conditions', wpc_sanitize_conditions( $_POST['conditions'] ) );

	}


	/**
	 * Redirect trash.
	 *
	 * Redirect user after trashing a WCASP post.
	 *
	 * @since 1.0.0
	 */
	public function redirect_after_trash() {

		$screen = get_current_screen();

		if ( 'edit-wcasp' == $screen->id ) :

			if ( isset( $_GET['trashed'] ) &&  intval( $_GET['trashed'] ) > 0 ) :

				wp_redirect( admin_url( '/admin.php?page=wc-settings&tab=shipping&section=wcasp_shipping_method' ) );
				exit();

			endif;

		endif;

	}


}

/**
 * Load condition object
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/class-wcasp-condition.php';