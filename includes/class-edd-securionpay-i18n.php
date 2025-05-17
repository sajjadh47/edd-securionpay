<?php
/**
 * This file contains the definition of the Edd_Securionpay_I18n class, which
 * is used to load the plugin's internationalization.
 *
 * @package       Edd_Securionpay
 * @subpackage    Edd_Securionpay/includes
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since    2.0.0
 */
class Edd_Securionpay_I18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'edd-securionpay',
			false,
			dirname( EDD_SECURIONPAY_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
