<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Condition class.
 *
 * Represents a single condition in a condition group.
 *
 * @author  Your Name
 * @version 1.0.0
 */
class WCASP_Condition {


	/**
	 * Condition ID.
	 *
	 * @since 1.0.0
	 * @var string $id Condition ID.
	 */
	public $id;

	/**
	 * Condition.
	 *
	 * @since 1.0.0
	 * @var string $condition Condition slug.
	 */
	public $condition;

	/**
	 * Operator.
	 *
	 * @since 1.0.0
	 * @var string $operator Operator slug.
	 */
	public $operator;

	/**
	 * Value.
	 *
	 * @since 1.0.0
	 * @var string $value Condition value.
	 */
	public $value;

	/**
	 * Group ID.
	 *
	 * @since 1.0.0
	 * @var string $group Condition group ID.
	 */
	public $group;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null, $group = 0, $condition = 'subtotal', $operator = null, $value = null ) {

		$this->id        = $id;
		$this->group     = $group;
		$this->condition = $condition;
		$this->operator  = $operator;
		$this->value     = $value;

		if ( ! $id ) {
			$this->id = rand();
		}

	}


	/**
	 * Output condition row.
	 *
	 * Output the full condition row which includes: condition, operator, value, add/delete buttons and
	 * the description.
	 *
	 * @since 1.1.6
	 */
	public function output_condition_row() {

		$wp_condition = $this;
		require 'views/html-condition-row.php';

	}


	/**
	 * Get conditions.
	 *
	 * Get a list with the available conditions.
	 *
	 * @since 1.1.6
	 *
	 * @return  array  List of available conditions for a condition row.
	 */
	public function get_conditions() {

		$conditions = array(
			__( 'Cart', 'woocommerce-advanced-shipping-pro' ) => array(
				'subtotal'                => __( 'Subtotal', 'woocommerce-advanced-shipping-pro' ),
				'subtotal_ex_tax'         => __( 'Subtotal ex. taxes', 'woocommerce-advanced-shipping-pro' ),
				'tax'                     => __( 'Tax', 'woocommerce-advanced-shipping-pro' ),
				'quantity'                => __( 'Quantity', 'woocommerce-advanced-shipping-pro' ),
				'contains_product'        => __( 'Contains product', 'woocommerce-advanced-shipping-pro' ),
				'coupon'                  => __( 'Coupon', 'woocommerce-advanced-shipping-pro' ),
				'weight'                  => __( 'Weight', 'woocommerce-advanced-shipping-pro' ),
				'contains_shipping_class' => __( 'Contains shipping class', 'woocommerce-advanced-shipping-pro' ),
			),
			__( 'User Details', 'woocommerce-advanced-shipping-pro' ) => array(
				'zipcode' => __( 'Zipcode', 'woocommerce-advanced-shipping-pro' ),
				'city'    => __( 'City', 'woocommerce-advanced-shipping-pro' ),
				'state'   => __( 'State', 'woocommerce-advanced-shipping-pro' ),
				'country' => __( 'Country', 'woocommerce-advanced-shipping-pro' ),
				'role'    => __( 'User role', 'woocommerce-advanced-shipping-pro' ),
			),
			__( 'Product', 'woocommerce-advanced-shipping-pro' ) => array(
				'width'        => __( 'Width', 'woocommerce-advanced-shipping-pro' ),
				'height'       => __( 'Height', 'woocommerce-advanced-shipping-pro' ),
				'length'       => __( 'Length', 'woocommerce-advanced-shipping-pro' ),
				'stock'        => __( 'Stock', 'woocommerce-advanced-shipping-pro' ),
				'stock_status' => __( 'Stock status', 'woocommerce-advanced-shipping-pro' ),
				'category'     => __( 'Category', 'woocommerce-advanced-shipping-pro' ),
			),
		);
		$conditions = apply_filters( 'wcasp_conditions', $conditions );

		return $conditions;

	}


	/**
	 * Get available operators.
	 *
	 * Get a list with the available operators for the conditions.
	 *
	 * @since 1.1.6
	 *
	 * @return  array  List of available operators.
	 */
	public function get_operators() {
		$wpc_condition = wpc_get_condition( $this->condition );
		return apply_filters( 'woocommerce_advanced_shipping_pro_operators', $wpc_condition->get_available_operators() );
	}


	/**
	 * Get value field args.
	 *
	 * Get the value field args that are condition dependent. This usually includes
	 * type, class and placeholder.
	 *
	 * @since 1.1.6
	 *
	 * @return  array
	 */
	public function get_value_field_args() {

		// Defaults
		$default_field_args = array(
			'name'        => 'conditions[' . absint( $this->group ) . '][' . absint( $this->id ) . '][value]',
			'placeholder' => '',
			'type'        => 'text',
			'class'       => array( 'wpc-value' ),
		);

		$field_args = $default_field_args;
		if ( $condition = wpc_get_condition( $this->condition ) ) {
			$field_args = wp_parse_args( $condition->get_value_field_args(), $field_args );
		}

		if ( $this->condition == 'contains_product' && $product = wc_get_product( $this->value ) ) {
			$field_args['custom_attributes']['data-selected'] = $product->get_formatted_name(); // WC < 2.7
			$field_args['options'][ $this->value ] = $product->get_formatted_name(); // WC >= 2.7
		}

		$field_args = apply_filters( 'wcasp_values', $field_args, $this->condition );
		$field_args = apply_filters( 'woocommerce_advanced_shipping_pro_values', $field_args, $this->condition );

		return $field_args;

	}


	/**
	 * Get description.
	 *
	 * Return the description related to this condition.
	 *
	 * @since 1.0.0
	 */
	public function get_description() {
		$descriptions = apply_filters( 'wcasp_descriptions', wpc_condition_descriptions() );
		return isset( $descriptions[ $this->condition ] ) ? $descriptions[ $this->condition ] : '';
	}


}