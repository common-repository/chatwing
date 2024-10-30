<div id="poststuff">
	<div id="post-body" class="columns-2">
		<div id="post-body-content">
			<form action="<?php echo admin_url('admin.php'); ?>" method="post">
				<div id="titlediv">
					<div id="titlewrap">
						<h2>Please insert Chatwing access token:</h2>
						<input type="text" id="title" name="token" placeholder="Access token value">
						<input type="hidden" name="action" value="chatwing_save_token">
						<?php wp_nonce_field('token_save', 'nonce' ); ?>
					</div>
				</div>

				<div style="margin-top:10px;">
					<button class="button button-primary"><?php _e('Save token', CHATWING_TEXTDOMAIN); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>