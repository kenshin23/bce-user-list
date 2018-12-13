<?php

/**
 * The plugin area to view the usermeta
 */
	$bce_deactivate_user_nonce = wp_create_nonce( 'bce_deactivate_user_form_nonce' );

	if( current_user_can('edit_users' ) ) { ?>
		<h2> <?php echo __('Deactivate User: ' . $user->display_name . ' (' . $user->user_login . ')', $this->plugin_text_domain ); ?> </h2>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="bce_deactivate_user_form" >

			<p>Are you sure you want to deactivate user: '<?php echo $user->user_login ?>'?</p>
			<p>Check the box below to confirm:</p>

			<table class="form-table">
				<tbody>
				<tr class="user-user-login-wrap">
					<th><label for="user_login">Deactivate user:</label></th>
					<td><input type="checkbox" name="deactivate" id="deactivate" value="yes">
				</tr>
			</tbody></table>

			<input type="hidden" name="user_id" value="<?php echo $user->ID ?>">
			<input type="hidden" name="action" value="bce_form_response">
			<input type="hidden" name="bce_deactivate_user_nonce" value="<?php echo $bce_deactivate_user_nonce; ?>" />

			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Edit User Details"></p>
		</form>
		<br/><br/>
		<div id="bce_form_feedback"></div>
		<br/><br/>
		<br>
		<a href="<?php echo esc_url( add_query_arg( array( 'page' => wp_unslash( $_REQUEST['page'] ) ) , admin_url( 'users.php' ) ) ); ?>"><?php _e( 'Back', $this->plugin_text_domain ) ?></a>
<?php
	}
	else {
?>
		<p> <?php echo __( 'You are not authorized to perform this operation.', $this->plugin_text_domain ) ?> </p>
<?php
	}
