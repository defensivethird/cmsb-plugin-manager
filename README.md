# Plugin Manager for CMS Builder

Enhanced plugin management dashboard for CMS Builder that provides a centralized control panel for viewing, activating, and managing all your plugins.

> **Note:** This plugin only works with CMS Builder, available for download at https://www.interactivetools.com/download/

## Overview

The Plugin Manager provides an improved way to manage and access your CMS Builder plugins. It offers a comprehensive dashboard accessible from the Plugins page that displays all your installed plugins in an easy-to-scan grid layout, with direct access to activate/deactivate plugins and access their admin pages.

This plugin is designed with future expansion in mind, leaving room for custom widgets, charts, and other dashboard enhancements that will make your CMS experience even more productive.

## Features

### Enhanced Dashboard

-   **4-Column Grid Layout**: Plugins displayed in an attractive responsive card grid (4 columns on desktop, 2 on tablet, 1 on mobile)
-   **Visual Plugin Cards**: Each plugin shown in a card format with status badges
-   **Plugin Management**: Activate or deactivate plugins directly from the dashboard with one click
-   **Direct Access**: Quick links to all plugin admin pages and settings
-   **Statistics Overview**: At-a-glance view of total plugins, active/inactive counts, and system plugins

### Customizable Display

-   **Filter Inactive Plugins**: Choose whether to show or hide inactive plugins
-   **Hide System Plugins**: Optionally hide core system plugins to focus on your custom tools
-   **Group by Status**: Organize plugins into "Active" and "Inactive" sections
-   **Status Badges**: Visual indicators showing active/inactive and system plugin status
-   **Kanban-Style Drag-and-Drop**: Reorder plugin cards by dragging them to your preferred position
-   **Persistent Sort Order**: Custom plugin arrangement is automatically saved and maintained across sessions

### Plugin Management

Each plugin card displays:

-   Plugin name and version
-   Author information
-   Description of functionality
-   Active/Inactive status badge
-   System plugin indicator
-   **Activate button** (green) for inactive plugins
-   Action buttons linking to the plugin's admin pages (Dashboard, Settings, etc.)
-   **Deactivate button** (red) for active plugins - with confirmation dialog

### Version Checking & Updates

-   **Automatic Update Detection**: Checks for available plugin updates from Interactive Tools and Sagentic Web Design RSS feeds
-   **Update Badges**: Visual indicators on plugin cards when newer versions are available
-   **Version Comparison**: Shows current version vs. available version
-   **24-Hour Cache**: Update checks are cached to minimize server requests
-   **Configurable**: Enable or disable update checking via Settings

### Future Ready

The dashboard design includes reserved space for:

-   Custom widget areas
-   Dashboard charts and analytics
-   Quick action shortcuts
-   Plugin usage statistics
-   Customizable layout options

## Installation

### Step 1: Upload Plugin Files

1.  Upload the entire `pluginManager` folder to:
    ```
    /cmsb/plugins/pluginManager/
    ```

2.  Ensure all files are present:
    -   `pluginManager.php`
    -   `pluginManager_admin.php`
    -   `pluginManager_functions.php`
    -   `LICENSE`
    -   `CHANGELOG.md`
    -   `README.md` (this file)

### Step 2: Activate the Plugin

1.  Log in to your CMS Builder admin panel
2.  Go to **Admin > Plugins**
3.  Find **Plugin Manager** in the inactive plugins list
4.  Click **Activate**

### Step 3: Access the Dashboard

1.  After activation, you'll see three new action links under the Plugin Manager in the Plugins page: **Dashboard**, **Settings**, and **Help**
2.  Click **Dashboard** to view your plugin management interface

### Step 4: Configure Settings (Optional)

1.  Click **Settings** from the Plugin Manager action links
2.  Configure display options:
    -   Show/hide inactive plugins
    -   Show/hide system plugins
    -   Enable plugin grouping by status
3.  Click **Save Settings**

## Usage

### Accessing the Dashboard

-   **Via Plugins Page**: Go to **Admin > Plugins**, then click **Dashboard** under the Plugin Manager
-   **Direct Link**: Bookmark `?_pluginAction=pluginManager_adminDashboard` for instant access

### Managing Plugins

From the dashboard, you can:

1.  **View All Plugins**: See every installed plugin with status indicators
2.  **Activate Plugins**: Click the green **Activate** button on inactive plugins
3.  **Deactivate Plugins**: Click the red **Deactivate** button on active plugins
4.  **Access Plugin Settings**: Click action buttons to jump directly to plugin admin pages
5.  **View Statistics**: Monitor how many plugins are active, inactive, etc.
6.  **Reorder Plugins**: Drag and drop plugin cards to arrange them in your preferred order

### Organizing with Drag-and-Drop

The Plugin Manager features kanban-style organization with both mouse and keyboard support:

**Mouse Navigation:**
1.  **Click and Hold**: Click on any plugin card and hold the mouse button
2.  **Drag**: Move your mouse to reposition the plugin card
3.  **Drop**: Release the mouse button to place the card in its new position

**Keyboard Navigation:**
1.  **Tab**: Navigate to a plugin card using the Tab key
2.  **Alt+Arrow Keys**: Move the focused plugin card up/down or left/right
   -   Alt+Up or Alt+Left: Move plugin earlier in the list
   -   Alt+Down or Alt+Right: Move plugin later in the list

**Auto-Save**: Your custom order is saved automatically in the background (works for both mouse and keyboard)

**Persistent**: Your arrangement persists across browser sessions and page refreshes

This kanban-style approach makes it easy to organize plugins by frequency of use, category, or any system that works for you. The keyboard navigation ensures full accessibility for users who cannot use a mouse.

### Customizing the Display

Go to **Plugin Manager > Settings** to customize:

-   **Plugin Filters**: Show/hide inactive or system plugins
-   **Grouping**: Organize plugins by active/inactive status

### Viewing Plugin Details

Each plugin card shows:

-   **Name**: The plugin's display name
-   **Status Badge**: Green "Active" or gray "Inactive"
-   **System Badge**: Blue badge for core system plugins
-   **Version**: Plugin version number
-   **Author**: Plugin author name
-   **Description**: What the plugin does
-   **Action Buttons**: Links to the plugin's admin pages

## Settings Reference

### Display Options

**Show inactive plugins on dashboard**

-   Controls visibility of inactive plugins
-   Helps focus on actively-used tools
-   Default: Enabled

**Show system plugins on dashboard**

-   Controls visibility of system plugins
-   System plugins are core CMS functionality
-   Hiding them reduces clutter for daily use
-   Default: Enabled

**Group plugins by status (Active/Inactive)**

-   Organizes plugins into separate sections
-   Makes it easier to distinguish active from inactive plugins
-   Default: Enabled

**Check for plugin updates automatically**

-   Enables automatic checking for plugin updates from RSS feeds
-   Checks Interactive Tools and Sagentic Web Design plugin libraries
-   Update checks are cached for 24 hours
-   Update badges appear on plugin cards when newer versions are available
-   Default: Enabled

## Technical Details

### Requirements

-   **CMS Builder Version**: 3.50 or higher
-   **PHP Version**: 7.1 or higher (uses nullable type hints)
-   **User Access**: Admin privileges required
-   **JavaScript**: Required for drag-and-drop sorting
-   **allow_url_fopen**: Optional, required for update checking feature

### File Structure

```
pluginManager/
├── pluginManager.php                    # Main plugin file
├── pluginManager_admin.php              # Admin UI pages
├── pluginManager_functions.php          # Helper functions
├── pluginManager_settings.json          # Settings storage (auto-created)
├── LICENSE                              # MIT License
├── CHANGELOG.md                         # Version history
└── README.md                            # This file
```

### Settings Storage

Settings are stored in JSON format:

-   **Location**: `/cmsb/plugins/pluginManager/pluginManager_settings.json`
-   **Format**: JSON
-   **Auto-created**: Yes, on first use
-   **Editable**: Via admin Settings page only

### Functions

**Core Functions:**

-   `pluginManager_loadSettings()`: Load settings from JSON file
-   `pluginManager_saveSettings()`: Save settings to JSON file
-   `pluginManager_getPluginsWithActions()`: Get all plugins with their menu items
-   `pluginManager_getStats()`: Get plugin statistics
-   `pluginManager_renderHomeDashboard()`: Render the main dashboard
-   `pluginManager_renderPluginCard()`: Render individual plugin card
-   `pluginManager_sortPlugins()`: Apply custom sort order to plugin array
-   `pluginManager_checkForUpdates()`: Check RSS feeds for plugin updates
-   `pluginManager_fetchRSSFeed()`: Fetch and parse RSS feed data

**Admin Functions:**

-   `pluginManager_adminDashboard()`: Plugin manager dashboard page
-   `pluginManager_adminSettings()`: Settings configuration page
-   `pluginManager_adminHelp()`: Help and documentation page

**AJAX Handlers:**

-   `pluginManager_saveSortOrder()`: Save custom plugin sort order (admin-only)

## Troubleshooting

### Dashboard Not Showing

1.  Check that the plugin is activated in **Admin > Plugins**
2.  Look for the **Dashboard** link under Plugin Manager in the Plugins page
3.  Try accessing directly: `?_pluginAction=pluginManager_adminDashboard`
4.  Clear browser cache and refresh

### Plugin Actions Not Appearing

-   Some plugins don't have admin interfaces
-   Only plugins with registered actions will show action buttons
-   System plugins may have limited user-facing options

### Activate/Deactivate Not Working

1.  Ensure you have admin privileges
2.  Check that JavaScript is enabled in your browser
3.  Look for error messages after clicking buttons
4.  Try using the standard Plugins page if issues persist

### Settings Not Saving

1.  Check file permissions on the plugin directory
2.  Ensure PHP can write to `/cmsb/plugins/pluginManager/`
3.  Look for error messages in the CMS

### Drag-and-Drop Not Working

1.  Ensure JavaScript is enabled in your browser
2.  Clear browser cache and refresh the page
3.  Check browser console for JavaScript errors
4.  Verify you have admin privileges (sorting is admin-only)
5.  Try a different browser to rule out compatibility issues

### Sort Order Resetting

1.  Check that you have admin privileges
2.  Verify `/cmsb/plugins/pluginManager/` directory is writable
3.  Look for error messages in browser console when dragging
4.  Ensure `pluginManager_settings.json` file can be written to

## Future Enhancements

The Plugin Manager is designed for expansion. Planned features include:

-   **Custom Widgets**: Add frequently-used tools to the dashboard
-   **Analytics Charts**: Visualize plugin usage and statistics
-   **Quick Actions**: Shortcut buttons for common tasks
-   **Plugin Search**: Filter and search installed plugins
-   **Usage Tracking**: Monitor which plugins you use most
-   **Performance Metrics**: See plugin impact on site performance
-   **Bulk Actions**: Activate/deactivate multiple plugins at once
-   **One-Click Updates**: Direct plugin update installation from the dashboard

## Support

-   **Author**: Sagentic Web Design
-   **Website**: [https://www.sagentic.com](https://www.sagentic.com)
-   **Version**: 1.01
-   **License**: MIT License

## License

MIT License - See [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## Contributing

This plugin follows the CMS Builder Plugin Development Manual standards. When modifying or extending:

-   Follow PSR-12 coding standards
-   Use single-tab indentation
-   Include PHPDoc comments
-   Implement CSRF protection for all state-changing operations
-   Follow accessibility guidelines (ARIA labels, semantic HTML)
-   Test on fresh CMS Builder installations

## Credits

Built following the CMS Builder plugin architecture and best practices. Developed to enhance the plugin management experience for CMS Builder users.
