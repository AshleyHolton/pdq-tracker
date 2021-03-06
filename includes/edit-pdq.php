<h2>Edit <?php echo $this->pdq_types[$this->current_setup_type]; ?> PDQ</h2>
<div id="validation-messages" class="notice notice-error"></div>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<form id="pdq-form" method="post" action="">
			<div id="postbox-container-1" class='postbox-container'>
					<input name="action" type="hidden" value="update_pdq" />
					<input name="status" type="hidden" value="<?php echo $pdq->status; ?>" />
					<input name="setup_type" type="hidden" value="<?php echo $this->current_setup_type; ?>" />
					<input name="pdq_id" type="hidden" value="<?php echo $edit_pdq_id; ?>" />
					<?php
						wp_nonce_field('pdq_update_nonce');
						do_meta_boxes($this->pdq_admin_pages['pdq-tracker'], 'side', $pdq);
					?>
			</div>

			<div id="postbox-container-2" class='postbox-container'>
				<?php do_meta_boxes($this->pdq_admin_pages['pdq-tracker'], 'normal', $pdq); ?>
			</div>
		</form>
	</div>
</div>