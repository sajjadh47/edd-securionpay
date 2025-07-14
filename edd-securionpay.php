<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           Edd_Securionpay
 * @author            Sajjad Hossain Sagor <sagorh672@gmail.com>
 *
 * Plugin Name:       Payment Gateway For EDD - SecurionPay
 * Plugin URI:        https://wordpress.org/plugins/edd-securionpay/
 * Description:       Integrate SecurionPay payment gateway to your Easy Digital Downloads (EDD) store.
 * Version:           2.0.0
 * Requires at least: 5.6
 * Requires PHP:      8.0
 * Author:            Sajjad Hossain Sagor
 * Author URI:        https://sajjadhsagor.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       edd-securionpay
 * Domain Path:       /languages
 * Requires Plugins:  easy-digital-downloads
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'EDD_SECURIONPAY_PLUGIN_VERSION', '2.0.0' );

/**
 * Define Plugin Folders Path
 */
define( 'EDD_SECURIONPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'EDD_SECURIONPAY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'EDD_SECURIONPAY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Define Gateway Name (Used as ID).
define( 'EDD_SECURIONPAY_GATEWAY_NAME', 'edd_securionpay_gateway' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-edd-securionpay-activator.php
 *
 * @since    2.0.0
 */
function on_activate_edd_securionpay() {
	require_once EDD_SECURIONPAY_PLUGIN_PATH . 'includes/class-edd-securionpay-activator.php';

	Edd_Securionpay_Activator::on_activate();
}

register_activation_hook( __FILE__, 'on_activate_edd_securionpay' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-edd-securionpay-deactivator.php
 *
 * @since    2.0.0
 */
function on_deactivate_edd_securionpay() {
	require_once EDD_SECURIONPAY_PLUGIN_PATH . 'includes/class-edd-securionpay-deactivator.php';

	Edd_Securionpay_Deactivator::on_deactivate();
}

register_deactivation_hook( __FILE__, 'on_deactivate_edd_securionpay' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since    2.0.0
 */
require EDD_SECURIONPAY_PLUGIN_PATH . 'includes/class-edd-securionpay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_edd_securionpay() {
	$plugin = new Edd_Securionpay();

	$plugin->run();
}

run_edd_securionpay();
