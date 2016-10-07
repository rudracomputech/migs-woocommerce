<?php

/*

Copyright 2016 Jorge Valdivia

This file is part of Comm-web plugin.

Comm-web plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Comm-web plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Comm-web.  If not, see <http://www.gnu.org/licenses/>.

*/


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Methods
$methods = array( 'paypage' => __('Hosted Payment Page', 'woocommerce'), 'direct' => __('Direct Integration') );
	
	
return array(
    'enabled' => array(
        'title' => __( 'Enable/Disable', 'migs' ),
        'type' => 'checkbox',
        'label' => __( 'Enable Migs payments', 'migs' ),
        'default' => 'no'
    ),
	'method' => array(
					'title' => __( 'Method', 'woocommerce' ),
					'type' => 'select',
					'options' => $methods,
					'description' => __('Choose between using the Migs Hosted Payment Page and direct Payment Gateway integration', 'woocommerce' ),
					'default' => 'paypage',
					'desc_tip' => true
				
	),		
			
    'title' => array(
        'title' => __( 'Title', 'migs' ),
        'type' => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'migs' ),
        'default' => __( 'Migs Payment Gateway', 'migs' ),
        'desc_tip'      => true,
    ),
    'description' => array(
        'title'		=> __( 'Description', 'migs' ),
        'type'		=> 'textarea',
        'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'migs' ),
        'default'	=> __( 'Pay securely using your credit card. Cards accepted: Visa, Mastercard & MasterPass', 'migs' ),
        'css'		=> 'max-width:350px;'
    ),
    'merchant_id' => array(
        'title'     => __( 'Merchant ID', 'migs' ),
        'type'      => 'textbox',
        'description' => __( 'Enter the Merchant ID provided by the bank.', 'migs' ),
    ),
    'access_code' => array(
        'title'     => __( 'Access Code', 'migs' ),
        'type'      => 'text',
        'description' => __( 'Enter the Access Code provided by the bank.', 'migs' ),
    ),
    'secret_hash' => array(
        'title'     => __( 'Secret Hash Secret', 'migs' ),
        'type'      => 'text',
        'description' => __( 'Enter the Secret Hash Secret provided by the bank.', 'migs' ),
        'default' => __( '', 'migs' ),
    ),
    'logs' => array(
        'title' => __( 'Enable Logs', 'migs' ),
        'type' => 'checkbox',
        'label' => __( 'Enable logs.', 'migs' ),
        'desc_tip'	=> __( "Enable to see the plugin logs in your php log file", 'migs' ),
        'default' => 'no'
    ),
);