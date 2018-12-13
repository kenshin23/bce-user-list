<?php

/**
 * The admin area of the plugin to load the User List Table
 */
?>

<div class="wrap">
    <h2><?php _e( 'BCE User List', $this->plugin_text_domain); ?></h2>
    <div id="bce-user-list">
        <div id="bce-post-body">
			<form id="bce-user-list-form" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php
					$this->user_list_table->search_box( __( 'Find', $this->plugin_text_domain ), 'bce-user-find');
					$this->user_list_table->display();
				?>
			</form>
        </div>
    </div>
</div>