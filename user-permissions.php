<?php
/*
Plugin Name: User Permissions
Plugin URI: http://urbangiraffe.com/plugins/user-permissions/
Description: Per-post user permissions
Author: John Godley
Version: 0.8.4
Author URI: http://urbangiraffe.com/

0.1   - Initial release
0.2   - Add support for role-based restrictions
0.3   - Separate type for user and role, allow use of 'edit_permissions' capability
0.4   - Correct table prefix
0.5   - Fix all known bugs, add read permissions
0.6   - Correct typo that prevented read permissions from working
0.7   - Fix #12, #62, #121
0.8   - WP 2.5
0.8.1 - WP 2.6
0.8.2 - WP 2.6 stuff again
0.8.3 - Fix for magic quotes
0.8.4 - Minor fix to remove warning message
*/


include (dirname (__FILE__).'/plugin.php');
include (dirname (__FILE__).'/models/permissions.php');


class User_Permissions extends UserPermission_Plugin
{
	function User_Permissions ()
	{
		$this->register_plugin ('user-perms', __FILE__);

		$this->add_filter ('user_has_cap', 'check_capabilities', 10, 3);
		$this->add_action ('the_posts');

		if (is_admin ())
		{
			$this->add_action ('dbx_post_sidebar', 'edit');
			$this->add_action ('dbx_page_sidebar', 'edit');
			$this->add_action ('edit_page_form', 'edit');
			$this->add_action ('save_post');
		}
	}

	function is_25 ()
	{
		global $wp_version;
		if (version_compare ('2.5', $wp_version) <= 0)
			return true;
		return false;
	}

	function edit ()
	{
		$this->check_version ();

		if (Permissions::can_restrict ())
		{
			global $wp_roles;
			$this->render_admin (($this->is_25 () ? 'edit25' : 'edit22'), array ('wp_roles' => $wp_roles, 'restrict' => Permissions::get (intval ($_GET['post']))));
		}
	}

	function check_capabilities ($all, $cap, $args)
	{
		if ( !isset( $_GET['post'] ) )
			return $all;

		$this->check_version ();

		global $current_user;

		// 1st arg - user ID, 2nd arg = post ID
		switch ($cap[0])
		{
			case 'edit_others_pages' :
			case 'edit_others_posts':
				$permissions = Permissions::get (intval ($_GET['post']));
				if ($permissions->can_write ($current_user) == false)
				{
					$all['edit_others_posts'] = 0;
					$all['edit_others_pages'] = 0;
				}
				break;
		}

		return $all;
	}

	function the_posts ($posts)
	{
		if (!empty ($posts))
		{
			global $current_user;

			foreach ($posts AS $id => $post)
			{
				$permissions = Permissions::get ($post->ID);
				if ($permissions && $permissions->can_read ($current_user) == false)
				{
					if (is_single () || is_page ())
						$permissions->redirect ();
					else
						unset ($posts[$id]);
				}
			}

			$posts = array_values ($posts);
		}

		return $posts;
	}

	function save_post ($id)
	{
		if (isset ($_POST['permissions']))
		{
			$restrict = new Permissions ($_POST);
			$restrict->save ($id);
		}
	}

	function check_version ()
	{
		if (get_option ('user_permissions') === false)
		{
			global $wpdb;

			$rows = $wpdb->get_results ("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='_permissions'");
			if ($rows)
			{
				foreach ($rows AS $row)
				{
					$perms = unserialize (unserialize ($row->meta_value));

					$newperms = new Permissions ();
					$newperms->user_write = '';
					if (is_array ($perms->users))
						$newperms->user_write = implode (',', $perms->users);
					$newperms->user_read  = '';
					$newperms->role_write = $perms->roles;
					$newperms->role_read  = array ();

					$newperms->save ($row->post_id);
				}
			}

			update_option ('user_permissions', 1);
		}
	}
}

$obj = new User_Permissions;
