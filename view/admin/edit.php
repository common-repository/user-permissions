<input type="hidden" name="permissions" value="true"/>

<table width="100%">
	<tr>
		<th style="text-align: left">Role</th>
		<th width="20" style="text-align: center">R</th>
		<th width="20" style="text-align: center">W</th>
	</tr>
	<?php foreach ($wp_roles->role_names as $key => $role) : ?>
	<tr>
		<td><?php echo $role ?></td>
		<td align="center"><input type="checkbox" name="role_read[]" value="<?php echo esc_attr( $key ) ?>"<?php if ($restrict->is_restricted_read_role ($key)) echo ' checked="checked"' ?>/></td>
		<td align="center"><input type="checkbox" name="role_write[]" value="<?php echo esc_attr( $key ) ?>"<?php if ($restrict->is_restricted_write_role ($key)) echo ' checked="checked"' ?>/></td>
	</tr>
	<?php endforeach; ?>
</table>

<p><strong>User IDs (read)</strong>:  <input style="width: 90%" type="text" name="user_read" value="<?php echo esc_attr( $restrict->user_read ) ?>"/></p>
<p><strong>User IDs (write)</strong>: <input style="width: 90%" type="text" name="user_write" value="<?php echo esc_attr( $restrict->user_write ) ?>"/></p>
<p><strong>Redirect read to post ID</strong>:  <input style="width: 90%" type="text" name="redirect_read" value="<?php echo esc_attr( $restrict->redirect_read > 0 ? $restrict->redirect_read : '' ) ?>"/></p>
