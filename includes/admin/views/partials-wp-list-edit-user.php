<?php

/**
 * The plugin area to admin the usermeta
 */
	$bce_edit_user_nonce = wp_create_nonce( 'bce_edit_user_form_nonce' );

	if( current_user_can('edit_users' ) ) { ?>
		<h2> <?php echo __('Edit User Details for ' . $user->display_name . ' (' . $user->user_login . ')', $this->plugin_text_domain ); ?> </h2>
		<br>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="bce_edit_user_form" >

			<table class="form-table">
				<tbody>
				<tr class="user-user-login-wrap">
					<th><label for="user_login">Username</label></th>
					<td><input type="text" name="user_login" id="user_login" value="<?php echo $user->user_login ?>" disabled="disabled" class="regular-text"> <span class="description">Usernames cannot be changed.</span></td>
				</tr>
				<tr class="user-first-name-wrap">
					<th><label for="first_name">First Name</label></th>
					<td><input type="text" name="first_name" id="first_name" value="<?php echo $user->first_name ?>" class="regular-text"></td>
				</tr>

				<tr class="user-last-name-wrap">
					<th><label for="last_name">Last Name</label></th>
					<td><input type="text" name="last_name" id="last_name" value="<?php echo $user->last_name ?>" class="regular-text"></td>
				</tr>

				<tr class="user-email-wrap">
					<th><label for="email">Email</label></th>
					<td><input type="text" name="email" id="email" value="<?php echo $user->user_email ?>" disabled="disabled" class="regular-text"> <span class="description">Email cannot be changed per evaluation guidelines.</span></td>
				</tr>

				<tr class="user-roles-wrap">
					<th><label for="roles">Roles</label></th>
					<td><input type="text" name="roles" id="roles" value="<?php echo implode(',', $user->roles); ?>" disabled="disabled" class="regular-text"> <span class="description">Roles cannot be changed per evaluation guidelines.</span></td>
				</tr>
			</tbody></table>

			<input type="hidden" name="user_id" value="<?php echo $user->ID ?>">
			<input type="hidden" name="action" value="bce_form_response">
			<input type="hidden" name="bce_edit_user_nonce" value="<?php echo $bce_edit_user_nonce; ?>" />

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
