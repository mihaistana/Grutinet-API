<?php 
/*
Plugin Name: Grutinet API
Plugin URI: http://joselazo.es/plugins
Description: Conexión API con Grutinet para la consulta de stock de productos
Author: Jose Lazo
Author URI: http://joselazo.es
Version: 0.1
*/

add_action( 'admin_menu', 'grutinetapi_menu' );
add_action( 'admin_init', 'update_webservice_info' );

function grutinetapi_menu() {
	add_menu_page( 'grutinetapi', 'Grutinet API', 'manage_options', 'connectoapi', 'grutinetapi_core');
	add_submenu_page( 'connectoapi', 'Get Grutinet Stock', 'Get Grutinet Stock', 'manage_options', 'get-grutinet-stock', 'get_grutinet_stock_api' );
	//add_submenu_page( 'connectoapi', 'hello request', 'hello request', 'manage_options', 'hello_request', 'hello_request' );
}

function grutinetapi_core() {
	?>
	<div class = "wpbody-content">
		<p>Sincronizar ahora<br />
		<ul>
			<li><a class="button" href="admin.php?page=get-grutinet-stock">Get Grutinet Stock</a></li>
			<!-- <li><a href="admin.php?page=hello_request">Hello Request</a></li> -->
		</ul>
	</div>
	<form method="post" action="options.php" id="sku">
			<?php settings_fields( 'webservice-settings' ); ?>
    		<?php do_settings_sections( 'webservice-settings' ); ?>
			<fieldset>
				<p>Introduce los SKUs de los productos que quieras crear<br />
					<input type="text" name="sku_array" value="<?php echo get_option('sku_array'); ?>" >
					<span class="small">Separa cada número con ";" (punto y coma)</span>
				<?php submit_button( 'Guardar cambios' ); ?>
			</fieldset>
		</form>
	<?php
}

//Guardamos los datos en la tabla wp_options
if( !function_exists("update_webservice_info") ) {
	function update_webservice_info() {
		register_setting( 'webservice-settings', 'sku_array' );
	}
}

// Aquí actualizamos el stock y el precio del producto al cargarlo
require('stock_request.php');
// Aquí obtenemos productos nuevos y los importamos
require('get_grutinet_stock_api.php');

// Añadimos el campo "Precio al por mayor"
add_action( 'woocommerce_product_options_pricing', 'wc_rrp_product_field' );
function wc_rrp_product_field() {
	woocommerce_wp_text_input( array( 'id' => 'rrp_price', 'class' => 'wc_input_price short', 'label' => __( 'Precio Grutinet (+IVA)', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
}

add_action( 'save_post', 'wc_rrp_save_product' );
function wc_rrp_save_product( $product_id ) {
	// If this is a auto save do nothing, we only save when update button is clicked
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( isset( $_POST['rrp_price'] ) ) {
		update_post_meta( $product_id, 'rrp_price', $_POST['rrp_price'] );
	}
}
?>