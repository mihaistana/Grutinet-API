<?php 

// Le indicamos a PHP que no muestre los Notices (por si el servicio no retorna datos)
// (esto se puede evitar preguntando si hay datos antes de mostrarlos)
// error_reporting(1);
error_reporting(E_ALL);

function get_grutinet_stock_api() {
	global $wpdb;

	// Make sure the PHP-Soap module is installed
	echo "<h3>Comprobacion si existe el modulo SoapClient:</h3> <br>";
	if (!class_exists( 'SoapClient' ) ) {
		die ("<b style='color:red;'>No tienes instalado el módulo de PHP-Soap.</b> Quizás tengas que hablar con tu proveedor de hosting");
	} else {
		echo " SI que lo tienes instalado <br /> \n";
	}

	// URL de consumo de datos y parámetros
 	$wsdl  = "http://www.grutinetpro.com/apps/articulos/Ficheros.asmx?WSDL";
	$parameters = array(	'trace'		  => true,
							'Usuario'	  => '',
							'Password'	  => ''
			);

	$client = new SoapClient( $wsdl, $parameters );

	$response = $client->ListaStockVariosD($parameters);

// echo "METODOS DEL SERVICIO: <br />";
// echo "<pre>";
// var_dump( $client->__getFunctions() );
// echo "TIPOS<br />";
// var_dump( $client->__getTypes() ) . "\n";
// echo "</pre>";

	echo "Intentando la adquisicion de datos: <br />";
	// Recogemos los valores de los SKUs almacenados en la tabla de opciones.
	$sku_actuales = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_sku'" );
	$sku_introducidos = explode( ";", get_option( 'sku_array' ) );
	// Si no hay, recuperamos los que haya en WooCommerce actualmente
	$grupo_sku = ( isset( $sku_introducidos ) && $sku_introducidos[0] !== "" ) ? $sku_introducidos : $sku_actuales ;

	// try/catch de los datos del webservice
	try {

		$datos = array(
			'Usuario'	  => '',
			'Password'	  => '',
			'codigos'	  => $grupo_sku //array( "16952", "27822" )
			);

		$result = $client->ListaStockVariosD($datos);
		// $result = $client->ListaArticulosVariosD($datos); 
        // perform some logic, output the data to Asterisk, or whatever you want to do with it.
	 
	} catch (Exception $ex) {
		echo " <b style='color:red;'>ERROR:</b> " . $ex->faultcode . "<br /> \n"; 
		echo "<pre>";
		var_dump( $ex->faultcode, $ex->faultstring );
		echo "</pre>";
		
	}

// echo "<pre>";
// echo "RESULTADO:";
// var_dump($result);
// echo "</pre>";
	// Creamos el producto como post_type = product
	function insert_product( $producto ){
		global $wpdb;
		// Insertamos el post en la DDBB para poder añadirle después los postmetas
		$product_id = wp_insert_post( array(
			'post_title' 	=> (string)$producto->nombreproducto, 					// Nombre del puesto vacante
			'post_content'  => 'Descripcion',
			'post_status' 	=> 'pending',											// Publicado o en borrador
			'post_type' 	=> 'product',											// Tipo de post
		 	) 
		);

		// Añadimos los demás datos al producto
		switch ( $producto->iva ) {
			case '21.0000':
				$iva = "";
				break;
			
			case '10.0000':
				$iva = "reducido";
				break;

			case '04.0000':
				$iva = "super-reducido";
				break;

			default:
				$iva = "";
				break;
		}

		// Añadimos los postmetas vacios
		$j   = "";
		// numero de posicion donde se encuentra el primer punto
		$pos = strpos( $producto->iva, ".");
		// elimino el string a partir del punto
		$iva_perc = substr( $producto->iva, 0, $pos);
		$iva_cant = ($producto->pvprec) * ($iva_perc / 100);

		update_post_meta( $product_id, '_edit_last', $j );
		update_post_meta( $product_id, '_edit_lock', $j );
		update_post_meta( $product_id, 'Sola', $j );
		update_post_meta( $product_id, 'En Pareja', $j );
		update_post_meta( $product_id, '_visibility', $j );
		update_post_meta( $product_id, '_stock_status', $j );
		update_post_meta( $product_id, 'total_sales', $j );
		update_post_meta( $product_id, '_downloadable', $j );
		update_post_meta( $product_id, '_virtual', $j );
		update_post_meta( $product_id, '_regular_price', $producto->pvprec - $iva_cant ); 
		update_post_meta( $product_id, '_sale_price', $j );
		update_post_meta( $product_id, 'rrp_price', $producto->prusu );
		update_post_meta( $product_id, '_purchase_note', $j );
		update_post_meta( $product_id, '_featured', $j );
		update_post_meta( $product_id, '_weight', $j );
		update_post_meta( $product_id, '_length', $j );
		update_post_meta( $product_id, '_width', $j );
		update_post_meta( $product_id, '_height', $j );
		update_post_meta( $product_id, '_sku', $producto->sku );
		update_post_meta( $product_id, '_product_attributes', $j );
		update_post_meta( $product_id, '_sale_price_dates_from', $j );
		update_post_meta( $product_id, '_sale_price_dates_to', $j );
		update_post_meta( $product_id, '_price', $producto->pvprec );
		update_post_meta( $product_id, '_sold_individually', $j );
		update_post_meta( $product_id, '_manage_stock', 'yes' );
		update_post_meta( $product_id, '_backorders', $j );
		update_post_meta( $product_id, '_stock', $producto->stock );
		update_post_meta( $product_id, '_product_image_gallery', $j );
		update_post_meta( $product_id, '_thumbnail_id', $j );
		update_post_meta( $product_id, '_crosssell_ids', $j );
		update_post_meta( $product_id, '_product_version', $j );
		update_post_meta( $product_id, 'slide_template', $j );
		update_post_meta( $product_id, '_product_layout', $j );
		update_post_meta( $product_id, '_upsell_ids', $j );
		update_post_meta( $product_id, '_default_attributes', $j );
		update_post_meta( $product_id, '_min_variation_price', $j );
		update_post_meta( $product_id, '_max_variation_price', $j );
		update_post_meta( $product_id, '_min_price_variation_id', $j );
		update_post_meta( $product_id, '_max_price_variation_id', $j );
		update_post_meta( $product_id, '_min_variation_regular_price', $j );
		update_post_meta( $product_id, '_max_variation_regular_price', $j );
		update_post_meta( $product_id, '_min_regular_price_variation_id', $j );
		update_post_meta( $product_id, '_max_regular_price_variation_id', $j );
		update_post_meta( $product_id, '_min_variation_sale_price', $j );
		update_post_meta( $product_id, '_max_variation_sale_price', $j );
		update_post_meta( $product_id, '_min_sale_price_variation_id', $j );
		update_post_meta( $product_id, '_max_sale_price_variation_id', $j );
		update_post_meta( $product_id, '_up_less_editor', $j );
		update_post_meta( $product_id, '_tax_status', $j );
		update_post_meta( $product_id, '_tax_class', $iva );
		
		if (!is_wp_error($product_id)) {
			return true;
		} else {
			return false;
		} // end if/else

	} // end function insert_product

		echo "<br />";

		foreach ( $result->ListaStockVariosDResult->Art as $obj ) {
		// foreach ( $result->ListaArticulosVariosDResult->ArticulosD as $obj ) {
			for ( $i=0; $i < count($obj); $i++ ) {
// echo "<pre>";
// var_dump($obj);
// echo "</pre>";
				// Conseguimos los IDs del producto y vemos si está ya en el sistema con su SKU para no duplicarlo
				$productSKU = $obj[$i]->Cod;
				$product_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_sku' AND meta_value = '$productSKU'");
					

				// Si no está ya el ID del producto como post_meta, añadimos el producto
				if (!$product_id) {
					echo "No estaba este producto [" . $productSKU . "] en el sistema<br />";
					// Guardamos los resultados en variables manejables
				    $producto = new stdClass;

				    $producto->nombreproducto 	= $obj[$i]->Nombre;
				    $producto->sku 				= $obj[$i]->Cod;			// SKU
				    $producto->stock 			= $obj[$i]->Stock;
				    $producto->iva 				= $obj[$i]->IVA;
				    $producto->prusu 			= $obj[$i]->PrUsu;			// Lo que nos cuesta a nosotros
				    $producto->pvprec 			= $obj[$i]->PVPRec;			// Precio recomendado CON IVA incluido
				    $producto->pvprecesp 		= $obj[$i]->PVPRecEsp;		// Creo que es para ofertas
				    $producto->dtopvprecesp		= $obj[$i]->DtoPVPRecEsp;	// Lo que le quitan durante las ofertas
				    $producto->promodesde 		= $obj[$i]->PromoDesde;
				    $producto->promohasta 		= $obj[$i]->PromoHasta;
				    $producto->estadoacc 		= $obj[$i]->EstadoAcc;		// Promocion o Normal

				    // Utilizamos la función que hemos creado insert_product
					insert_product( $producto );
					echo "Insertando " . $producto->nombreproducto . "<br />";

				} else {
					echo "<b style='color:red;'>Producto ya existente - </b>" . get_the_title( $product_id ) . " - ID: " . $product_id . " - SKU: " . $productSKU . "<br>";
				} // end if/else
			} // end for
		} // end foreach


	echo '<br /><h4><b style="color:red;">No olvide <a href="/shop/wp-admin/edit.php?post_status=pending&post_type=product&paged=1" >editar la descripción</a></b>, los campos personalizados y publicar los productos nuevos</h4><br /><br />';

	echo "<b style='color:green;'>Script completado!! </b><br /><br />\n\n";
}


// update_field( '_stock', $product->Stock, $product_id );

// do_action( 'woocommerce_product_options_stock_status' );

// $product->get_stock_quantity();

// set_stock( $cantidad, 'set' );

// $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = %f WHERE post_id = %d AND meta_key='_stock'", $amount, $post->id ) );