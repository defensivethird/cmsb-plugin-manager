<?php
/**
 * Plugin Manager - Helper Functions
 */

/**
 * Load plugin settings from JSON file
 *
 * @return array Settings array
 */
function pluginManager_loadSettings(): array
{
	$file = __DIR__ . '/pluginManager_settings.json';
	$defaults = pluginManager_getDefaultSettings();

	if (!file_exists($file)) {
		pluginManager_saveSettings($defaults);
		return $defaults;
	}

	$json = file_get_contents($file);
	$settings = json_decode($json, true);

	return array_merge($defaults, $settings ?: []);
}

/**
 * Save plugin settings to JSON file
 *
 * @param array $settings Settings to save
 * @return bool Success status
 */
function pluginManager_saveSettings(array $settings): bool
{
	$file = __DIR__ . '/pluginManager_settings.json';
	$json = json_encode($settings, JSON_PRETTY_PRINT);
	return file_put_contents($file, $json) !== false;
}

/**
 * Get default settings
 *
 * @return array Default settings
 */
function pluginManager_getDefaultSettings(): array
{
	return [
		'showInactivePlugins' => true,
		'showSystemPlugins' => true,
		'groupByStatus' => true,
		'checkForUpdates' => true,
		'updateCheckInterval' => 86400, // 24 hours in seconds
		'lastUpdateCheck' => 0,
		'pluginSortOrder' => [], // Array of plugin paths in custom sort order
	];
}

/**
 * Get all plugins with their action links
 *
 * @return array Array of plugins with menu items
 */
function pluginManager_getPluginsWithActions(): array
{
	$allPlugins = getPluginList(false);
	$pluginsWithActions = [];

	foreach ($allPlugins as $path => $pluginData) {
		// Get registered actions for this plugin
		$actions = pluginManager_getPluginActions($path, $pluginData);

		$pluginsWithActions[] = [
			'path' => $path,
			'data' => $pluginData,
			'actions' => $actions,
		];
	}

	return $pluginsWithActions;
}

/**
 * Get plugin action links from global plugin actions registry
 *
 * @param string $pluginPath Path to plugin file
 * @param array $pluginData Plugin metadata
 * @return array Array of action links
 */
function pluginManager_getPluginActions(string $pluginPath, array $pluginData): array
{
	// Check if plugin has registered menu links
	if (empty($GLOBALS['PLUGIN_ACTION_MENU_LINKS'][$pluginPath])) {
		return [];
	}

	$actions = [];
	$menuLinksHTML = $GLOBALS['PLUGIN_ACTION_MENU_LINKS'][$pluginPath];

	// Parse the HTML links to extract action information
	// Links are in format: <a href="?_pluginAction=functionName">Link Text</a>
	if (preg_match_all('/<a[^>]+href=["\']\?_pluginAction=([^"\']+)["\']>([^<]+)<\/a>/i', $menuLinksHTML, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$actions[] = [
				'name' => html_entity_decode(strip_tags($match[2]), ENT_QUOTES, 'UTF-8'),
				'url' => '?_pluginAction=' . urlencode($match[1]),
				'actionName' => $match[1],
			];
		}
	}

	return $actions;
}

/**
 * Get plugin statistics
 *
 * @return array Statistics
 */
function pluginManager_getStats(): array
{
	$allPlugins = getPluginList(false);
	$activePlugins = getPluginList(true);

	$stats = [
		'total' => count($allPlugins),
		'active' => count($activePlugins),
		'inactive' => count($allPlugins) - count($activePlugins),
		'system' => 0,
		'custom' => 0,
	];

	foreach ($allPlugins as $plugin) {
		if ($plugin['isSystemPlugin']) {
			$stats['system']++;
		} else {
			$stats['custom']++;
		}
	}

	return $stats;
}

/**
 * Render the home dashboard (called when replaceHomepage is enabled)
 */
function pluginManager_renderHomeDashboard(): void
{
	global $CURRENT_USER;

	$settings = pluginManager_loadSettings();
	$stats = pluginManager_getStats();
	$plugins = pluginManager_getPluginsWithActions();
	$csrfToken = $_SESSION['_csrf'] ?? $_SESSION['_CSRFToken'] ?? ''; // Support both 3.82 (_csrf) and 3.81 (_CSRFToken)

	// Check for updates
	$updateInfo = pluginManager_checkForUpdates();

	// Apply custom sort order
	$plugins = pluginManager_sortPlugins($plugins, $settings['pluginSortOrder']);

	// Filter based on settings
	if (!$settings['showInactivePlugins']) {
		$plugins = array_filter($plugins, function ($p) {
			return $p['data']['isActive'];
		});
	}

	if (!$settings['showSystemPlugins']) {
		$plugins = array_filter($plugins, function ($p) {
			return !$p['data']['isSystemPlugin'];
		});
	}

	// Group by status if enabled
	if ($settings['groupByStatus']) {
		$activePlugins = array_filter($plugins, function ($p) {
			return $p['data']['isActive'];
		});
		$inactivePlugins = array_filter($plugins, function ($p) {
			return !$p['data']['isActive'];
		});
	}
	?>

	<style>
		/* Screen reader only class */
		.sr-only {
			position: absolute;
			width: 1px;
			height: 1px;
			padding: 0;
			margin: -1px;
			overflow: hidden;
			clip: rect(0, 0, 0, 0);
			white-space: nowrap;
			border-width: 0;
		}
		.sr-only-focusable:focus,
		.sr-only-focusable:active {
			position: static;
			width: auto;
			height: auto;
			overflow: visible;
			clip: auto;
			white-space: normal;
		}

		/* Focus indicators */
		.btn:focus,
		.plugin-actions .btn:focus,
		a:focus {
			outline: 2px solid #0056b3;
			outline-offset: 2px;
			box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.25);
		}
		.plugin-card:focus-within {
			box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.25);
		}
		@media (prefers-contrast: high) {
			.btn:focus,
			a:focus {
				outline: 3px solid currentColor;
				outline-offset: 3px;
			}
		}

		.plugin-grid-row {
			display: flex;
			flex-wrap: wrap;
			margin-left: -15px;
			margin-right: -15px;
		}
		.plugin-grid-col {
			padding-left: 15px;
			padding-right: 15px;
			margin-bottom: 15px;
			display: flex;
		}
		@media (min-width: 768px) {
			.plugin-grid-col {
				width: 50%;
			}
		}
		@media (min-width: 992px) {
			.plugin-grid-col {
				width: 25%;
			}
		}
		.plugin-card {
			border: 1px solid #ddd;
			border-radius: 4px;
			padding: 12px;
			background: #fff;
			width: 100%;
			display: flex;
			flex-direction: column;
		}
		.plugin-card.inactive {
			opacity: 0.6;
			background: #f8f9fa;
		}
		.plugin-header {
			margin-bottom: 8px;
		}
		.plugin-title {
			font-size: 14px;
			font-weight: 600;
			margin: 0 0 5px 0;
			line-height: 1.3;
		}
		.plugin-meta {
			font-size: 11px;
			color: #495057;
			margin-bottom: 8px;
		}
		.plugin-description {
			margin-bottom: 10px;
			color: #495057;
			font-size: 12px;
			flex-grow: 1;
			line-height: 1.4;
		}
		.plugin-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 4px;
			margin-top: auto;
		}
		.plugin-actions .btn {
			padding: 4px 8px;
			font-size: 11px;
			line-height: 1.3;
		}
		.plugin-actions .btn i {
			font-size: 10px;
			margin-right: 3px;
		}
		.badge-status {
			display: inline-block;
			padding: 2px 5px;
			font-size: 9px;
			font-weight: 600;
			border-radius: 3px;
			text-transform: uppercase;
			margin-left: 5px;
		}
		.badge-active {
			background-color: #28a745;
			color: #fff;
		}
		.badge-inactive {
			background-color: #6c757d;
			color: #fff;
		}
		.badge-system {
			background-color: #007bff;
			color: #fff;
		}
		.badge-update {
			background-color: #d87400;
			color: #fff;
		}
		.update-notification {
			background-color: #fff3cd;
			border: 1px solid #ffc107;
			border-radius: 4px;
			padding: 8px 10px;
			margin-bottom: 8px;
			font-size: 11px;
		}
		.update-notification a {
			color: #856404;
			font-weight: 600;
			text-decoration: underline;
		}
	</style>

	<!-- Welcome message -->
	<div class="alert alert-info" style="margin-bottom: 20px;">
		<i class="fa-duotone fa-solid fa-circle-info" aria-hidden="true"></i>
		<strong><?= t('Welcome to the Plugin Manager Dashboard') ?></strong><br>
		<?= t('This enhanced dashboard provides centralized control over your CMS plugins. View active plugins, activate or deactivate them, access their settings, and manage all your extensions from one place. You can customize the display via the Plugin Manager settings - toggle visibility of inactive plugins, system plugins, and grouping preferences.') ?>
	</div>

	<!-- Statistics Cards -->
	<div class="row g-3 mb-4">
		<div class="col-6 col-lg-3">
			<div class="border rounded-3 p-3 h-100 text-center">
				<div class="text-uppercase small fw-semibold mb-3"><?= t('Total Plugins') ?></div>
				<div class="fs-2 fw-bold" style="color:#138496"><?= $stats['total'] ?></div>
			</div>
		</div>
		<div class="col-6 col-lg-3">
			<div class="border rounded-3 p-3 h-100 text-center">
				<div class="text-uppercase small fw-semibold mb-3"><?= t('Active') ?></div>
				<div class="fs-2 fw-bold" style="color:#28a745"><?= $stats['active'] ?></div>
			</div>
		</div>
		<div class="col-6 col-lg-3">
			<div class="border rounded-3 p-3 h-100 text-center">
				<div class="text-uppercase small fw-semibold mb-3"><?= t('Inactive') ?></div>
				<div class="fs-2 fw-bold" style="color:#6c757d"><?= $stats['inactive'] ?></div>
			</div>
		</div>
		<div class="col-6 col-lg-3">
			<div class="border rounded-3 p-3 h-100 text-center">
				<div class="text-uppercase small fw-semibold mb-3"><?= t('System') ?></div>
				<div class="fs-2 fw-bold" style="color:#0275d8"><?= $stats['system'] ?></div>
			</div>
		</div>
	</div>

	<?php if ($settings['groupByStatus']): ?>
		<!-- Active Plugins Section -->
		<?php if (!empty($activePlugins)): ?>
			<h3 style="margin-top: 30px; margin-bottom: 15px;">
				<i class="fa-duotone fa-solid fa-circle-check text-success" aria-hidden="true"></i>
				<?= t('Active Plugins') ?>
			</h3>
			<div class="plugin-grid-row">
			<?php foreach ($activePlugins as $plugin): ?>
				<div class="plugin-grid-col" data-plugin-path="<?= htmlencode($plugin['path']) ?>">
					<?php pluginManager_renderPluginCard($plugin, $updateInfo); ?>
				</div>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Inactive Plugins Section -->
		<?php if (!empty($inactivePlugins) && $settings['showInactivePlugins']): ?>
			<h3 style="margin-top: 30px; margin-bottom: 15px;">
				<i class="fa-duotone fa-solid fa-circle-xmark text-danger" aria-hidden="true"></i>
				<?= t('Inactive Plugins') ?>
			</h3>
			<div class="plugin-grid-row">
			<?php foreach ($inactivePlugins as $plugin): ?>
				<div class="plugin-grid-col" data-plugin-path="<?= htmlencode($plugin['path']) ?>">
					<?php pluginManager_renderPluginCard($plugin, $updateInfo); ?>
				</div>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php else: ?>
		<!-- All Plugins (ungrouped) -->
		<h3 style="margin-top: 30px; margin-bottom: 15px;">
			<i class="fa-duotone fa-solid fa-puzzle-piece" aria-hidden="true"></i>
			<?= t('All Plugins') ?>
		</h3>
		<div class="plugin-grid-row">
		<?php foreach ($plugins as $plugin): ?>
			<div class="plugin-grid-col" data-plugin-path="<?= htmlencode($plugin['path']) ?>">
				<?php pluginManager_renderPluginCard($plugin, $updateInfo); ?>
			</div>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if (empty($plugins)): ?>
		<div class="alert alert-warning">
			<i class="fa-duotone fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
			<?= t('No plugins found matching your filter criteria.') ?>
		</div>
	<?php endif; ?>

	<!-- Footer Info -->
	<?php if ($CURRENT_USER['isAdmin']): ?>
		<div class="alert alert-info" style="margin-top: 30px;">
			<strong><?= t('Administrators:') ?></strong>
			<?= t('This dashboard features kanban-style drag-and-drop organization. Click and drag any plugin card to reorder them to your preference, or use keyboard navigation (Alt+Arrow keys to move focused plugin). Your custom sort order will be saved automatically and persist across sessions. Customize additional display options via the Plugin Manager settings.') ?>
		</div>
	<?php endif; ?>

	<!-- Drag and Drop JavaScript -->
	<script>
	var pluginManagerCsrfToken = <?= json_encode($csrfToken) ?>;
	</script>
	<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Make all plugin grid rows sortable
		const grids = document.querySelectorAll('.plugin-grid-row');

		grids.forEach(function(grid) {
			new Sortable(grid, {
				animation: 150,
				handle: '.plugin-card',
				draggable: '.plugin-grid-col',
				ghostClass: 'sortable-ghost',
				chosenClass: 'sortable-chosen',
				dragClass: 'sortable-drag',
				onEnd: function(evt) {
					// Collect all plugin paths in current order
					const sortOrder = [];
					document.querySelectorAll('.plugin-grid-col').forEach(function(col) {
						const path = col.getAttribute('data-plugin-path');
						if (path) {
							sortOrder.push(path);
						}
					});

					// Save sort order via AJAX
					const formData = new FormData();
					formData.append('sortOrder', JSON.stringify(sortOrder));
					formData.append('_csrf', pluginManagerCsrfToken);

					fetch('?_pluginAction=pluginManager_saveSortOrder', {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							console.log('Sort order saved successfully');
						} else {
							console.error('Failed to save sort order:', data.error);
						}
					})
					.catch(error => {
						console.error('Error saving sort order:', error);
					});
				}
			});
		});

		// Keyboard navigation for drag-and-drop (Alt+Arrow keys)
		document.addEventListener('keydown', function(e) {
			if (!e.altKey) return;

			const focused = document.activeElement;
			const pluginCol = focused.closest('.plugin-grid-col');

			if (!pluginCol) return;

			let targetCol = null;

			if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
				targetCol = pluginCol.previousElementSibling;
				if (targetCol) {
					e.preventDefault();
					pluginCol.parentNode.insertBefore(pluginCol, targetCol);
					pluginCol.querySelector('.plugin-card')?.focus();
					saveSortOrderFromKeyboard();
				}
			} else if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
				targetCol = pluginCol.nextElementSibling;
				if (targetCol) {
					e.preventDefault();
					pluginCol.parentNode.insertBefore(targetCol, pluginCol);
					pluginCol.querySelector('.plugin-card')?.focus();
					saveSortOrderFromKeyboard();
				}
			}
		});

		function saveSortOrderFromKeyboard() {
			const sortOrder = [];
			document.querySelectorAll('.plugin-grid-col').forEach(function(col) {
				const path = col.getAttribute('data-plugin-path');
				if (path) {
					sortOrder.push(path);
				}
			});

			const formData = new FormData();
			formData.append('sortOrder', JSON.stringify(sortOrder));
			formData.append('_csrf', pluginManagerCsrfToken);

			fetch('?_pluginAction=pluginManager_saveSortOrder', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					console.log('Sort order saved successfully (keyboard)');
				}
			})
			.catch(error => {
				console.error('Error saving sort order:', error);
			});
		}
	});
	</script>

	<style>
		.plugin-grid-col {
			cursor: move;
			cursor: grab;
		}
		.plugin-grid-col:active {
			cursor: grabbing;
		}
		.sortable-ghost {
			opacity: 0.4;
		}
		.sortable-chosen {
			opacity: 0.8;
		}
		.sortable-drag {
			opacity: 1;
		}
	</style>

	<?php
}

/**
 * Render a single plugin card
 *
 * @param array $plugin Plugin data with actions
 * @param array $updateInfo Update check results
 */
function pluginManager_renderPluginCard(array $plugin, array $updateInfo = []): void
{
	$data = $plugin['data'];
	$actions = $plugin['actions'];
	$isActive = $data['isActive'];
	$isSystem = $data['isSystemPlugin'];

	// Check if update is available
	$updateAvailable = pluginManager_getPluginUpdate($data['name'], $updateInfo);
	?>

	<div class="plugin-card <?= !$isActive ? 'inactive' : '' ?>" tabindex="0" role="article" aria-label="<?= htmlencode($data['name']) ?> plugin">
		<div class="plugin-header">
			<div>
				<h4 class="plugin-title">
					<?= htmlencode($data['name']) ?>
					<?php if ($isActive): ?>
						<span class="badge-status badge-active"><?= t('Active') ?></span>
					<?php else: ?>
						<span class="badge-status badge-inactive"><?= t('Inactive') ?></span>
					<?php endif; ?>
					<?php if ($isSystem): ?>
						<span class="badge-status badge-system"><?= t('System') ?></span>
					<?php endif; ?>
					<?php if ($updateAvailable): ?>
						<span class="badge-status badge-update"><?= t('Update') ?></span>
					<?php endif; ?>
				</h4>
				<div class="plugin-meta">
					<?= t('Version') ?>: <?= htmlencode($data['version']) ?>
					<?php if ($updateAvailable): ?>
						<span style="color: #ff9800; font-weight: 600;">→ <?= htmlencode($updateAvailable['availableVersion']) ?></span>
					<?php endif; ?>
					<?php if (!empty($data['author'])): ?>
						| <?= t('by') ?> <?= htmlencode($data['author']) ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ($updateAvailable): ?>
			<div class="update-notification">
				<i class="fa-duotone fa-solid fa-circle-arrow-up" aria-hidden="true"></i>
				<strong><?= t('Update available') ?>:</strong>
				v<?= htmlencode($updateAvailable['availableVersion']) ?> (<?= htmlencode($updateAvailable['updateDate']) ?>)
				<?php if (!empty($updateAvailable['link'])): ?>
					- <a href="<?= htmlencode($updateAvailable['link']) ?>" target="_blank" rel="noopener"><?= t('Download') ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($data['description'])): ?>
			<div class="plugin-description">
				<?= htmlencode($data['description']) ?>
			</div>
		<?php endif; ?>

		<div class="plugin-actions">
			<?php if (!$isActive): ?>
				<a href="?_pluginAction=pluginManager_activatePlugin&file=<?= urlencode($plugin['path']) ?>"
				   class="btn btn-sm btn-success"
				   data-method="post">
					<i class="fa-duotone fa-solid fa-power-off" aria-hidden="true"></i>
					<?= t('Activate') ?>
				</a>
			<?php endif; ?>

			<?php if (!empty($actions)): ?>
				<?php foreach ($actions as $action): ?>
					<a href="<?= htmlencode($action['url']) ?>" class="btn btn-sm btn-primary">
						<i class="fa-duotone fa-solid fa-arrow-right" aria-hidden="true"></i>
						<?= htmlencode($action['name']) ?>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ($isActive): ?>
				<a href="?_pluginAction=pluginManager_deactivatePlugin&file=<?= urlencode($plugin['path']) ?>"
				   class="btn btn-sm btn-danger"
				   data-method="post"
				   onclick="return confirm('<?= t('Are you sure you want to deactivate this plugin?') ?>');">
					<i class="fa-duotone fa-solid fa-power-off" aria-hidden="true"></i>
					<?= t('Deactivate') ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php
}

/**
 * Fetch and parse RSS feed for plugin updates
 *
 * @param string $feedUrl RSS feed URL
 * @return array Array of available plugins with versions
 */
function pluginManager_fetchRSSFeed(string $feedUrl): array
{
	$availablePlugins = [];

	// Fetch RSS feed with timeout
	$context = stream_context_create([
		'http' => [
			'timeout' => 10,
			'user_agent' => 'CMS Builder Plugin Manager/1.0',
		],
	]);

	$rssContent = @file_get_contents($feedUrl, false, $context);
	if (!$rssContent) {
		return $availablePlugins;
	}

	// Parse XML
	libxml_use_internal_errors(true);
	$xml = simplexml_load_string($rssContent);
	libxml_clear_errors();

	if (!$xml || !isset($xml->channel->item)) {
		return $availablePlugins;
	}

	// Extract plugin information from each item
	foreach ($xml->channel->item as $item) {
		$title = (string) $item->title;

		// Parse title format: "Plugin Name v1.23 (updated Aug 21, 2025)"
		if (preg_match('/^(.+?)\s+v([\d.]+(?:\s*BETA)?)\s*\(updated\s+(.+?)\)/i', $title, $matches)) {
			$pluginName = trim($matches[1]);
			$version = trim($matches[2]);
			$updateDate = trim($matches[3]);

			$availablePlugins[$pluginName] = [
				'name' => $pluginName,
				'version' => $version,
				'updateDate' => $updateDate,
				'link' => (string) $item->link,
				'description' => (string) $item->description,
			];
		}
	}

	return $availablePlugins;
}

/**
 * Check for plugin updates from RSS feeds
 *
 * @param bool $forceCheck Force check even if within interval
 * @return array Update information
 */
function pluginManager_checkForUpdates(bool $forceCheck = false): array
{
	$settings = pluginManager_loadSettings();

	// Check if updates are enabled
	if (!$settings['checkForUpdates']) {
		return ['updates' => [], 'lastCheck' => 0];
	}

	// Check if we should skip (within interval)
	$now = time();
	if (!$forceCheck && ($now - $settings['lastUpdateCheck']) < $settings['updateCheckInterval']) {
		// Load cached results
		$cacheFile = __DIR__ . '/pluginManager_updates_cache.json';
		if (file_exists($cacheFile)) {
			$cached = json_decode(file_get_contents($cacheFile), true);
			return $cached ?: ['updates' => [], 'lastCheck' => $settings['lastUpdateCheck']];
		}
	}

	// Fetch from both RSS feeds
	$feeds = [
		'https://interactivetools.com/plugins/rss.php',
		'https://www.sagentic.dev/public/plugins/rss.php',
	];

	$availablePlugins = [];
	foreach ($feeds as $feedUrl) {
		$feedPlugins = pluginManager_fetchRSSFeed($feedUrl);
		$availablePlugins = array_merge($availablePlugins, $feedPlugins);
	}

	// Get installed plugins
	$installedPlugins = getPluginList(false);

	// Compare versions
	$updates = [];
	foreach ($installedPlugins as $pluginData) {
		$installedName = $pluginData['name'];
		$installedVersion = $pluginData['version'];

		// Check if this plugin exists in available updates
		if (isset($availablePlugins[$installedName])) {
			$availableVersion = $availablePlugins[$installedName]['version'];

			// Compare versions
			if (version_compare($availableVersion, $installedVersion, '>')) {
				$updates[$installedName] = [
					'currentVersion' => $installedVersion,
					'availableVersion' => $availableVersion,
					'link' => $availablePlugins[$installedName]['link'],
					'updateDate' => $availablePlugins[$installedName]['updateDate'],
				];
			}
		}
	}

	// Update last check time
	$settings['lastUpdateCheck'] = $now;
	pluginManager_saveSettings($settings);

	// Cache results
	$result = ['updates' => $updates, 'lastCheck' => $now];
	$cacheFile = __DIR__ . '/pluginManager_updates_cache.json';
	file_put_contents($cacheFile, json_encode($result, JSON_PRETTY_PRINT));

	return $result;
}

/**
 * Get update information for a specific plugin
 *
 * @param string $pluginName Plugin name
 * @param array $updateInfo Update check results
 * @return array|null Update info or null if no update
 */
function pluginManager_getPluginUpdate(string $pluginName, array $updateInfo): ?array
{
	return $updateInfo['updates'][$pluginName] ?? null;
}

/**
 * Sort plugins according to custom sort order
 *
 * @param array $plugins Array of plugins with their data
 * @param array $sortOrder Array of plugin paths in desired order
 * @return array Sorted plugins array
 */
function pluginManager_sortPlugins(array $plugins, array $sortOrder): array
{
	if (empty($sortOrder)) {
		return $plugins;
	}

	// Create a map of plugin path to plugin data
	$pluginMap = [];
	foreach ($plugins as $plugin) {
		$pluginMap[$plugin['path']] = $plugin;
	}

	// Build sorted array based on sortOrder
	$sorted = [];
	foreach ($sortOrder as $path) {
		if (isset($pluginMap[$path])) {
			$sorted[] = $pluginMap[$path];
			unset($pluginMap[$path]);
		}
	}

	// Append any plugins not in sort order at the end
	foreach ($pluginMap as $plugin) {
		$sorted[] = $plugin;
	}

	return $sorted;
}

/**
 * Save plugin sort order via AJAX
 */
function pluginManager_saveSortOrder(): void
{
	// CSRF protection
	security_dieOnInvalidCsrfToken();

	global $CURRENT_USER;

	// Security check - admins only
	if (!$CURRENT_USER['isAdmin']) {
		http_response_code(403);
		echo json_encode(['success' => false, 'error' => 'Unauthorized']);
		exit;
	}

	// Get sort order from POST (it's JSON encoded)
	$sortOrderJson = $_POST['sortOrder'] ?? '[]';
	$sortOrder = json_decode($sortOrderJson, true);

	if (!is_array($sortOrder)) {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid sort order']);
		exit;
	}

	// Validate that sort order contains valid plugin paths
	$validPlugins = getPluginList(false);
	$validPaths = array_keys($validPlugins);
	$sortOrder = array_filter($sortOrder, function($path) use ($validPaths) {
		return in_array($path, $validPaths);
	});

	// Load current settings
	$settings = pluginManager_loadSettings();
	$settings['pluginSortOrder'] = $sortOrder;

	// Save settings
	if (pluginManager_saveSettings($settings)) {
		echo json_encode(['success' => true, 'sortOrder' => $sortOrder]);
	} else {
		http_response_code(500);
		echo json_encode(['success' => false, 'error' => 'Failed to save settings']);
	}
	exit;
}

/**
 * Custom plugin activation handler that redirects back to Plugin Manager
 */
function pluginManager_activatePlugin(): void
{
	// Security checks
	security_dieUnlessPostForm();
	security_dieUnlessInternalReferer();
	security_dieOnInvalidCsrfToken();

	// Activate the plugin
	$file = request('file');
	activatePlugin($file);

	// Redirect back to Plugin Manager dashboard
	redirectBrowserToURL('?_pluginAction=pluginManager_adminDashboard', true);
}

/**
 * Custom plugin deactivation handler that redirects back to Plugin Manager
 */
function pluginManager_deactivatePlugin(): void
{
	// Security checks
	security_dieUnlessPostForm();
	security_dieUnlessInternalReferer();
	security_dieOnInvalidCsrfToken();

	// Deactivate the plugin
	$file = request('file');
	deactivatePlugin($file);

	// Redirect back to Plugin Manager dashboard
	redirectBrowserToURL('?_pluginAction=pluginManager_adminDashboard', true);
}
