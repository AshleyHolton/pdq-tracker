<h2>New <?php echo $this->pdq_types[$this->current_setup_type]; ?> PDQ</h2>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<form method="post" action="">
			<div id="postbox-container-1" class='postbox-container'>
					<input name="action" type="hidden" value="create_pdq" />
					<input name="setup_type" type="hidden" value="<?php echo $this->current_setup_type; ?>" />
					<?php
						wp_nonce_field('pdq_create_nonce');
						do_meta_boxes($this->pdq_admin_pages['pdq-tracker'], 'side', null);
					?>
			</div>
			
			<div id="postbox-container-2" class='postbox-container'>
				<?php do_meta_boxes($this->pdq_admin_pages['pdq-tracker'], 'normal', null); ?>
			</div>
		</form>
	</div>
</div>