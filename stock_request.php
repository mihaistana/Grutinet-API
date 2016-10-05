<?php 

function GetStock() {

	// Variables global y el SKU del producto en cuestión
	global $product;
	$productSKU = $product->get_sku();

 	// URL de consumo de datos y parámetros
 	$wsdl  = "http://www.grutinetpro.com/apps/articulos/Ficheros.asmx?WSDL";
	$parameters = array(	'trace'		  => true,
							'Usuario'	  => '',
							'Password'	  => ''
			);

	$client = new SoapClient( $wsdl, $parameters );

	// try/catch de los datos del webservice
	$_sku 	= array();
	$_sku[] = $productSKU;

	try {

		$datos = array(
			'Usuario'	  => '',
			'Password'	  => '',
			'codigos'	  => $_sku
			);

		$result = $client->ListaStockVariosD($datos);
	 
	} catch (Exception $ex) {
		// TO-DO pasar los errores a algún log en lugar de por pantalla
		echo " ERROR: " . $ex->faultcode . "<br /> \n"; 
		echo "<pre>";
		var_dump( $ex->faultcode, $ex->faultstring );
		echo "</pre>";
		
	}
	
// echo "<pre>";
// echo "RESULTADO:";
// var_dump($result);
// echo "</pre>";

	// Si no está ya el ID del producto como post_meta, añadimos el producto
	if ( $result ) {
// echo "Hay resultado del webservice";
		// Conseguimos el stock del webservice		
		$product_stock 	= $result->ListaStockVariosDResult->Art->Art->Stock;

		update_post_meta( $product->id, '_stock', $product_stock );
		// set_stock( $product_stock, $mode = 'set' );
	} else {
		echo "No se ha podido actualizar el stock"; // TO-DO cambiar el hhok de sitio más cerca de la cifra de stock
	}
}

// Trigger action 
// add_action( '', 'GetStock', 10, 2 );
add_action( 'woocommerce_before_single_product', 'GetStock', 10, 2 );

 ?>