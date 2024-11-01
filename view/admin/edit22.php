<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<fieldset id="authordiv" class="dbx-box">
<h3 class="dbx-handle"><?php _e('Permissions', 'user-permissions'); ?>:</h3>
<div class="dbx-content">
	<?php echo $this->render_admin ('edit', array ('wp_roles' => $wp_roles, 'restrict' => $restrict)); ?>
</div>
</fieldset>
