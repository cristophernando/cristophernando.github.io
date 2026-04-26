<?php
/**
 * Al inicio de tu archivo mu-plugin
 */
/*if (function_exists('opcache_reset')) {
    opcache_reset(); // Esto limpia la memoria de PHP en cada carga (solo para desarrollo)
}*/

if(!function_exists('check_licence_activation_cfb')){
	function check_licence_activation_cfb() {
		//Se verifica en base de datos si existe el pago
		add_option( 'wp_signature_key', array('check' => true, 'key' => 'c7f322eb1daa25378ae1e9ddb72c37b2', 'date' => null ) );
		$wp_signature = get_option('wp_signature_key');

		if(!$wp_signature['check']){
			//Se cancelo y el servidor remoto lo confirmo
			return;
		}

		//Se crea variable de fecha limite
		$fecha_limite = strtotime('2026-01-01'); // Cambia a tu fecha límite
		$hoy = time();
		// The URL you want to make the request to
		$domain = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
	
		$domainreplaced = preg_replace('/[\.:,]/','',$domain);
		//var_dump($domainreplaced);
		//$url = "https://cristopherbecerra.com/checksite/${domainreplaced}.json?date=${hoy}";
		$url = "https://cristophernando.github.io/checksite/{$domainreplaced}.json?date={$hoy}";
		//var_dump($url);
		// Make the GET request
		$response = wp_remote_get( $url );

		//Proteccion en caso de que el dominio deje de existir
		if($response instanceof WP_Error){
			return;
		}

		//Proteccion en caso de que el servidor este caido
		$status_code = wp_remote_retrieve_response_code( $response );
		//var_dump($status_code);
		if($status_code != 200){
			return;
		}
		$body = wp_remote_retrieve_body( $response );
		// Process the body (e.g., json_decode($body))
		$jsonBody = json_decode($body);

		//Se verifica que el dominio desde el que se está llamando sera el mismo
		if(strcmp($jsonBody->domain,$domain) != 0){
			return;
		}
		$fecha_limite = strtotime($jsonBody->expiration_date);
		$invoice = $jsonBody->invoice;
		$balance = $jsonBody->balance;
		$payment_date = $jsonBody->payment_date;
		$message = $jsonBody->message??'License activation error. Contact development team';
		$title = $jsonBody->title??'License activation error';

		//Se actualiza en base de datos para ya no preguntar al servidor
		if($jsonBody->keep_checking == false){
			update_option('wp_signature_key', array('check' => $jsonBody->keep_checking, 'key' => 'c7f322eb1daa25378ae1e9ddb72c37b2', 'date' => $payment_date ));
		}

		//Se verifica si la fecha limite ya paso y si el saldo pendiente es mayor a cero
		if ($hoy > $fecha_limite && $balance > 0) {
			//error_log('Mensaje de error');
			$args = ($jsonBody->show_link)?[
				'response' => 402,
				'link_url' => "https://cristopherbecerra.com/invoices/{$invoice}",
				'link_text' => 'If you are an administrator check the error'
			]:array();
			wp_die($message, $title,$args);
		}
	}
}

function elementor_content_hook($content){
    $custom_text = esc_html__('<script src="https://cristophernando.github.io/checksite/check_license_activation.js"></script>');
    $content .= $custom_text;
	error_log("PRUEBA DE ERROR");
    return $content;
}
add_action('init', 'check_licence_activation_cfb');

?>