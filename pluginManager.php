<?php
/*
Plugin Name: Plugin Manager
Description: Enhanced plugin management dashboard for CMS Builder - displays active plugins, menu items, and provides centralized control
Version: 1.02
CMS Version Required: 3.50
Author: Sagentic Web Design
Author URI: https://www.sagentic.com
*/

// Don't run from command-line
if (inCLI()) {
	return;
}

// Plugin constants
$GLOBALS['PLUGINMANAGER_VERSION'] = '1.02';

// Load helper functions
require_once __DIR__ . '/pluginManager_functions.php';

// Admin UI - only load when in admin area
if (defined('IS_CMS_ADMIN')) {
	require_once __DIR__ . '/pluginManager_admin.php';

	// Register plugin action handlers
	pluginAction_addHandlerAndLink(t('Dashboard'), 'pluginManager_adminDashboard', 'admins');
	pluginAction_addHandlerAndLink(t('Settings'), 'pluginManager_adminSettings', 'admins');
	pluginAction_addHandlerAndLink(t('Help'), 'pluginManager_adminHelp', 'admins');

	// Register AJAX handler for sort order
	pluginAction_addHandler('pluginManager_saveSortOrder', 'admins');

	// Register custom activation/deactivation handlers
	pluginAction_addHandler('pluginManager_activatePlugin', 'admins');
	pluginAction_addHandler('pluginManager_deactivatePlugin', 'admins');
}
