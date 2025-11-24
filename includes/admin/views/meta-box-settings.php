<?php
/**
 * WCASP meta box settings.
 *
 * Display the shipping settings in the meta box.
 *
 * @author		Your Name
 * @package		WooCommerce Advanced Shipping Pro
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wp_nonce_field( 'wcasp_settings_meta_box', 'wcasp_settings_meta_box_nonce' );

global $post;
$settings                   = (array) get_post_meta( $post->ID, '_wcasp_shipping_method', true );
$settings['shipping_title'] = ! empty( $settings['shipping_title'] ) ? $settings['shipping_title'] : '';

?><div class='wcasp wcasp_settings wcasp_meta_box wcasp_settings_meta_box'>

	<p class='wcasp-option'>

		<label for='shipping_title'><?php _e( 'Shipping title', 'woocommerce-advanced-shipping-pro' ); ?></label>
		<input
			type='text'
			id='shipping_title'
			name='_wcasp_shipping_method[shipping_title]'
			value='<?php echo esc_attr( $settings['shipping_title'] ); ?>'
			placeholder='<?php _e( 'e.g. Advanced Shipping', 'woocommerce-advanced-shipping-pro' ); ?>'
		>

	</p>

</div>