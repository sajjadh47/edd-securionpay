<?php
/**
 * This file contains the definition of the Edd_Securionpay class, which
 * is used to begin the plugin's functionality.
 *
 * @package       Edd_Securionpay
 * @subpackage    Edd_Securionpay/includes
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since    2.0.0
 */
class Edd_Securionpay {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       Edd_Securionpay_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function __construct() {
		$this->version     = defined( 'EDD_SECURIONPAY_PLUGIN_VERSION' ) ? EDD_SECURIONPAY_PLUGIN_VERSION : '1.0.0';
		$this->plugin_name = 'edd-securionpay';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Edd_Securionpay_Loader.  Orchestrates the hooks of the plugin.
	 * - Edd_Securionpay_i18n.    Defines internationalization functionality.
	 * - Edd_Securionpay_Admin.   Defines all hooks for the admin area.
	 * - Edd_Securionpay_Public.  Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once EDD_SECURIONPAY_PLUGIN_PATH . 'includes/class-edd-securionpay-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once EDD_SECURIONPAY_PLUGIN_PATH . 'includes/class-edd-securionpay-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once EDD_SECURIONPAY_PLUGIN_PATH . 'admin/class-edd-securionpay-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once EDD_SECURIONPAY_PLUGIN_PATH . 'public/class-edd-securionpay-public.php';

		$this->loader = new Edd_Securionpay_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Edd_Securionpay_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function set_locale() {
		$plugin_i18n = new Edd_Securionpay_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Edd_Securionpay_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugin_action_links_' . EDD_SECURIONPAY_PLUGIN_BASENAME, $plugin_admin, 'add_plugin_action_links' );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

		$this->loader->add_filter( 'edd_payment_gateways', $plugin_admin, 'add_payment_gateway' );
		$this->loader->add_filter( 'edd_settings_gateways', $plugin_admin, 'add_payment_gateway_settings' );
		$this->loader->add_action( 'edd_gateway_' . EDD_SECURIONPAY_GATEWAY_NAME, $plugin_admin, 'process_purchase' );
		$this->loader->add_action( 'edd_view_order_details_before', $plugin_admin, 'refund_admin_js', 100 );
		$this->loader->add_action( 'edd_pre_refund_payment', $plugin_admin, 'pre_refund_payment', 100 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 */
	private function define_public_hooks() {
		$plugin_public = new Edd_Securionpay_Public( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    Edd_Securionpay_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @return    string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Helper function to convert amount to minor unit
	 *
	 * Charge amount in minor units of given currency.
	 * For example 10€ is represented as "1000" and 10¥ is represented as "10".
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     float|int $amount   The amount to convert.
	 * @param     string    $currency The currency of the amount.
	 * @return    float|int $amount   Modified converted amount.
	 */
	public static function get_amount( $amount, $currency ) {
		$currency = strtoupper( $currency );

		// Currencies that do not require conversion.
		// if it's Chinese yuan (¥) or japanese yen then no amount conversion.
		$no_conversion_currencies = array(
			'JPY',
			'BIF',
			'CLP',
			'DJF',
			'GNF',
			'ISK',
			'KMF',
			'KRW',
			'PYG',
			'RWF',
			'UGX',
			'UYI',
			'XAF',
		);

		if ( in_array( $currency, $no_conversion_currencies, true ) ) {
			return $amount;
		}

		return $amount * 100;
	}
}
