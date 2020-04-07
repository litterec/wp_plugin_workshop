<?php


/**
 * Plugin Name: DHL Shipping
 * Plugin URI: https://github.com/litterec/wp_plugin_workshop/blob/master/dhl-shipping
 * Description: Custom Shipping Method for WooCommerce
 * Version: 1.0.0
 * Author: Andrew Galagan
 * Author URI: https://galagan.ra-solo.ru/?lang=eng&showitem=1
 * Idea: I gor Benić http://www.ibenic.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: tutsplus
 */

if ( ! defined( 'WPINC' ) ) {

    die;

}

/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function dhl_shipping_method() {
        if ( ! class_exists( 'dhl_Shipping_Method' ) ) {
            class dhl_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'rasolo-dhl';
                    $this->method_title       = __( 'DHL Shipping', 'rasolo-dhl' );
                    $this->method_description = __( 'Custom Shipping Method for DHL', 'rasolo-dhl' );

                    $this->init();

                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'dhl Shipping', 'rasolo-dhl' );
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * Define settings field for this shipping
                 * @return void
                 */
                function init_form_fields() {

                    // We will add our settings here

                }

                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package ) {

                    // We will add the cost, rate and logics in here

                }
            }
        }
    }

    add_action( 'woocommerce_shipping_init', 'dhl_shipping_method' );

    function add_dhl_shipping_method( $methods ) {
        $methods[] = 'dhl_Shipping_Method';
        return $methods;
    }

    add_filter( 'woocommerce_shipping_methods', 'add_dhl_shipping_method' );
}
