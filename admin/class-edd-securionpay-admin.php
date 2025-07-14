<?php
/**
 * This file contains the definition of the Edd_Securionpay_Admin class, which
 * is used to load the plugin's admin-specific functionality.
 *
 * @package       Edd_Securionpay
 * @subpackage    Edd_Securionpay/admin
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

use SecurionPay\SecurionPayGateway;
use SecurionPay\Exception\SecurionPayException;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    2.0.0
 */
class Edd_Securionpay_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since     2.0.0
	 * @access    private
	 * @var       string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     string $plugin_name The name of this plugin.
	 * @param     string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Adds a settings link to the plugin's action links on the plugin list table.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     array $links The existing array of plugin action links.
	 * @return    array $links The updated array of plugin action links, including the settings link.
	 */
	public function add_plugin_action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways' ) ), __( 'Settings', 'edd-securionpay' ) );

		return $links;
	}

	/**
	 * Displays admin notices in the admin area.
	 *
	 * This function checks if the required plugin is active.
	 * If not, it displays a warning notice and deactivates the current plugin.
	 *
	 * @since     2.0.0
	 * @access    public
	 */
	public function admin_notices() {
		// Check if required plugin is active.
		if ( ! class_exists( 'Easy_Digital_Downloads', false ) ) {
			sprintf(
				'<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a> %s</p></div>',
				__( 'Payment Gateway for EDD - SecurionPay requires', 'edd-securionpay' ),
				esc_url( 'https://wordpress.org/plugins/easy-digital-downloads/' ),
				__( 'Easy Digital Downloads', 'edd-securionpay' ),
				__( 'plugin to be active!', 'edd-securionpay' ),
			);

			// Deactivate the plugin.
			deactivate_plugins( EDD_SECURIONPAY_PLUGIN_BASENAME );
		}
	}

	/**
	 * Adds SecurionPay as a payment gateway in Easy Digital Downloads (EDD).
	 *
	 * This function adds SecurionPay to the list of available
	 * payment gateways in EDD. It defines the admin label and checkout label
	 * for the gateway.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     array $gateways An array of EDD payment gateways.
	 * @return    array           An array of EDD payment gateways with SecurionPay added.
	 */
	public function add_payment_gateway( $gateways ) {
		$gateways[ EDD_SECURIONPAY_GATEWAY_NAME ] = array(
			'admin_label'    => __( 'SecurionPay', 'edd-securionpay' ),
			'checkout_label' => __( 'SecurionPay', 'edd-securionpay' ),
		);

		return $gateways;
	}

	/**
	 * Adds SecurionPay settings to the EDD settings array.
	 *
	 * This function adds custom settings for the SecurionPay
	 * payment gateway to the EDD settings page. These settings include the
	 * API secret key.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     array $settings An array of EDD settings.
	 * @return    array           An array of EDD settings with SecurionPay settings added.
	 */
	public function add_payment_gateway_settings( $settings ) {
		$edd_securionpay_gateway_settings = array(
			array(
				'id'   => 'edd_securionpay_gateway_heading',
				'type' => 'header',
				'name' => __( 'SecurionPay Payment Gateway', 'edd-securionpay' ),
			),
			array(
				'id'   => 'edd_securionpay_api_key',
				'type' => 'text',
				'name' => 'API Secret Key',
				'size' => 'regular',
				'desc' => __( 'You can find you Secret Key in <a href="https://securionpay.com/account-settings#api-keys">your account settings</a> once you login to your account.', 'edd-securionpay' ),
			),
		);

		return array_merge( $settings, $edd_securionpay_gateway_settings );
	}

	/**
	 * Process the payment for an EDD order using SecurionPay.
	 *
	 * This function handles the payment processing for an EDD order. It
	 * retrieves the order details, processes the payment through SecurionPay,
	 * and updates the order status accordingly.
	 *
	 * @since     2.0.0
	 * @access    public
	 * @param     array $purchase_data The order to process the payment for.
	 */
	public function process_purchase( $purchase_data ) {
		// check for nonce to skip security vulnerability.
		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
			wp_die(
				esc_html__( 'Nonce verification has failed', 'edd-securionpay' ),
				esc_html__( 'Error', 'edd-securionpay' ),
				array( 'response' => 403 )
			);
		}

		global $edd_options;

		// load the securionpay library [https://github.com/securionpay/securionpay-php].
		require EDD_SECURIONPAY_PLUGIN_PATH . '/vendor/autoload.php';

		// make up the data to add a pending payment order first.
		$payment_data = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => edd_get_currency(),
			'downloads'    => $purchase_data['downloads'],
			'user_info'    => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'status'       => 'pending',
		);

		// record the pending payment.
		$payment_id = edd_insert_payment( $payment_data );

		// if couldn't process exit and get back to checkout again.
		if ( empty( $payment_id ) ) {
			edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
		}

		// if not API key set then no going forward.
		if ( empty( $edd_options['edd_securionpay_api_key'] ) ) {
			// send user to payment failed page.
			wp_safe_redirect( get_permalink( $edd_options['failure_page'] ) );
		}

		// SecurionPay API key.
		$api_key = $edd_options['edd_securionpay_api_key'];

		// amount to charge.
		$amount = Edd_Securionpay::get_amount( $purchase_data['price'], edd_get_currency() );

		// success page to redirect after payment processed successfully.
		$callback = get_permalink( $edd_options['success_page'] );

		// initiate the SecurionPay gateway library class.
		$gateway = new SecurionPayGateway( $api_key );

		// make up the data to send to Gateway API.
		$request = array(
			'amount'   => $amount,
			'currency' => edd_get_currency(),
			'card'     => array(
				'cardholderName' => $purchase_data['card_info']['card_name'],
				'number'         => $purchase_data['card_info']['card_number'],
				'cvc'            => $purchase_data['card_info']['card_cvc'],
				'expMonth'       => $purchase_data['card_info']['card_exp_month'],
				'expYear'        => $purchase_data['card_info']['card_exp_year'],
				'addressLine1'   => $purchase_data['card_info']['card_address'],
				'addressCity'    => $purchase_data['card_info']['card_city'],
				'addressState'   => $purchase_data['card_info']['card_state'],
				'addressZip'     => $purchase_data['card_info']['card_zip'],
				'addressCountry' => $purchase_data['card_info']['card_country'],
			),
		);

		// go for it... charge the amount and see if it has any chance.
		try {
			// the charge object after successfully charging a card.
			// do something with charge object - see https://securionpay.com/docs/api#charge-object.
			$charge = $gateway->createCharge( $request );

			// charge id will be used as TransactionID for reference.
			$charge_id = $charge->getId();

			// Saves transaction id.
			edd_insert_payment_note( $payment_id, '_edd_securionpay_transaction_id', $charge_id );
			edd_insert_payment_note( $payment_id, __( 'Transaction ID : ', 'edd-securionpay' ) . $charge_id );

			// now we better empty the cart.
			edd_empty_cart();

			// we change the payment status as completed.
			edd_update_payment_status( $payment_id, 'publish' );

			edd_send_to_success_page();
		} catch ( SecurionPayException $e ) {
			// something went wrong buddy.
			// handle error response - see https://securionpay.com/docs/api#error-object.
			$error_message = $e->getMessage();

			// add the error message to the order.
			edd_insert_payment_note( $payment_id, $error_message );

			// change the order as failed.
			edd_update_payment_status( $payment_id, 'failed' );

			// redirect.
			wp_safe_redirect( get_permalink( $edd_options['failure_page'] ) );
		}
	}

	/**
	 * Shows checkbox to automatically refund payments made in Securionpay.
	 *
	 * @since    2.0.0
	 * @access   public
	 * @param    int $payment_id The current payment ID.
	 */
	public function refund_admin_js( $payment_id = 0 ) {
		global $edd_options;

		// If not the proper gateway, return early.
		if ( EDD_SECURIONPAY_GATEWAY_NAME !== edd_get_payment_gateway( $payment_id ) ) {
			return;
		}

		// If our credentials (Secret key) are not set, return early.
		if ( empty( $edd_options['edd_securionpay_api_key'] ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ )
			{
				$( 'select[name=edd-payment-status]' ).change( function()
				{
					if ( 'refunded' == $( this ).val() )
					{
						$( this ).parent().parent().append( '<input type="checkbox" id="edd-securionpay-refund" name="edd-securionpay-refund" value="1" style="margin-top:0">' );
						$( this ).parent().parent().append( '<label for="edd-securionpay-refund"><?php esc_html_e( 'Refund Payment in Securionpay', 'edd-securionpay' ); ?></label>' );
					}
					else
					{
						$( '#edd-securionpay-refund' ).remove();
						
						$( 'label[for="edd-securionpay-refund"]' ).remove();
					}
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Possibly refunds a payment made with SecurionPay Gateway.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     EDD_Payment $payment The payment object.
	 */
	public function edd_maybe_refund_securionpay_purchase( EDD_Payment $payment ) {
		// check if current user can actually have the permission to process refund.
		// phpcs:ignore WordPress.WP.Capabilities.Unknown
		if ( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
			return;
		}

		// is it a refund request.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['edd-securionpay-refund'] ) ) {
			return;
		}

		// If the payment has already been refunded in the past, return early.
		$processed = $payment->get_meta( '_edd_securionpay_refunded', true );

		if ( $processed ) {
			return;
		}

		// If not Securionpay, return early.
		if ( EDD_SECURIONPAY_GATEWAY !== $payment->gateway ) {
			return;
		}

		// Process the refund in Securionpay.
		self::refund_purchase( $payment );
	}

	/**
	 * Refunds a purchase made via SecurionPay.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     object|int $payment The payment ID or object to refund.
	 */
	public static function refund_purchase( $payment ) {
		global $edd_options;

		// If our credentials (Secret key) are not set, return early.
		$api_key = empty( $edd_options['edd_securionpay_api_key'] ) ? '' : $edd_options['edd_securionpay_api_key'];

		if ( empty( $api_key ) ) {
			return;
		}

		// if not $payment is an intance of EDD_Payment class then create one quick.
		if ( ! $payment instanceof EDD_Payment && is_numeric( $payment ) ) {
			$payment = new EDD_Payment( $payment );
		}

		// the transaction_id we saved while checkout.
		$charge_id = $payment->get_meta( '_edd_securionpay_transaction_id', true );

		// if not set then no refund can happen buddy.
		if ( empty( $charge_id ) ) {
			return;
		}

		// load the securionpay library [https://github.com/securionpay/securionpay-php].
		require EDD_SECURIONPAY_PLUGIN_PATH . '/vendor/autoload.php';

		// initiate the SecurionPay gateway library class.
		$gateway = new SecurionPayGateway( $api_key );

		// make up the data to send to Gateway API.
		$request = array(
			'chargeId' => $charge_id,
		);

		// try to refund the amount.
		try {
			$refund = $gateway->refundCharge( $request );

			// do something with charge object - see https://securionpay.com/docs/api#charge-object.
			$charge_id = $refund->getId();
			$amount    = $refund->getAmount();
			$currency  = $refund->getCurrency();

			// Prevents the Securionpay gateway from trying to process the refund multiple times...
			$payment->update_meta( '_edd_securionpay_refunded', true );

			$payment->add_note(
				sprintf(
					/* translators: 1: Payment amount, 2: Payment currency */
					__( 'Securionpay successfully refunded %1$s %2$s', 'edd-securionpay' ),
					$amount,
					$currency
				)
			);
		} catch ( SecurionPayException $e ) {
			// handle error response - see https://securionpay.com/docs/api#error-object.
			$error_message = $e->getMessage();

			$payment->add_note(
				sprintf(
					/* translators: 1: Error message */
					__( 'Securionpay refund failed : %s', 'edd-securionpay' ),
					$error_message
				)
			);

			return false;
		}

		// Run hook letting people know the payment has been refunded successfully.
		do_action( 'edd_securionpay_refund_purchase', $payment );
	}
}
