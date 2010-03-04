<?php
/**********************************************************************************
* Admin.php                                                                       *
***********************************************************************************
* SMF: Simple Machines Forum                                                      *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                    *
* =============================================================================== *
* Software Version:           SMF 2.0 RC2                                         *
* Software by:                Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2009 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
* Support, News, Updates at:  http://www.simplemachines.org                       *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version can always be found at http://www.simplemachines.org.        *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file, unpredictable as this might be, handles basic administration.
	The most important function in this file for mod makers happens to be the
	updateSettingsFile() function, but it shouldn't be used often anyway.

	void AdminMain()
		- initialises all the basic context required for the admin center.
		- passes execution onto the relevant admin section.
		- if the passed section is not found it shows the admin home page.

	void AdminHome()
		- prepares all the data necessary for the administration front page.
		- uses the Admin template along with the admin sub template.
		- requires the moderate_forum, manage_membergroups, manage_bans,
		  admin_forum, manage_permissions, manage_attachments, manage_smileys,
		  manage_boards, edit_news, or send_mail permission.
		- uses the index administrative area.
		- can be found by going to ?action=admin.

	void ManageCopyright()
		// !!!

	void AdminSearch()
		// !!

	void AdminSearchInternal()
		// !!

	void AdminSearchMember()
		// !!

	void DisplayAdminFile()
		// !!

*/

// The main admin handling function.
function AdminMain()
{
	global $txt, $context, $scripturl, $sc, $modSettings, $user_info, $settings, $sourcedir, $options, $smcFunc;

	// Load the language and templates....
	loadLanguage('Admin');
	loadTemplate('Admin', 'admin');

	// No indexing evil stuff.
	$context['robot_no_index'] = true;

	require_once($sourcedir . '/Subs-Menu.php');

	// Some preferences.
	$context['admin_preferences'] = !empty($options['admin_preferences']) ? unserialize($options['admin_preferences']) : array();

	// Define all the menu structure - see Subs-Menu.php for details!
	$admin_areas = array(
		'forum' => array(
			'title' => $txt['admin_main'],
			'permission' => array('admin_forum', 'manage_permissions', 'moderate_forum', 'manage_membergroups', 'manage_bans', 'send_mail', 'edit_news', 'manage_boards', 'manage_smileys', 'manage_attachments'),
			'areas' => array(
				'index' => array(
					'label' => $txt['admin_center'],
					'function' => 'AdminHome',
					'icon' => 'administration.gif',
				),
				'credits' => array(
					'label' => $txt['support_credits_title'],
					'function' => 'AdminHome',
					'icon' => 'support.gif',
				),
				'news' => array(
					'label' => $txt['news_title'],
					'file' => 'ManageNews.php',
					'function' => 'ManageNews',
					'icon' => 'news.gif',
					'permission' => array('edit_news', 'send_mail', 'admin_forum'),
					'subsections' => array(
						'edit_news' => array($txt['admin_edit_news'], 'edit_news'),
						'mailingmembers' => array($txt['admin_newsletters'], 'send_mail'),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'packages' => array(
					'label' => $txt['package'],
					'file' => 'Packages.php',
					'function' => 'Packages',
					'permission' => array('admin_forum'),
					'icon' => 'packages.gif',
					'subsections' => array(
						'browse' => array($txt['browse_packages']),
						'packageget' => array($txt['download_packages'], 'url' => $scripturl . '?action=admin;area=packages;sa=packageget;get'),
						'installed' => array($txt['installed_packages']),
						'perms' => array($txt['package_file_perms']),
						'options' => array($txt['package_settings']),
					),
				),
				'copyright' => array(
					'function' => 'ManageCopyright',
					'permission' => array('admin_forum'),
					'select' => 'index'
				),
				'search' => array(
					'function' => 'AdminSearch',
					'permission' => array('admin_forum'),
					'select' => 'index'
				),
			),
		),
		'config' => array(
			'title' => $txt['admin_config'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'corefeatures' => array(
					'label' => $txt['core_settings_title'],
					'file' => 'ManageSettings.php',
					'function' => 'ModifyCoreFeatures',
					'icon' => 'corefeatures.gif',
				),
				'featuresettings' => array(
					'label' => $txt['modSettings_title'],
					'file' => 'ManageSettings.php',
					'function' => 'ModifyFeatureSettings',
					'icon' => 'features.gif',
					'subsections' => array(
						'basic' => array($txt['mods_cat_features']),
						'layout' => array($txt['mods_cat_layout']),
						'karma' => array($txt['karma'], 'enabled' => in_array('k', $context['admin_features'])),
						'sig' => array($txt['signature_settings_short']),
						'profile' => array($txt['custom_profile_shorttitle'], 'enabled' => in_array('cp', $context['admin_features'])),
					),
				),
				'securitysettings' => array(
					'label' => $txt['admin_security_moderation'],
					'file' => 'ManageSettings.php',
					'function' => 'ModifySecuritySettings',
					'icon' => 'security.gif',
					'subsections' => array(
						'general' => array($txt['mods_cat_security_general']),
						'spam' => array($txt['antispam_title']),
						'moderation' => array($txt['moderation_settings_short'], 'enabled' => substr($modSettings['warning_settings'], 0, 1) == 1),
					),
				),
				'languages' => array(
					'label' => $txt['language_configuration'],
					'file' => 'ManageServer.php',
					'function' => 'ManageLanguages',
					'icon' => 'languages.gif',
					'subsections' => array(
						'edit' => array($txt['language_edit']),
						'add' => array($txt['language_add']),
						'settings' => array($txt['language_settings']),
					),
				),
				'serversettings' => array(
					'label' => $txt['admin_server_settings'],
					'file' => 'ManageServer.php',
					'function' => 'ModifySettings',
					'icon' => 'server.gif',
					'subsections' => array(
						'general' => array($txt['general_settings']),
						'database' => array($txt['database_paths_settings']),
						'cookie' => array($txt['cookies_sessions_settings']),
						'cache' => array($txt['caching_settings']),
						'loads' => array($txt['load_balancing_settings']),
					),
				),
				'current_theme' => array(
					'label' => $txt['theme_current_settings'],
					'file' => 'Themes.php',
					'function' => 'ThemesMain',
					'custom_url' => $scripturl . '?action=admin;area=theme;sa=settings;th=' . $settings['theme_id'],
					'icon' => 'current_theme.gif',
				),
				'theme' => array(
					'label' => $txt['theme_admin'],
					'file' => 'Themes.php',
					'function' => 'ThemesMain',
					'custom_url' => $scripturl . '?action=admin;area=theme;sa=admin',
					'icon' => 'themes.gif',
					'subsections' => array(
						'admin' => array($txt['themeadmin_admin_title']),
						'list' => array($txt['themeadmin_list_title']),
						'reset' => array($txt['themeadmin_reset_title']),
						'edit' => array($txt['themeadmin_edit_title']),
					),
				),
				'modsettings' => array(
					'label' => $txt['admin_modifications'],
					'file' => 'ManageSettings.php',
					'function' => 'ModifyModSettings',
					'icon' => 'modifications.gif',
					'subsections' => array(
						'general' => array($txt['mods_cat_modifications_misc']),
						// Mod Authors for a "ADD AFTER" on this line. Ensure you end your change with a comma. For example:
						// 'shout' => array($txt['shout']),
						// Note the comma!! The setting with automatically appear with the first mod to be added.
					),
				),
			),
		),
		'layout' => array(
			'title' => $txt['layout_controls'],
			'permission' => array('manage_boards', 'admin_forum', 'manage_smileys', 'manage_attachments', 'moderate_forum'),
			'areas' => array(
				'manageboards' => array(
					'label' => $txt['admin_boards'],
					'file' => 'ManageBoards.php',
					'function' => 'ManageBoards',
					'icon' => 'boards.gif',
					'permission' => array('manage_boards'),
					'subsections' => array(
						'main' => array($txt['boardsEdit']),
						'newcat' => array($txt['mboards_new_cat']),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'postsettings' => array(
					'label' => $txt['manageposts'],
					'file' => 'ManagePosts.php',
					'function' => 'ManagePostSettings',
					'permission' => array('admin_forum', 'moderate_forum'),
					'icon' => 'posts.gif',
					'subsections' => array(
						'posts' => array($txt['manageposts_settings'], 'admin_forum'),
						'bbc' => array($txt['manageposts_bbc_settings'], 'admin_forum'),
						'censor' => array($txt['admin_censored_words'], 'moderate_forum'),
						'topics' => array($txt['manageposts_topic_settings'], 'admin_forum'),
					),
				),
				'managecalendar' => array(
					'label' => $txt['manage_calendar'],
					'file' => 'ManageCalendar.php',
					'function' => 'ManageCalendar',
					'icon' => 'calendar.gif',
					'permission' => array('admin_forum'),
					'enabled' => in_array('cd', $context['admin_features']),
					'subsections' => array(
						'holidays' => array($txt['manage_holidays'], 'admin_forum', 'enabled' => !empty($modSettings['cal_enabled'])),
						'settings' => array($txt['calendar_settings'], 'admin_forum'),
					),
				),
				'managesearch' => array(
					'label' => $txt['manage_search'],
					'file' => 'ManageSearch.php',
					'function' => 'ManageSearch',
					'icon' => 'search.gif',
					'permission' => array('admin_forum'),
					'subsections' => array(
						'weights' => array($txt['search_weights']),
						'method' => array($txt['search_method']),
						'settings' => array($txt['settings']),
					),
				),
				'smileys' => array(
					'label' => $txt['smileys_manage'],
					'file' => 'ManageSmileys.php',
					'function' => 'ManageSmileys',
					'icon' => 'smiley.gif',
					'permission' => array('manage_smileys'),
					'subsections' => array(
						'editsets' => array($txt['smiley_sets']),
						'addsmiley' => array($txt['smileys_add'], 'enabled' => !empty($modSettings['smiley_enable'])),
						'editsmileys' => array($txt['smileys_edit'], 'enabled' => !empty($modSettings['smiley_enable'])),
						'setorder' => array($txt['smileys_set_order'], 'enabled' => !empty($modSettings['smiley_enable'])),
						'editicons' => array($txt['icons_edit_message_icons'], 'enabled' => !empty($modSettings['messageIcons_enable'])),
						'settings' => array($txt['settings']),
					),
				),
				'manageattachments' => array(
					'label' => $txt['attachments_avatars'],
					'file' => 'ManageAttachments.php',
					'function' => 'ManageAttachments',
					'icon' => 'attachment.gif',
					'permission' => array('manage_attachments'),
					'subsections' => array(
						'browse' => array($txt['attachment_manager_browse']),
						'attachments' => array($txt['attachment_manager_settings']),
						'avatars' => array($txt['attachment_manager_avatar_settings']),
						'maintenance' => array($txt['attachment_manager_maintenance']),
					),
				),
			),
		),
		'members' => array(
			'title' => $txt['admin_manage_members'],
			'permission' => array('moderate_forum', 'manage_membergroups', 'manage_bans', 'manage_permissions', 'admin_forum'),
			'areas' => array(
				'viewmembers' => array(
					'label' => $txt['admin_users'],
					'file' => 'ManageMembers.php',
					'function' => 'ViewMembers',
					'icon' => 'members.gif',
					'permission' => array('moderate_forum'),
					'subsections' => array(
						'all' => array($txt['view_all_members']),
						'search' => array($txt['mlist_search']),
					),
				),
				'membergroups' => array(
					'label' => $txt['admin_groups'],
					'file' => 'ManageMembergroups.php',
					'function' => 'ModifyMembergroups',
					'icon' => 'membergroups.gif',
					'permission' => array('manage_membergroups'),
					'subsections' => array(
						'index' => array($txt['membergroups_edit_groups'], 'manage_membergroups'),
						'add' => array($txt['membergroups_new_group'], 'manage_membergroups'),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'permissions' => array(
					'label' => $txt['edit_permissions'],
					'file' => 'ManagePermissions.php',
					'function' => 'ModifyPermissions',
					'icon' => 'permissions.gif',
					'permission' => array('manage_permissions'),
					'subsections' => array(
						'index' => array($txt['permissions_groups'], 'manage_permissions'),
						'board' => array($txt['permissions_boards'], 'manage_permissions'),
						'profiles' => array($txt['permissions_profiles'], 'manage_permissions'),
						'postmod' => array($txt['permissions_post_moderation'], 'manage_permissions', 'enabled' => $modSettings['postmod_active']),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'regcenter' => array(
					'label' => $txt['registration_center'],
					'file' => 'ManageRegistration.php',
					'function' => 'RegCenter',
					'icon' => 'regcenter.gif',
					'permission' => array('admin_forum', 'moderate_forum'),
					'subsections' => array(
						'register' => array($txt['admin_browse_register_new'], 'moderate_forum'),
						'agreement' => array($txt['registration_agreement'], 'admin_forum'),
						'reservednames' => array($txt['admin_reserved_set'], 'admin_forum'),
						'settings' => array($txt['settings'], 'admin_forum'),
					),
				),
				'ban' => array(
					'label' => $txt['ban_title'],
					'file' => 'ManageBans.php',
					'function' => 'Ban',
					'icon' => 'ban.gif',
					'permission' => 'manage_bans',
					'subsections' => array(
						'list' => array($txt['ban_edit_list']),
						'add' => array($txt['ban_add_new']),
						'browse' => array($txt['ban_trigger_browse']),
						'log' => array($txt['ban_log']),
					),
				),
				'paidsubscribe' => array(
					'label' => $txt['paid_subscriptions'],
					'enabled' => in_array('ps', $context['admin_features']),
					'file' => 'ManagePaid.php',
					'icon' => 'paid.gif',
					'function' => 'ManagePaidSubscriptions',
					'permission' => 'admin_forum',
					'subsections' => array(
						'view' => array($txt['paid_subs_view']),
						'settings' => array($txt['settings']),
					),
				),
				'sengines' => array(
					'label' => $txt['search_engines'],
					'enabled' => in_array('sp', $context['admin_features']),
					'file' => 'ManageSearchEngines.php',
					'icon' => 'engines.gif',
					'function' => 'SearchEngines',
					'permission' => 'admin_forum',
					'subsections' => array(
						'stats' => array($txt['spider_stats']),
						'logs' => array($txt['spider_logs']),
						'spiders' => array($txt['spiders']),
						'settings' => array($txt['settings']),
					),
				),
			),
		),
		'maintenance' => array(
			'title' => $txt['admin_maintenance'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'maintain' => array(
					'label' => $txt['maintain_title'],
					'file' => 'ManageMaintenance.php',
					'icon' => 'maintain.gif',
					'function' => 'ManageMaintenance',
					'subsections' => array(
						'routine' => array($txt['maintain_sub_routine'], 'admin_forum'),
						'database' => array($txt['maintain_sub_database'], 'admin_forum'),
						'members' => array($txt['maintain_sub_members'], 'admin_forum'),
						'topics' => array($txt['maintain_sub_topics'], 'admin_forum'),
					),
				),
				'scheduledtasks' => array(
					'label' => $txt['maintain_tasks'],
					'file' => 'ManageScheduledTasks.php',
					'icon' => 'scheduled.gif',
					'function' => 'ManageScheduledTasks',
					'subsections' => array(
						'tasks' => array($txt['maintain_tasks'], 'admin_forum'),
						'tasklog' => array($txt['scheduled_log'], 'admin_forum'),
					),
				),
				'mailqueue' => array(
					'label' => $txt['mailqueue_title'],
					'file' => 'ManageMail.php',
					'function' => 'ManageMail',
					'icon' => 'mail.gif',
					'subsections' => array(
						'browse' => array($txt['mailqueue_browse'], 'admin_forum'),
						'settings' => array($txt['mailqueue_settings'], 'admin_forum'),
					),
				),
				'reports' => array(
					'enabled' => in_array('rg', $context['admin_features']),
					'label' => $txt['generate_reports'],
					'file' => 'Reports.php',
					'function' => 'ReportsMain',
					'icon' => 'reports.gif',
				),
				'logs' => array(
					'label' => $txt['logs'],
					'function' => 'AdminLogs',
					'icon' => 'logs.gif',
					'subsections' => array(
						'errorlog' => array($txt['errlog'], 'admin_forum', 'enabled' => !empty($modSettings['enableErrorLogging']), 'url' => $scripturl . '?action=admin;area=logs;sa=errorlog;desc'),
						'adminlog' => array($txt['admin_log'], 'admin_forum', 'enabled' => in_array('ml', $context['admin_features'])),
						'modlog' => array($txt['moderation_log'], 'admin_forum', 'enabled' => in_array('ml', $context['admin_features'])),
						'banlog' => array($txt['ban_log'], 'manage_bans'),
						'spiderlog' => array($txt['spider_logs'], 'admin_forum', 'enabled' => in_array('sp', $context['admin_features'])),
						'tasklog' => array($txt['scheduled_log'], 'admin_forum'),
						'pruning' => array($txt['pruning_title'], 'admin_forum'),
					),
				),
				'repairboards' => array(
					'label' => $txt['admin_repair'],
					'file' => 'RepairBoards.php',
					'function' => 'RepairBoards',
					'select' => 'maintain',
					'hidden' => true,
				),
			),
		),
	);

	// Make sure the administrator has a valid session...
	validateSession();

	// Actually create the menu!
	$admin_include_data = createMenu($admin_areas);
	unset($admin_areas);

	// Nothing valid?
	if ($admin_include_data == false)
		fatal_lang_error('no_access', false);

	// Build the link tree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin',
		'name' => $txt['admin_center'],
	);
	if (isset($admin_include_data['current_area']) && $admin_include_data['current_area'] != 'index')
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=' . $admin_include_data['current_area'],
			'name' => $admin_include_data['label'],
		);
	if (!empty($admin_include_data['current_subsection']) && $admin_include_data['subsections'][$admin_include_data['current_subsection']][0] != $admin_include_data['label'])
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=' . $admin_include_data['current_area'] . ';sa=' . $admin_include_data['current_subsection'],
			'name' => $admin_include_data['subsections'][$admin_include_data['current_subsection']][0],
		);

	// Make a note of the Unique ID for this menu.
	$context['admin_menu_id'] = $context['max_menu_id'];
	$context['admin_menu_name'] = 'menu_data_' . $context['admin_menu_id'];

	// Why on the admin are we?
	$context['admin_area'] = $admin_include_data['current_area'];

	// Now - finally - call the right place!
	if (isset($admin_include_data['file']))
		require_once($sourcedir . '/' . $admin_include_data['file']);

	$admin_include_data['function']();
}

// The main administration section.
function AdminHome()
{
	global $sourcedir, $forum_version, $txt, $scripturl, $context, $user_info, $boardurl, $modSettings, $smcFunc;

	// You have to be able to do at least one of the below to see this page.
	isAllowedTo(array('admin_forum', 'manage_permissions', 'moderate_forum', 'manage_membergroups', 'manage_bans', 'send_mail', 'edit_news', 'manage_boards', 'manage_smileys', 'manage_attachments'));

	// Find all of this forum's administrators...
	require_once($sourcedir . '/Subs-Membergroups.php');
	if (listMembergroupMembers_Href($context['administrators'], 1, 32) && allowedTo('manage_membergroups'))
	{
		// Add a 'more'-link if there are more than 32.
		$context['more_admins_link'] = '<a href="' . $scripturl . '?action=moderate;area=viewgroups;sa=members;group=1">' . $txt['more'] . '</a>';
	}

	// Load the credits stuff.
	require_once($sourcedir . '/Who.php');
	Credits(true);

	// Fill in the blanks in the support resources paragraphs.
	$txt['support_resources_p1'] = sprintf($txt['support_resources_p1'],
		'http://docs.simplemachines.org/',
		'http://docs.simplemachines.org/redirect/features',
		'http://docs.simplemachines.org/redirect/settings',
		'http://docs.simplemachines.org/redirect/themes',
		'http://docs.simplemachines.org/redirect/packages'
		);
	$txt['support_resources_p2'] = sprintf($txt['support_resources_p2'],
		'http://www.simplemachines.org/community/',
		'http://www.simplemachines.org/redirect/english_support',
		'http://www.simplemachines.org/redirect/international_support_boards',
		'http://www.simplemachines.org/redirect/smf_support',
		'http://www.simplemachines.org/redirect/customize_support'
		);

	// Copyright?
	if (!empty($modSettings['copy_settings']) || !empty($modSettings['copyright_key']))
	{
		if (empty($modSettings['copy_settings']))
			$modSettings['copy_settings'] = 'a,0';

		// Not done it yet...
		if (empty($_SESSION['copy_expire']))
		{
			list ($key, $expires) = explode(',', $modSettings['copy_settings']);
			// Get the expired date.
			require_once($sourcedir . '/Subs-Package.php');
			$return_data = fetch_web_data('http://www.simplemachines.org/smf/copyright/check_copyright.php?site=' . base64_encode($boardurl) . '&key=' . $key . '&version=' . base64_encode($forum_version));

			// Get the expire date.
			$return_data = substr($return_data, strpos($return_data, 'STARTCOPY') + 9);
			$return_data = trim(substr($return_data, 0, strpos($return_data, 'ENDCOPY')));

			$deletekeys = true;
			if ($return_data != 'void')
			{
				list ($_SESSION['copy_expire'], $copyright_key) = explode('|', $return_data);
				$_SESSION['copy_key'] = $key;

				if ($_SESSION['copy_expire'] > time())
				{
					$deletekeys = false;
					$copy_settings = $key . ',' . (int) $_SESSION['copy_expire'];
					updateSettings(array('copy_settings' => $copy_settings, 'copyright_key' => $copyright_key));
				}
			}

			if ($deletekeys)
			{
				$_SESSION['copy_expire'] = '';
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}settings
					WHERE variable = {string:copy_settings}
						OR variable = {string:copyright_key}',
					array(
						'copy_settings' => 'copy_settings',
						'copyright_key' => 'copyright_key',
					)
				);
			}
		}

		if (isset($_SESSION['copy_expire']) && $_SESSION['copy_expire'] > time())
		{
			$context['copyright_expires'] = (int) (($_SESSION['copy_expire'] - time()) / 3600 / 24);
			$context['copyright_key'] = $_SESSION['copy_key'];
		}
	}

	// This makes it easier to get the latest news with your time format.
	$context['time_format'] = urlencode($user_info['time_format']);

	$context['current_versions'] = array(
		'php' => array('title' => $txt['support_versions_php'], 'version' => PHP_VERSION),
		'db' => array('title' => sprintf($txt['support_versions_db'], $smcFunc['db_title']), 'version' => ''),
		'server' => array('title' => $txt['support_versions_server'], 'version' => $_SERVER['SERVER_SOFTWARE']),
	);
	$context['forum_version'] = $forum_version;

	// Get a list of current server versions.
	require_once($sourcedir . '/Subs-Admin.php');
	$checkFor = array(
		'gd',
		'db_server',
		'mmcache',
		'eaccelerator',
		'phpa',
		'apc',
		'memcache',
		'xcache',
		'php',
		'server',
	);
	$context['current_versions'] = getServerVersions($checkFor);

	$context['can_admin'] = allowedTo('admin_forum');

	$context['sub_template'] = $context['admin_area'] == 'credits' ? 'credits' : 'admin';
	$context['page_title'] = $context['admin_area'] == 'credits' ? $txt['support_credits_title'] : $txt['admin_center'];

	// The format of this array is: permission, action, title, description, icon.
	$quick_admin_tasks = array(
		array('', 'credits', 'support_credits_title', 'support_credits_info', 'support_and_credits.png'),
		array('admin_forum', 'featuresettings', 'modSettings_title', 'modSettings_info', 'features_and_options.png'),
		array('admin_forum', 'maintain', 'maintain_title', 'maintain_info', 'forum_maintenance.png'),
		array('manage_permissions', 'permissions', 'edit_permissions', 'edit_permissions_info', 'permissions.png'),
		array('admin_forum', 'theme;sa=admin;' . $context['session_var'] . '=' . $context['session_id'], 'theme_admin', 'theme_admin_info', 'themes_and_layout.png'),
		array('admin_forum', 'packages', 'package', 'package_info', 'packages.png'),
		array('manage_smileys', 'smileys', 'smileys_manage', 'smileys_manage_info', 'smilies_and_messageicons.png'),
		array('moderate_forum', 'viewmembers', 'admin_users', 'member_center_info', 'members.png'),
	);

	$context['quick_admin_tasks'] = array();
	foreach ($quick_admin_tasks as $task)
	{
		if (!empty($task[0]) && !allowedTo($task[0]))
			continue;

		$context['quick_admin_tasks'][] = array(
			'href' => $scripturl . '?action=admin;area=' . $task[1],
			'link' => '<a href="' . $scripturl . '?action=admin;area=' . $task[1] . '">' . $txt[$task[2]] . '</a>',
			'title' => $txt[$task[2]],
			'description' => $txt[$task[3]],
			'icon' => $task[4],
			'is_last' => false
		);
	}

	if (count($context['quick_admin_tasks']) % 2 == 1)
	{
		$context['quick_admin_tasks'][] = array(
			'href' => '',
			'link' => '',
			'title' => '',
			'description' => '',
			'is_last' => true
		);
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 2]['is_last'] = true;
	}
	elseif (count($context['quick_admin_tasks']) != 0)
	{
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 1]['is_last'] = true;
		$context['quick_admin_tasks'][count($context['quick_admin_tasks']) - 2]['is_last'] = true;
	}
}

// Allow users to remove their copyright.
function ManageCopyright()
{
	global $forum_version, $txt, $sourcedir, $context, $boardurl, $modSettings;

	isAllowedTo('admin_forum');

	if (isset($_POST['copy_code']))
	{
		checkSession('post');

		$_POST['copy_code'] = urlencode($_POST['copy_code']);

		// Check the actual code.
		require_once($sourcedir . '/Subs-Package.php');
		$return_data = fetch_web_data('http://www.simplemachines.org/smf/copyright/check_copyright.php?site=' . base64_encode($boardurl) . '&key=' . $_POST['copy_code'] . '&version=' . base64_encode($forum_version));

		// Get the data back
		$return_data = substr($return_data, strpos($return_data, 'STARTCOPY') + 9);
		$return_data = trim(substr($return_data, 0, strpos($return_data, 'ENDCOPY')));

		if ($return_data != 'void')
		{
			list ($_SESSION['copy_expire'], $copyright_key) = explode('|', $return_data);

			if ($_SESSION['copy_expire'] <= time())
			{
				// So sorry but that has already expired.
				$_SESSION['copy_expire'] = '';
				fatal_lang_error('copyright_failed');
			}

			$_SESSION['copy_key'] = $_POST['copy_code'];
			$copy_settings = $_POST['copy_code'] . ',' . (int) $_SESSION['copy_expire'];
			updateSettings(array('copy_settings' => $copy_settings, 'copyright_key' => $copyright_key));
			redirectexit('action=admin');
		}
		else
		{
			fatal_lang_error('copyright_failed');
		}
	}

	$context['sub_template'] = 'manage_copyright';
	$context['page_title'] = $txt['copyright_removal'];
}

// Get one of the admin information files from Simple Machines.
function DisplayAdminFile()
{
	global $context, $modSettings, $smcFunc;

	@ini_set('memory_limit', '32M');

	if (empty($_REQUEST['filename']) || !is_string($_REQUEST['filename']))
		fatal_lang_error('no_access', false);

	$request = $smcFunc['db_query']('', '
		SELECT data, filetype
		FROM {db_prefix}admin_info_files
		WHERE filename = {string:current_filename}
		LIMIT 1',
		array(
			'current_filename' => $_REQUEST['filename'],
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('admin_file_not_found', true, array($_REQUEST['filename']));

	list ($file_data, $filetype) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// !!! Temp.
	// Figure out if sesc is still being used.
	if (strpos($file_data, ';sesc=') !== false)
		$file_data = '
if (!(\'smfForum_sessionvar\' in window))
	window.smfForum_sessionvar = \'sesc\';
' . strtr($file_data, array(';sesc=' => ';\' + window.smfForum_sessionvar + \'='));

	$context['template_layers'] = array();
	// Lets make sure we aren't going to output anything nasty.
	@ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		@ob_start();

	// Make sure they know what type of file we are.
	header('Content-Type: ' . $filetype);
	echo $file_data;
	obExit(false);
}

// This allocates out all the search stuff.
function AdminSearch()
{
	global $txt, $context, $smcFunc, $sourcedir;

	isAllowedTo('admin_forum');

	// What can we search for?
	$subactions = array(
		'internal' => 'AdminSearchInternal',
		'online' => 'AdminSearchOM',
		'member' => 'AdminSearchMember',
	);

	$context['search_type'] = !isset($_REQUEST['search_type']) || !isset($subactions[$_REQUEST['search_type']]) ? 'internal' : $_REQUEST['search_type'];
	$context['search_term'] = isset($_REQUEST['search_term']) ? $smcFunc['htmlspecialchars']($_REQUEST['search_term'], ENT_QUOTES) : '';

	$context['sub_template'] = 'admin_search_results';
	$context['page_title'] = $txt['admin_search_results'];

	// Keep track of what the admin wants.
	if (empty($context['admin_preferences']['sb']) || $context['admin_preferences']['sb'] != $context['search_type'])
	{
		$context['admin_preferences']['sb'] = $context['search_type'];

		// Update the preferences.
		require_once($sourcedir . '/Subs-Admin.php');
		updateAdminPreferences();
	}

	if (trim($context['search_term']) == '')
		$context['search_results'] = array();
	else
		$subactions[$context['search_type']]();
}

// A complicated but relatively quick internal search.
function AdminSearchInternal()
{
	global $context, $txt, $helptxt, $scripturl, $sourcedir;

	// Try to get some more memory.
	@ini_set('memory_limit', '128M');

	// Load a lot of language files.
	$language_files = array(
		'Help', 'ManageMail', 'ManageSettings', 'ManageCalendar', 'ManageBoards', 'ManagePaid', 'ManagePermissions', 'Search',
		'Login', 'ManageSmileys',
	);
	loadLanguage(implode('+', $language_files));

	// All the files we need to include.
	$include_files = array(
		'ManageSettings', 'ManageBoards', 'ManageNews', 'ManageAttachments', 'ManageCalendar', 'ManageMail', 'ManagePaid', 'ManagePermissions',
		'ManagePosts', 'ManageRegistration', 'ManageSearch', 'ManageSearchEngines', 'ManageServer', 'ManageSmileys',
	);
	foreach ($include_files as $file)
		require_once($sourcedir . '/' . $file . '.php');

	/* This is the huge array that defines everything... it's a huge array of items formatted as follows:
		0 = Language index (Can be array of indexes) to search through for this setting.
		1 = URL for this indexes page.
		2 = Help index for help associated with this item (If different from 0)
	*/

	$search_data = array(
		// All the major sections of the forum.
		'sections' => array(
		),
		'settings' => array(
			array('COPPA', 'area=regcenter;sa=settings'),
			array('CAPTCHA', 'area=regcenter;sa=settings'),
		),
	);

	// Go through the admin menu structure trying to find suitably named areas!
	foreach ($context[$context['admin_menu_name']]['sections'] as $section)
	{
		foreach ($section['areas'] as $menu_key => $menu_item)
		{
			$search_data['sections'][] = array($menu_item['label'], 'area=' . $menu_key);
			if (!empty($menu_item['subsections']))
				foreach ($menu_item['subsections'] as $key => $sublabel)
				{
					if (isset($sublabel['label']))
						$search_data['sections'][] = array($sublabel['label'], 'area=' . $menu_key . ';sa=' . $key);
				}
		}
	}

	// This is a special array of functions that contain setting data - we query all these to simply pull all setting bits!
	$settings_search = array(
		array('ModifyCoreFeatures', 'area=corefeatures'),
		array('ModifyBasicSettings', 'area=featuresettings;sa=basic'),
		array('ModifyLayoutSettings', 'area=featuresettings;sa=layout'),
		array('ModifyKarmaSettings', 'area=featuresettings;sa=karma'),
		array('ModifySignatureSettings', 'area=featuresettings;sa=sig'),
		array('ModifyGeneralSecuritySettings', 'area=securitysettings;sa=general'),
		array('ModifySpamSettings', 'area=securitysettings;sa=spam'),
		array('ModifyModerationSettings', 'area=securitysettings;sa=moderation'),
		array('ModifyGeneralModSettings', 'area=modsettings;sa=general'),
		// Mod authors if you want to be "real freaking good" then add any setting pages for your mod BELOW this line!
		array('ManageAttachmentSettings', 'area=manageattachments;sa=attachments'),
		array('ManageAvatarSettings', 'area=manageattachments;sa=avatars'),
		array('ModifyCalendarSettings', 'area=managecalendar;sa=settings'),
		array('EditBoardSettings', 'area=manageboards;sa=settings'),
		array('ModifyMailSettings', 'area=mailqueue;sa=settings'),
		array('ModifyNewsSettings', 'area=news;sa=settings'),
		array('GeneralPermissionSettings', 'area=permissions;sa=settings'),
		array('ModifyPostSettings', 'area=postsettings;sa=posts'),
		array('ModifyBBCSettings', 'area=postsettings;sa=bbc'),
		array('ModifyTopicSettings', 'area=postsettings;sa=topics'),
		array('EditSearchSettings', 'area=managesearch;sa=settings'),
		array('EditSmileySettings', 'area=smileys;sa=settings'),
		array('ModifyGeneralSettings', 'area=serversettings;sa=general'),
		array('ModifyDatabaseSettings', 'area=serversettings;sa=database'),
		array('ModifyCookieSettings', 'area=serversettings;sa=cookie'),
		array('ModifyCacheSettings', 'area=serversettings;sa=cache'),
		array('ModifyLanguageSettings', 'area=languages;sa=settings'),
		array('ModifyRegistrationSettings', 'area=regcenter;sa=settings'),
		array('ManageSearchEngineSettings', 'area=sengines;sa=settings'),
		array('ModifySubscriptionSettings', 'area=paidsubscribe;sa=settings'),
		array('ModifyPruningSettings', 'area=logs;sa=pruning'),
	);

	foreach ($settings_search as $setting_area)
	{
		// Get a list of their variables.
		$config_vars = $setting_area[0](true);

		foreach ($config_vars as $var)
			if (!empty($var[1]) && !in_array($var[0], array('permissions', 'switch')))
				$search_data['settings'][] = array($var[(isset($var[2]) && in_array($var[2], array('file', 'db'))) ? 0 : 1], $setting_area[1]);
	}

	$context['page_title'] = $txt['admin_search_results'];
	$context['search_results'] = array();

	$search_term = strtolower($context['search_term']);
	// Go through all the search data trying to find this text!
	foreach ($search_data as $section => $data)
	{
		foreach ($data as $item)
		{
			$found = false;
			if (!is_array($item[0]))
				$item[0] = array($item[0]);
			foreach ($item[0] as $term)
			{
				$lc_term = strtolower($term);
				if (strpos($lc_term, $search_term) !== false || (isset($txt[$term]) && strpos($txt[$term], $search_term) !== false) || (isset($txt['setting_' . $term]) && strpos($txt['setting_' . $term], $search_term) !== false))
				{
					$found = $term;
					break;
				}
			}

			if ($found)
			{
				// Format the name - and remove any descriptions the entry may have.
				$name = isset($txt[$found]) ? $txt[$found] : (isset($txt['setting_' . $found]) ? $txt['setting_' . $found] : $found);
				$name = preg_replace('~<(?:div|span)\sclass="smalltext">.+?</(?:div|span)>~', '', $name);

				$context['search_results'][] = array(
					'url' => (substr($item[1], 0, 4) == 'area' ? $scripturl . '?action=admin;' . $item[1] : $item[1]) . ';' . $context['session_var'] . '=' . $context['session_id'] . ((substr($item[1], 0, 4) == 'area' && $section == 'settings' ? '#' . $item[0][0] : '')),
					'name' => $name,
					'type' => $section,
					'help' => shorten_subject(isset($item[2]) ? strip_tags($helptxt[$item2]) : (isset($helptxt[$found]) ? strip_tags($helptxt[$found]) : ''), 255),
				);
			}
		}
	}
}

// All this does is pass through to manage members.
function AdminSearchMember()
{
	global $context, $sourcedir;

	require_once($sourcedir . '/ManageMembers.php');
	$_REQUEST['sa'] = 'query';

	$_POST['membername'] = $context['search_term'];

	ViewMembers();
}

// This file allows the user to search the SM online manual for a little of help.
function AdminSearchOM()
{
	global $context, $sourcedir;

	$docsURL = 'docs.simplemachines.org';
	$context['doc_scripturl'] = 'http://docs.simplemachines.org/index.php';

	// Set all the parameters search might expect.
	$postVars = array(
		'search' => $context['search_term'],
	);

	// Encode the search data.
	foreach ($postVars as $k => $v)
		$postVars[$k] = urlencode($k) . '=' . urlencode($v);

	// This is what we will send.
	$postVars = implode('&', $postVars);

	// Get the results from the doc site.
	require_once($sourcedir . '/Subs-Package.php');
	$search_results = fetch_web_data($context['doc_scripturl'] . '?action=search2&xml', $postVars);

	// If we didn't get any xml back we are in trouble - perhaps the doc site is overloaded?
	if (!$search_results || preg_match('~<' . '\?xml\sversion="\d+\.\d+"\sencoding=".+?"\?' . '>\s*(<smf>.+?</smf>)~is', $search_results, $matches) != true)
		fatal_lang_error('cannot_connect_doc_site');

	$search_results = $matches[1];

	// Otherwise we simply walk through the XML and stick it in context for display.
	$context['search_results'] = array();
	loadClassFile('Class-Package.php');

	// Get the results loaded into an array for processing!
	$results = new xmlArray($search_results, false);

	// Move through the smf layer.
	if (!$results->exists('smf'))
		fatal_lang_error('cannot_connect_doc_site');
	$results = $results->path('smf[0]');

	// Are there actually some results?
	if (!$results->exists('noresults') && !$results->exists('results'))
		fatal_lang_error('cannot_connect_doc_site');
	elseif ($results->exists('results'))
	{
		foreach ($results->set('results/result') as $result)
		{
			if (!$result->exists('messages'))
				continue;

			$context['search_results'][$result->fetch('id')] = array(
				'topic_id' => $result->fetch('id'),
				'relevance' => $result->fetch('relevance'),
				'board' => array(
					'id' => $result->fetch('board/id'),
					'name' => $result->fetch('board/name'),
					'href' => $result->fetch('board/href'),
				),
				'category' => array(
					'id' => $result->fetch('category/id'),
					'name' => $result->fetch('category/name'),
					'href' => $result->fetch('category/href'),
				),
				'messages' => array(),
			);

			// Add the messages.
			foreach ($result->set('messages/message') as $message)
				$context['search_results'][$result->fetch('id')]['messages'][] = array(
					'id' => $message->fetch('id'),
					'subject' => $message->fetch('subject'),
					'body' => $message->fetch('body'),
					'time' => $message->fetch('time'),
					'timestamp' => $message->fetch('timestamp'),
					'start' => $message->fetch('start'),
					'author' => array(
						'id' => $message->fetch('author/id'),
						'name' => $message->fetch('author/name'),
						'href' => $message->fetch('author/href'),
					),
				);
		}
	}
}

// This function decides which log to load.
function AdminLogs()
{
	global $sourcedir, $context, $txt, $scripturl;

	// These are the logs they can load.
	$log_functions = array(
		'errorlog' => array('ManageErrors.php', 'ViewErrorLog'),
		'adminlog' => array('Modlog.php', 'ViewModlog'),
		'modlog' => array('Modlog.php', 'ViewModlog'),
		'banlog' => array('ManageBans.php', 'BanLog'),
		'spiderlog' => array('ManageSearchEngines.php', 'SpiderLogs'),
		'tasklog' => array('ManageScheduledTasks.php', 'TaskLog'),
		'pruning' => array('ManageSettings.php', 'ModifyPruningSettings'),
	);

	$sub_action = isset($_REQUEST['sa']) && isset($log_functions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'errorlog';
	// If it's not got a sa set it must have come here for first time, pretend error log should be reversed.
	if (!isset($_REQUEST['sa']))
		$_REQUEST['desc'] = true;

	// Setup some tab stuff.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['logs'],
		'help' => '',
		'description' => $txt['maintain_info'],
		'tabs' => array(
			'errorlog' => array(
				'url' => $scripturl . '?action=admin;area=logs;sa=errorlog;desc',
				'description' => sprintf($txt['errlog_desc'], $txt['remove']),
			),
			'adminlog' => array(
				'description' => $txt['admin_log_desc'],
			),
			'modlog' => array(
				'description' => $txt['moderation_log_desc'],
			),
			'banlog' => array(
				'description' => $txt['ban_log_description'],
			),
			'spiderlog' => array(
				'description' => $txt['spider_log_desc'],
			),
			'tasklog' => array(
				'description' => $txt['scheduled_log_desc'],
			),
			'pruning' => array(
				'description' => $txt['pruning_log_desc'],
			),
		),
	);

	require_once($sourcedir . '/' . $log_functions[$sub_action][0]);
	$log_functions[$sub_action][1]();
}

?>