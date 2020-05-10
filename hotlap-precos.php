<?php
/**
 * Plugin Name: Hotlap da Villa - Lógica de Ingressos
 * Plugin URI: https://www.austersoftware.com
 * Description: Plugin cuja função inicial era apenas aplicar um desconto para os competidores que comprarem ingressos em pacote, mas que agora controla diversas particularidades da bilheteria virtual do Hotlap da Villa. Se tiver duvidas, drop me a mail: ejneto@austersoftware.com
 * Version: 2.0
 * Author: Estevao J. Neto / Auster Software
 * Author URI: https://austersoftware.com
 */
add_action( 'woocommerce_view_order', 'show_qr_code' );
add_action( 'woocommerce_before_cart', 'main_product_loop' );
function show_qr_code( $order_id ){
    // Get an instance of the `WC_Order` Object
    $order = wc_get_order( $order_id );
	//var_dump($order);
    // Get the order number
    $order_id  = $order->get_order_number();

    // Get the formatted order date created
    $order_id  = wc_format_datetime( $order->get_date_created() );

    // Get the order status name
    $order_id  = wc_get_order_status_name( $order->get_status() );
	$data = "Numero_Ingresso:".$order->get_order_number()."|Status_Pgto:".wc_get_order_status_name( $order->get_status() )."|CPF:";
    // Display the order status 
    echo '<p>QR Code de verificação:</p><p><img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='.$data.'" alt="qrcode"></p>';
}

function main_product_loop(){
	//for the $prods below: Event ID (as per WP Events Manager) => priority (who overwrites who in the cart checkout)
	$prods_hotlap = [1032 => 99,  1064 => 2, 1056 => 1, 1058 => 1];
	$prods_drift = [1051 => 2, 1066 => 1, 1067 => 1];
	// $prod_types: 0 means Hotlap; 1 means Drift; hotlap events can only overrule hotlap events, and drift events can only overrule drift events
	$prod_types = [1032 => 0, 1064 => 0, 1056 => 0, 1058 => 0,
				  1051 => 1, 1066 => 1, 1067 => 1];
	
	foreach($prods_hotlap as $prod_key => $prod_priority)
		if($prod_priority > 1)
			check_product_categories_in_cart($prods_hotlap, $prod_key, $prod_types);
	foreach($prods_drift as $prod_key => $prod_priority)
		if($prod_priority > 1)
			check_product_categories_in_cart($prods_drift, $prod_key, $prod_types);
}
function check_product_categories_in_cart($product_list, $combo_id, $prod_types){	
	$prod_priority = $product_list[$combo_id];
	$replaced = false;
	$product_cart_id = WC()->cart->generate_cart_id( $combo_id );
	$in_cart = WC()->cart->find_product_in_cart( $product_cart_id ); 
	if($in_cart){
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if($product_list[$cart_item['product_id']] < $prod_priority && $prod_types[$cart_item['product_id']] == $prod_types[$combo_id]){
				WC()->cart->remove_cart_item( $cart_item_key );			
				$replaced = true;
				// purely for debugging purposes:
				//wc_print_notice( $product_list[$cart_item['product_id']]." is lower than ".$prod_priority, 'notice' );
			}
		}
	}
	if ($replaced){
		$notice = 'Você está levando um combo de ingressos! Nós retiramos os ingressos individuais do carrinho - eles já estão inclusos no combo, que inclusive é mais barato ;)';
      	wc_print_notice( $notice, 'notice' );
	}
}
