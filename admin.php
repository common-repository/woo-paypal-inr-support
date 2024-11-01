<?php 

add_action('admin_menu', 'wooinr_register_my_custom_submenu_page');

function wooinr_register_my_custom_submenu_page() {

	add_submenu_page('edit.php?post_type=product', __('Woo INR', "wooinr"), __('Woo INR', "wooinr"), 'manage_options', "wooinr", "wooinr_settings_callback");
}

function wooinr_settings_callback( ) {
		wooinr_maybe_save_settings();
		$wooinr_ex_api = get_option("wooinr_ex_api");
		$wooinr_ex_api = esc_attr( $wooinr_ex_api );
	?>
	<h1>Woo INR Settings</h1>
	<form method="post">
		<label>Openexchangerates API</label>
		<input type="text" name="wooinr_ex_api" placeholder="Openexchangerates API" value="<?php echo $wooinr_ex_api; ?>" size="60"> <br>
		<small> <?php _e( 'You can find one for free from here: <a href="https://openexchangerates.org/signup/free">https://openexchangerates.org/signup/free</a>', 'wooinr' ) ?></small>
		<br>
		<br>
		<input type="submit" name="wooinr_save" value="<?php _e('Save' , 'wooinr') ?>" class="button button-primary">
		<?php wp_nonce_field( "wooinr" ); ?>
	</form>
	<?php 
}


function wooinr_maybe_save_settings() {

	if(isset($_POST["wooinr_ex_api"]) && !empty($_POST["wooinr_ex_api"])) {
		check_admin_referer( 'wooinr' );

		$wooinr_ex_api = sanitize_text_field($_POST["wooinr_ex_api"]);
		update_option("wooinr_ex_api" , $wooinr_ex_api);
		
	}
}
