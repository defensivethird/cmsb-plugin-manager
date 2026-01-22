<?php
/**
 * Plugin Manager - Admin UI Pages
 */

/**
 * Generate plugin navigation bar
 *
 * @param string $currentPage Current page identifier
 * @return string HTML for navigation bar
 */
function pluginManager_getPluginNav(string $currentPage): string
{
	$pages = [
		'dashboard' => ['label' => t('Dashboard'), 'action' => 'pluginManager_adminDashboard'],
		'settings' => ['label' => t('Settings'), 'action' => 'pluginManager_adminSettings'],
		'help' => ['label' => t('Help'), 'action' => 'pluginManager_adminHelp'],
	];

	$html = '<nav aria-label="' . t('Plugin Manager navigation') . '"><div class="btn-group" role="group" style="margin-bottom:20px">';
	foreach ($pages as $key => $page) {
		$isActive = ($key === $currentPage);
		$btnClass = $isActive ? 'btn btn-primary' : 'btn btn-default';
		$ariaCurrent = $isActive ? ' aria-current="page"' : '';
		$html .= '<a href="?_pluginAction=' . urlencode($page['action']) . '" class="' . $btnClass . '"' . $ariaCurrent . '>' . $page['label'] . '</a>';
	}
	$html .= '</div></nav>';

	return $html;
}

/**
 * Dashboard page - Plugin overview and statistics
 */
function pluginManager_adminDashboard(): void
{
	$adminUI = [];

	$adminUI['PAGE_TITLE'] = [
		t("Plugins") => '?menu=admin&action=plugins',
		t("Plugin Manager"),
	];

	// Plugin navigation
	$content = pluginManager_getPluginNav('dashboard');

	// Render the same content as the homepage dashboard
	ob_start();
	pluginManager_renderHomeDashboard();
	$content .= ob_get_clean();

	$adminUI['CONTENT'] = $content;
	adminUI($adminUI);
}

/**
 * Settings page
 */
function pluginManager_adminSettings(): void
{
	$message = '';
	$messageType = 'info';

	// Load current settings
	$settings = pluginManager_loadSettings();

	// Handle form submission
	if (($_REQUEST['saveSettings'] ?? '')) {
		security_dieOnInvalidCsrfToken();

		// Update from form
		$settings['showInactivePlugins'] = isset($_POST['showInactivePlugins']);
		$settings['showSystemPlugins'] = isset($_POST['showSystemPlugins']);
		$settings['groupByStatus'] = isset($_POST['groupByStatus']);
		$settings['checkForUpdates'] = isset($_POST['checkForUpdates']);

		if (pluginManager_saveSettings($settings)) {
			$message = t('Settings saved successfully.');
			$messageType = 'success';
			$settings = pluginManager_loadSettings(); // Reload
		} else {
			$message = t('Error saving settings.');
			$messageType = 'danger';
		}
	}

	$adminUI = [];
	$adminUI['PAGE_TITLE'] = [
		t("Plugins") => '?menu=admin&action=plugins',
		t("Plugin Manager") => '?_pluginAction=pluginManager_adminDashboard',
		t("Settings"),
	];

	// Use adminUI form handling
	$adminUI['FORM'] = ['name' => 'settingsForm', 'autocomplete' => 'off'];
	$adminUI['HIDDEN_FIELDS'] = [
		['name' => 'saveSettings', 'value' => '1'],
		['name' => '_pluginAction', 'value' => 'pluginManager_adminSettings'],
	];
	$adminUI['BUTTONS'] = [
		['name' => '_action=save', 'label' => t('Save Settings')],
	];

	$content = '';
	$content .= pluginManager_getPluginNav('settings');

	// Display message if any
	if ($message) {
		$content .= '<div class="alert alert-' . $messageType . '" role="alert">' . htmlencode($message) . '</div>';
	}

	// Display Options
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Display Options') . '</div></div>';

	$content .= '<div class="form-horizontal">';

	// Show Inactive Plugins
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label">' . t('Plugin Visibility') . '</div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label for="field_showInactivePlugins">';
	$content .= '<input type="hidden" name="showInactivePlugins" value="0">';
	$checked = $settings['showInactivePlugins'] ? ' checked' : '';
	$content .= '<input type="checkbox" name="showInactivePlugins" id="field_showInactivePlugins" value="1"' . $checked . '> ';
	$content .= t('Show inactive plugins on dashboard');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('When unchecked, only active plugins will be displayed on the dashboard.') . '</p>';
	$content .= '</div></div>';

	// Show System Plugins
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label"></div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label for="field_showSystemPlugins">';
	$content .= '<input type="hidden" name="showSystemPlugins" value="0">';
	$checked = $settings['showSystemPlugins'] ? ' checked' : '';
	$content .= '<input type="checkbox" name="showSystemPlugins" id="field_showSystemPlugins" value="1"' . $checked . '> ';
	$content .= t('Show system plugins on dashboard');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('When unchecked, system plugins will be hidden from the dashboard.') . '</p>';
	$content .= '</div></div>';

	// Group by Status
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label"></div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label for="field_groupByStatus">';
	$content .= '<input type="hidden" name="groupByStatus" value="0">';
	$checked = $settings['groupByStatus'] ? ' checked' : '';
	$content .= '<input type="checkbox" name="groupByStatus" id="field_groupByStatus" value="1"' . $checked . '> ';
	$content .= t('Group plugins by status (Active/Inactive)');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('When enabled, plugins will be separated into "Active" and "Inactive" sections.') . '</p>';
	$content .= '</div></div>';

	// Plugin Updates Section
	$content .= '<div class="separator" style="margin-top:20px"><div>' . t('Plugin Updates') . '</div></div>';

	// Check for Updates
	$content .= '<div class="form-group">';
	$content .= '<div class="col-sm-2 control-label">' . t('Update Checking') . '</div>';
	$content .= '<div class="col-sm-10">';
	$content .= '<div class="checkbox"><label for="field_checkForUpdates">';
	$content .= '<input type="hidden" name="checkForUpdates" value="0">';
	$checked = $settings['checkForUpdates'] ? ' checked' : '';
	$content .= '<input type="checkbox" name="checkForUpdates" id="field_checkForUpdates" value="1"' . $checked . '> ';
	$content .= t('Check for plugin updates automatically');
	$content .= '</label></div>';
	$content .= '<p class="help-block">' . t('When enabled, the Plugin Manager will check for updates from Interactive Tools and Sagentic Web Design RSS feeds. Update checks are cached for 24 hours.') . '</p>';
	$content .= '</div></div>';

	$content .= '</div>'; // end form-horizontal

	$adminUI['CONTENT'] = $content;
	adminUI($adminUI);
}

/**
 * Help page
 */
function pluginManager_adminHelp(): void
{
	$adminUI = [];

	$adminUI['PAGE_TITLE'] = [
		t("Plugins") => '?menu=admin&action=plugins',
		t("Plugin Manager") => '?_pluginAction=pluginManager_adminDashboard',
		t("Help"),
	];

	$content = '';

	// Plugin navigation
	$content .= pluginManager_getPluginNav('help');

	// Overview Section
	$content .= '<div class="separator"><div>' . t('Overview') . '</div></div>';
	$content .= '<p>' . t('The Plugin Manager provides an enhanced dashboard for managing all your CMS Builder plugins from one central location.') . '</p>';
	$content .= '<p><strong>' . t('Features:') . '</strong></p>';
	$content .= '<ul>';
	$content .= '<li><strong>Plugin Dashboard</strong> - Visual grid displaying all installed plugins with status badges</li>';
	$content .= '<li><strong>Direct Access</strong> - Quick links to each plugin\'s admin pages and settings</li>';
	$content .= '<li><strong>Plugin Statistics</strong> - Overview of total, active, inactive, and system plugins</li>';
	$content .= '<li><strong>Customizable Display</strong> - Filter and group plugins by status and type</li>';
	$content .= '<li><strong>Drag and Drop</strong> - Reorder plugin cards with Kanban-style sorting</li>';
	$content .= '<li><strong>Update Notifications</strong> - Check for newer plugin versions automatically</li>';
	$content .= '</ul>';

	// Installation Section
	$content .= '<div class="separator"><div>' . t('Installation') . '</div></div>';
	$content .= '<ol>';
	$content .= '<li>Copy the <code>pluginManager</code> folder to your plugins directory</li>';
	$content .= '<li>Ensure PHP files have proper permissions: <code>chmod 644 /path/to/plugins/pluginManager/*.php</code></li>';
	$content .= '<li>Log into the CMSB admin area and navigate to the Plugins menu</li>';
	$content .= '<li>The plugin will automatically initialize with default settings</li>';
	$content .= '<li>Verify installation by visiting <strong>Plugins &gt; Plugin Manager &gt; Dashboard</strong></li>';
	$content .= '<li>Go to <strong>Plugins &gt; Plugin Manager &gt; Settings</strong> to customize display options</li>';
	$content .= '</ol>';

	// Getting Started
	$content .= '<div class="separator"><div>' . t('Getting Started') . '</div></div>';
	$content .= '<ol>';
	$content .= '<li><strong>' . t('Activate the plugin') . '</strong> - Install and activate from the Plugins page</li>';
	$content .= '<li><strong>' . t('View dashboard') . '</strong> - Access via Plugins menu or visit the Dashboard tab</li>';
	$content .= '<li><strong>' . t('Customize display') . '</strong> - Adjust settings to show/hide inactive and system plugins</li>';
	$content .= '<li><strong>' . t('Reorder plugins') . '</strong> - Drag and drop cards to arrange them in your preferred order</li>';
	$content .= '<li><strong>' . t('Enable updates') . '</strong> - Turn on update checking to receive notifications of new versions</li>';
	$content .= '</ol>';

	// Configuration Section
	$content .= '<div class="separator"><div>' . t('Configuration') . '</div></div>';
	$content .= '<p>All settings are configured through the admin interface at <strong>Plugins &gt; Plugin Manager &gt; Settings</strong>.</p>';
	$content .= '<div class="table-responsive">';
	$content .= '<table class="table table-striped">';
	$content .= '<thead><tr><th>' . t('Setting') . '</th><th>' . t('Description') . '</th><th>' . t('Default') . '</th></tr></thead>';
	$content .= '<tbody>';
	$content .= '<tr><td>Show inactive plugins</td><td>Display inactive plugins on dashboard</td><td>Enabled</td></tr>';
	$content .= '<tr><td>Show system plugins</td><td>Display system/core plugins on dashboard</td><td>Enabled</td></tr>';
	$content .= '<tr><td>Group by status</td><td>Separate plugins into Active/Inactive sections</td><td>Disabled</td></tr>';
	$content .= '<tr><td>Check for updates</td><td>Automatically check RSS feeds for newer versions</td><td>Enabled</td></tr>';
	$content .= '</tbody></table>';
	$content .= '</div>';

	// Features Detail
	$content .= '<div class="separator" style="margin-top:25px"><div>' . t('Features') . '</div></div>';
	$content .= '<p><strong>Plugin Cards:</strong></p>';
	$content .= '<ul>';
	$content .= '<li>Visual cards with status badges (Active, Inactive, System)</li>';
	$content .= '<li>Display plugin name, version, author, and description</li>';
	$content .= '<li>Action buttons for each plugin\'s admin pages</li>';
	$content .= '<li>Update notification badges when newer versions available</li>';
	$content .= '</ul>';
	$content .= '<p><strong>Drag and Drop Sorting:</strong></p>';
	$content .= '<ul>';
	$content .= '<li>Reorder plugin cards by dragging them</li>';
	$content .= '<li>Custom sort order saves automatically via AJAX</li>';
	$content .= '<li>Persistent arrangement across sessions</li>';
	$content .= '<li>Admin-only sorting capability</li>';
	$content .= '</ul>';
	$content .= '<p><strong>Update Checking:</strong></p>';
	$content .= '<ul>';
	$content .= '<li>Checks Interactive Tools and Sagentic Web Design RSS feeds</li>';
	$content .= '<li>Update badges displayed on plugin cards</li>';
	$content .= '<li>24-hour cache to minimize external requests</li>';
	$content .= '<li>Can be disabled in Settings</li>';
	$content .= '</ul>';

	// Requirements Section
	$content .= '<div class="separator"><div>' . t('Requirements') . '</div></div>';
	$content .= '<ul>';
	$content .= '<li>CMS Builder 3.50 or higher</li>';
	$content .= '<li>PHP 7.1 or higher (for nullable type hints)</li>';
	$content .= '<li>Write access to plugin directory (for settings.json)</li>';
	$content .= '<li>JavaScript enabled for drag-and-drop functionality</li>';
	$content .= '<li>External CDN access for SortableJS (optional, for drag-and-drop)</li>';
	$content .= '</ul>';

	// Troubleshooting
	$content .= '<div class="separator" style="margin-top:30px"><div>' . t('Troubleshooting') . '</div></div>';
	$content .= '<p><strong>Settings Not Saving</strong></p>';
	$content .= '<ul>';
	$content .= '<li>Check write permissions on the <code>pluginManager</code> folder</li>';
	$content .= '<li>Verify <code>pluginManager_settings.json</code> is writable</li>';
	$content .= '<li>Check PHP error logs for permission issues</li>';
	$content .= '</ul>';
	$content .= '<p><strong>Drag and Drop Not Working</strong></p>';
	$content .= '<ul>';
	$content .= '<li>Verify JavaScript is enabled in your browser</li>';
	$content .= '<li>Check that SortableJS CDN is accessible</li>';
	$content .= '<li>Try clearing browser cache and reload</li>';
	$content .= '</ul>';
	$content .= '<p><strong>Update Checks Failing</strong></p>';
	$content .= '<ul>';
	$content .= '<li>Verify <code>allow_url_fopen</code> is enabled in PHP</li>';
	$content .= '<li>Check server can access external RSS feeds</li>';
	$content .= '<li>Disable update checking in Settings if not needed</li>';
	$content .= '</ul>';

	// Support
	$content .= '<div class="separator"><div>' . t('Version Information') . '</div></div>';
	$content .= '<p><strong>Version:</strong> ' . htmlencode($GLOBALS['PLUGINMANAGER_VERSION']) . '</p>';
	$content .= '<p><strong>Author:</strong> <a href="https://www.sagentic.com" target="_blank" rel="noopener">Sagentic Web Design <span class="sr-only">' . t('(opens in new tab)') . '</span></a></p>';

	$adminUI['CONTENT'] = $content;
	adminUI($adminUI);
}
