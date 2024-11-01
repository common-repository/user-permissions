<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div style="clear: both"></div>
<div class="postbox">
	<h3><?php _e ('Permissions', 'headspace') ?></h3>
	
	<div class="inside" id="headspacestuff">
		<?php echo $this->render_admin ('edit', array ('wp_roles' => $wp_roles, 'restrict' => $restrict)); ?>
	</div>
</div>
