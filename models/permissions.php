<?php

class Permissions
{
	var $role_read     = array ();
	var $role_write    = array ();
	var $user_read     = '';
	var $user_write    = '';
	var $redirect_read = '';

	function Permissions ($data = '')
	{
		if (is_array ($data))
		{
			if (isset ($data['role_read']))
				$this->role_read = $data['role_read'];

			if (isset ($data['role_write']))
				$this->role_write = $data['role_write'];

			if (isset ($data['user_read']))
			{
				$this->user_read = explode (',', $data['user_read']);
				$this->user_read = array_filter (array_map ('intval', $this->user_read));
				$this->user_read  = implode (',', $this->user_read);
			}

			if (isset ($data['user_write']))
			{
				$this->user_write = explode (',', $data['user_write']);
				$this->user_write = array_filter (array_map ('intval', $this->user_write));
				$this->user_write = implode (',', $this->user_write);
			}

			$this->redirect_read = intval ($data['redirect_read']);
		}
	}

	function save ($postid)
	{
		if (!function_exists ('has_meta'))
		{
			if (file_exists (ABSPATH.'wp-admin/admin-functions.php'))
				include (ABSPATH.'wp-admin/admin-functions.php');
			else if (file_exists (ABSPATH.'wp-admin/includes/post.php'))
				include (ABSPATH.'wp-admin/includes/post.php');
		}

		$delete = false;
		if (empty ($this->role_read) && empty ($this->role_write) && empty ($this->user_read) && empty ($this->user_write))
			$delete = true;

		$meta = has_meta ($postid);

		if (count ($meta) > 0)
		{
			foreach ($meta AS $item)
			{
				if ($item['meta_key'] == '_permissions')
				{
					if ($delete)
						delete_meta ($item['meta_id']);
					else
						update_meta ($item['meta_id'], '_permissions', serialize ($this));
					return;
				}
			}
		}

		$_POST['metakeyinput'] = '_permissions';
		$_POST['metavalue']    = serialize ($this);

		add_meta ($postid);
	}

	function get ($postid)
	{
		global $wpdb;

		if (!empty ($postid))
		{
			$row = $wpdb->get_row ( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key='_permissions'", $postid ) );
			if ($row)
			{
				if (get_magic_quotes_gpc ())
					$row->meta_value = stripslashes ($row->meta_value);

				$data = unserialize ($row->meta_value);
				if (!is_object ($data) == 'O:')
					$data = unserialize ($data);
				return $data;
			}
		}
		return new Permissions ();
	}

	function can_restrict ()
	{
		if (current_user_can ('administrator') || current_user_can ('edit_permissions'))
			return true;
		return false;
	}

	function is_restricted_array ($array, $types)
	{
		if (empty ($types) || in_array ('administrator', $array))
			return true;

		$matched = array_intersect ($array, $types);
		if (count ($matched) > 0)
			return true;
		return false;
	}

	function is_restricted_read_role ($role)
	{
		if (is_array ($role))
			return $this->is_restricted_array ($role, $this->role_read);
		return in_array ($role, $this->role_read);
	}

	function is_restricted_write_role ($role)
	{
		if (is_array ($role))
			return $this->is_restricted_array ($role, $this->role_write);
		return in_array ($role, $this->role_write);
	}

	function can_read ($user)
	{
		// Is the user specified?
		$users = array_filter (explode (',', $this->user_read));
		if (!empty ($users) && in_array ($user->ID, $users))
			return true;

		if ($this->is_restricted_read_role ($user->roles))
			return true;
		return false;
	}

	function can_write ($user)
	{
		// Is the user specified?
		$users = explode (',', $this->user_write);
		if (in_array ($user->ID, $users))
			return true;

		// Check roles
		if ($this->is_restricted_write_role ($user->roles))
			return true;

		return false;
	}

	function redirect ()
	{
		if ($this->redirect_read > 0)
			wp_redirect (get_permalink ($this->redirect_read));
		else
			wp_redirect ('/');
		die ();
	}
}
