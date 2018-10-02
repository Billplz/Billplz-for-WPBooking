<?php
/**
 * Plugin Name: Billplz for WP Booking
 * Plugin URI: https://github.com/billplz/billplz-for-wpbooking/
 * Description: Billplz Payment Gateway | <a href="https://www.billplz.com/join/8ant7x743awpuaqcxtqufg" target="_blank">Sign up Now</a>.
 * Author: Billplz Sdn. Bhd.
 * Author URI: http://github.com/billplz/billplz-for-wpbooking/
 * Version: 3.0.0
 * Requires PHP: 5.6
 * Requires at least: 4.6
 * License: GPLv3
 * Text Domain: bwp
 * Domain Path: /languages/
 */

function bwp_load()
{
    if (class_exists('WPBooking_Abstract_Payment_Gateway')) {
        require 'includes/Billplz_API.php';
        require 'includes/Billplz_WPConnect.php';
        require 'includes/WPBooking_Billplz_Gateway.php';
    }
}

add_action('init', 'bwp_load', 0);
// Ref: http://shinetheme.com/demosd/documentation/wpbooking/?p=51
