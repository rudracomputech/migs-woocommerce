<?php
class WC_Gateway_Migs_Request {
    /**
     * Pointer to gateway making the request.
     * @var WC_Gateway_Comm_Web
     */
    protected $gateway;
    /**
     * Endpoint for requests from Comm_Web.
     * @var string
     */
    public $require_cvv;
    public $merchant_id;
    public $access_code;
    public $secret_hash;
    protected $notify_url;
    protected $log_mode;
    protected $TAG = 'COMM_WEB: ';
	
	
    /**
     * Constructor.
     * @param WC_Gateway_Comm_Web $gateway
     */
    function __construct($gateway) {
        $this->gateway    = $gateway;
        $this->notify_url = WC()->api_request_url( 'WC_Gateway_Comm_Web' );
        $this->log_mode = ( $gateway->logs == "yes" ) ? true : false;
		$this->require_cvv = true;
		$this->merchant_id = $this->gateway->merchant_id;
		$this->access_code = $this->gateway->access_code;
		$this->secret_hash = $this->gateway->secret_hash;
    }
    /**
     * Get the CommWeb request URL for an order.
     * @param  WC_Order $order
     * @return string
     */
    public function get_request_url( $order) {
        $order = new WC_Order( $order );
        $orderTotal = $order->get_total() * 100;
        $orderID = $order->id;
        // Get admin options
        $merchantID = $this->gateway->merchant_id;
        $access_code = $this->gateway->access_code;
        $md5HashData = $this->gateway->secret_hash;
        $secret_hash = $this->gateway->secret_hash;
        $vpc_ReturnURL = $this->notify_url;
        // Set request URL
        $vpcURL = 'https://migs.mastercard.com.au/vpcpay' . '?';
        $vpc_MerchTxnRef = 'woo-payment';
        $data = array(
            'vpc_Version' => '1',
           'vpc_Locale' => 'en',
			 'vpc_Command' => 'pay',
			'vpc_AccessCode' => $access_code,
			'vpc_MerchTxnRef' => $vpc_MerchTxnRef,
			'vpc_Merchant' => $merchantID,
			'vpc_OrderInfo' => 'woo-order_'.$orderID,
			'vpc_Amount' => $orderTotal,
			'vpc_ReturnURL' => $vpc_ReturnURL
			//'vpc_Currency' => get_woocommerce_currency()
        );
        ksort ($data);
        // set a parameter to show the first pair in the URL
        $appendAmp = 0;
      /*  foreach($data as $key => $value) {
			 if(in_array($key, array('vpc_SecureHash', 'vpc_SecureHashType'))) continue;
            // create the md5 input and URL leaving out any fields that have no value
            if (strlen($value) > 0) {
                // this ensures the first paramter of the URL is preceded by the '?' char
                if ($appendAmp == 0) {
                    $vpcURL .= $key . '=' . $value;
                    $appendAmp = 1;
                } else {
                    $vpcURL .= '&' . $key . "=" . $value;
                }
                $md5HashData .= $value;
            }
        } 
        // Create the secure hash and append it to the Virtual Payment Client Data if
        // the merchant secret has been provided.
        if (strlen($md5HashData) > 0) {
            //$vpcURL .= "&vpc_SecureHash=" . strtoupper(md5($md5HashData));
            $vpcURL .= "&vpc_SecureHash=" . strtoupper(hash_hmac('SHA256',$md5HashData,pack('H*',$access_code)))."&vpc_SecureHashType=SHA256";
        } */
	$sha256 = "";	
	ksort ( $data );
	foreach($data as $key => $value) {	
		if ( strlen( $value ) > 0 ) {
			if ( $appendAmp == 0 ) {
				$vpcURL .=  $key  . '=' .  $value ;
				$appendAmp = 1;
			} else {
				$vpcURL .= '&' .  $key  . "=" .  $value ;
			}
			$sha256 .= $key ."=". $value ."&";
		}
	}
	$sha256 = rtrim($sha256,"&");
	$vpcURL .= "&vpc_SecureHash=". strtoupper( hash_hmac( 'SHA256', $sha256, pack( "H*", $secret_hash ) ) );
	$vpcURL .= "&vpc_SecureHashType=SHA256";
		#die($vpcURL);
        return $vpcURL;
    }
	
	public function get_merchant_url (){
		
		return $vpcURL = 'https://migs.mastercard.com.au/vpcdps';
		
	}
}
