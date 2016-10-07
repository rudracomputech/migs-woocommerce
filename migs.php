<?php
/*
Plugin Name: Rudra Computech Migs Payment Gateway
Plugin URI: http://www.rudracomputech.com
Description: Rudra Computech Migs Mastercard Payment Gateway
Version: 1.0
Author: Rudra Computech 
Author URI: http://www.rudracomputech.com
License: GNU GPLv3
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function flyonet_migs() {
    if (!class_exists('WC_Payment_Gateway'))  return;
    class WC_Gateway_Comm_Web extends WC_Payment_Gateway {
        protected $TAG = 'COMM_WEB: ';
        function __construct() {
            // The global ID for this Payment method
            $this->id = "migs";
            // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
            $this->method_title = __( "Migs Mastercards ", 'migs' );
            // The description for this Payment Gateway, shown on the actual Payment options page on the backend
            $this->method_description = __( "Migs Mastercards. Cards accepted: Visa, Mastercard & MasterPass", 'migs' );
            // The title to be used for the vertical tabs that can be ordered top to bottom
            $this->title = __( 'Migs Mastercards', 'migs' );
            // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
            $this->icon = WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/visa-master.png';;
            // Bool. Can be set to true if you want payment fields to show on the checkout
			$this->method	= $this->get_option( 'method' );
			$this->use_paypage = $this->method == 'paypage' ? 1 : 0;
            // if doing a direct integration, which we are doing in this case
            if (!$this->use_paypage){
				$this->has_fields	= true;
				$this->supports[] = 'default_credit_card_form';
			}else
				$this->has_fields 	= false;
			
            // This basically defines your settings which are then loaded with init_settings()
            $this->init_form_fields();
            // After init_settings() is called, you can get the settings and load them into variables
            $this->init_settings();
            // Turn these settings into variables we can use
            foreach ( $this->settings as $setting_key => $value ) {
                $this->$setting_key = $value;
            }
            // Save admin options
            add_action( 'woocommerce_update_options_payment_gateways_' .
                $this->id, array( $this, 'process_admin_options' ) );
            // Add call back handler
            include_once( 'includes/WC_gateway_migs_response_handler.php' );
            new WC_gateway_migs_response_handler($this);
        }
        public function init_form_fields() {
            $this->form_fields = include( 'includes/settings_migs.php' );
        }
        // Submit payment and handle response
        public function process_payment( $order_id ) {
            include_once('includes/WC_Gateway_Migs_Request.php');
            $log_mode = ( $this->logs == "yes" ) ? true : false;
            $migs_request = new WC_Gateway_Migs_Request($this);
            $order = new WC_Order( $order_id );
			if ($this->use_paypage)
			{
				$vpcURL = $migs_request->get_request_url($order);
				if ( $log_mode ) { error_log($this->TAG . "Sending request to url: " . $vpcURL); }
				// Redirect to migs page
				return array(
					'result'   => 'success',
					'redirect' => "$vpcURL",
				);
			}
			else
			{
				// here needs to work
				 // Get admin options
			$merchantID = $migs_request->merchant_id;
			$access_code = $migs_request->access_code;
			$md5HashData = $migs_request->secret_hash;
			$orderTotal = $order->get_total() * 100;
			$orderID = $order->id;
			$vpc_MerchTxnRef = 'woo-payment';
				$vpcURL = $migs_request->get_merchant_url();
				$helcim_args = $this->params;
			$helcim_args['vpc_Version'] = '1';
			$helcim_args['vpc_Command'] = 'pay';
			$helcim_args['vpc_AccessCode'] = $access_code;
			$helcim_args['vpc_SecureHash'] = $md5HashData;
			$helcim_args['vpc_MerchTxnRef'] = $vpc_MerchTxnRef;
			$helcim_args['vpc_Merchant'] = $merchantID;
			$helcim_args['vpc_OrderInfo'] = 'woo-order_'.$orderID;
			$helcim_args['vpc_Amount'] = $orderTotal;
			$helcim_args = array_merge($helcim_args, $this->get_paypage_args($order));
			//print_r($helcim_args);die;
			$helcim_args_string = '';
			foreach ($helcim_args as $key=>$value)
				$helcim_args_string .= $key.'='.urlencode($value).'&';
			$http_params = array(
					'body' => $helcim_args_string,
					'method' => 'POST',
					'timeout' => 45,
					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
					'sslverify' => false
				);
			//$formFields['vpc_Currency'] = $this->getCurrencyCode();
				//die($vpcURL);
				$response_array = wp_remote_post($vpcURL, $http_params);
				//var_dump($response_array);
				//die("we are here");
				if(is_array(@$response_array)){ return $this->check_direct_response($response_array['body']); }
			}
        }
		/**
	 * Get payment page args
	 *
	 * @access public
	 * @param mixed $order
	 * @return array
	 */
	public function get_paypage_args( $order ) {
		global $woocommerce;
		$order_id = $order->id;
		// SET OPTIONAL TRANSACTION FIELDS
		$paypage_args = array(
						'cardholderAddress' => $order->billing_address_1.' '.$order->billing_address_2,
						'cardholderPostalCode' => $order->billing_postcode,
						'billingName' => $order->billing_first_name.' '.$order->billing_last_name,
						'billingAddress' => $order->billing_address_1.' '.$order->billing_address_2,
						'billingCity' => $order->billing_city,
						'billingProvince' => $order->billing_state,
						'billingPostalCode' => $order->billing_postcode,
						'billingCountry' => $order->billing_country,
						'billingPhoneNumber' => $order->billing_phone,
						'billingEmailAddress' => $order->billing_email,
						'shippingName' => $order->shipping_first_name.' '.$order->shipping_last_name,
						'shippingAddress' => $order->shipping_address_1.' '.$order->shipping_adress_2,
						'shippingCity' => $order->shipping_city,
						'shippingProvince' => $order->shipping_state,
						'shippingPostalCode' => $order->shipping_postcode,
						'shippingCountry' => $order->shipping_country,
						'comments' => $order->customer_note,
						'amount' => number_format($order->get_total(),2,'.',''),						
						);
		// get cart items
		$i = 1;
		foreach ($order->get_items() as $item) {
			$product = $order->get_product_from_item( $item );
			if (get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) { 
				$taxed = false;
			} else { 
				$taxed = false;
			}
			$paypage_args['itemId'.$i] = $product->get_sku();
			$paypage_args['itemDescription'.$i] = $item['name'];
			$paypage_args['itemQuantity'.$i] = $item['qty'];
			$paypage_args['itemPrice'.$i] = $order->get_item_total($item, $taxed, false);
			$paypage_args['itemTotal'.$i] = $order->get_line_total($item, $taxed, false);			
		}
		// GET SHIPPING AND TAXES
		if(get_option( 'woocommerce_prices_include_tax' ) == 'yes' ){
			// SHIPPING HAS EXTRA TAXES
			$paypage_args['shippingAmount'] = number_format($order->get_total_shipping() + $order->get_shipping_tax(),2,'.','');
		}else{
			// NORMAL SHIPPING
			$paypage_args['shippingAmount'] = number_format($order->get_total_shipping(),2,'.','');
			$paypage_args['taxAmount'] = number_format($order->get_total_tax(),2,'.','');
		}
		// SET ORDER ID
		$paypage_args['orderId'] = $order_id.'-'.rand(10000,99999);
		// RETURN ARGUMENTS
		return $paypage_args;
	}
		/**
		* Validate payment fields
		*
		* @access public
		* @return void
		*/
		public function validate_fields()
		{
			// SET GLOBAL VARS
			global $woocommerce;
			// CHECK FOR PAYMENT PAGE
			if($this->use_paypage){
				// SET
				$this->validated = 1;
				return;
			}
			// CHECK FOR MISSING FIELDS
			if(empty($_POST['migs-card-number']) || strlen($_POST['migs-card-number']) < 15){
				// SET NOTICE
				wc_add_notice('<b>Card number</b> must be 16 characters long.','error');
			}
			// CHECK FOR MISSING FIELDS
			if(empty($_POST['migs-card-expiry'])){
				// SET NOTICE
				wc_add_notice('<b>Expiry month</b> is a required field.','error');
			}
			// CHECK FOR MISSING FIELDS
			if(empty($_POST['migs-card-cvc'])){
				// SET NOTICE
				wc_add_notice('<b>Expiry year</b> is a required field.','error');
			}
			// CEHCK FOR CVV REQUIREMENT
			if($this->require_cvv){
				// CHECK FOR MISSING FIELDS
				if(empty($_POST['migs-card-cvc'])){
					// SET NOTICE
					wc_add_notice('<b>CVV2</b> is a required field.','error');
				}
			}
			// CHECK FOR NO ERRORS
			//if(!$woocommerce->error_count()){
			if(!wc_get_notices('error')){
				// SET VALUES
				
				$vpc_CardExp = explode('/',$_POST['migs-card-expiry']);
				$this->params['vpc_CardNum'] =  preg_replace('/\s+/', '', $_POST['migs-card-number']); //'5123456789012346';
				$this->params['vpc_CardExp'] = trim($vpc_CardExp[1]).trim($vpc_CardExp[0]); //'1705'; 
				$this->params['vpc_CardSecurityCode']	= $_POST['migs-card-cvc']; //'123';
				$this->validated 			= 1;
				
				//echo "<pre>";
				//print_r($this->params);die;
				
			}else{
				// NO VALID
				$this->validated = 0;
			}
		}
		/**
	 * Check for direct response
	 *
	 * @access public
	 * @return void
	 */
	public function check_direct_response($response_string){
		//echo "<pre>";
		//print_r($response_string);die;
			global $woocommerce;		
			@ob_clean();
			// CHANGE ENTERS TO &
			$response_string = str_replace("\r\n", '&', $response_string);
			parse_str($response_string, $response_array);
		//	echo "<pre>";
		//print_r($response_array);die;
			//$response = $this->parse_response($response_array);
			if ($response_array){
			$txnCode = isset($response_array['vpc_TxnResponseCode']) ? $response_array['vpc_TxnResponseCode'] : '';
		    // Process order
            switch ($txnCode) {
                case '0' :
                case '00' :
				// approved			
				$order_id = $response_array['vpc_OrderInfo'] ? $response_array['vpc_OrderInfo'] : '';
				// GET EXTRA ORDER ID NUMBERS
				$extraNum = substr($order_id, strpos($order_id, "-"));
				// SET REAL ORDER ID FOR WOOCOMMERCE
				$order_id = str_replace($extraNum,"", $order_id);
				if ($order_id) {			
					$order = new WC_Order( $order_id );
					$order->add_order_note( __('Commonwealth payment completed', 'woocommerce') .' (Approval Code: ' . $response->approvalCode . ')' );
					$order->payment_complete();
					$woocommerce->cart->empty_cart();
					$redirect = $this->get_return_url( $order );			
				} else {
					// error
				}
				//wp_redirect($redirect);
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url( $order )
				);
				 break;
                default :
				wc_add_notice('<b>Payment error:</b> '.$response_array['vpc_Message'].' - Please contact merchant and verify if payment was already completed.','error');
			}
			}
			else
			{
				wc_add_notice('<b>Payment error:</b> Please contact merchant and verify if payment was already completed.','error');
			}		
		}
    }
}
add_action('plugins_loaded', 'flyonet_migs');
function flyonet_add_comm_gateway_class( $methods ) {
    $methods[] = 'WC_Gateway_Comm_Web';
    return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'flyonet_add_comm_gateway_class' );