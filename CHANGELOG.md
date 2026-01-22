# Changelog

## Version 1.01 (2026-01-22)

### Bug Fixes

**CSRF Token Compatibility:**
-   Fixed drag-and-drop sort order persistence issue where changes weren't being saved after page refresh
-   Corrected CSRF token field name in AJAX requests from `csrfToken` to `_csrf` to match CMSB security requirements
-   Added backward compatibility support for both CMSB 3.81 (`_CSRFToken`) and 3.82+ (`_csrf`) session variable names
-   Kanban-style sorting now properly saves and persists across page refreshes on all supported CMSB versions

### Technical Changes
-   Updated JavaScript AJAX handlers to use correct CSRF field name (`_csrf`)
-   Modified CSRF token retrieval to check both `$_SESSION['_csrf']` (3.82+) and `$_SESSION['_CSRFToken']` (3.81) for cross-version compatibility
-   Improved error handling for sort order save operations

## Version 1.00 (2026-01-20)

### Initial Release

**Core Features:**
-   Enhanced plugin management dashboard accessible from Plugins page
-   Display all installed plugins in responsive 4-column grid layout (4 desktop / 2 tablet / 1 mobile)
-   Plugin activation and deactivation directly from dashboard with confirmation dialogs
-   Direct access links to all plugin admin pages and settings
-   Real-time plugin statistics overview (total, active, inactive, system plugins)

**Plugin Cards:**
-   Visual plugin cards with status badges (Active/Inactive/System)
-   Display plugin name, version, author, and description
-   Action buttons for each plugin's admin pages
-   Update notification badges when newer versions available
-   Version comparison display (current vs. available)

**Customizable Display Options:**
-   Show/hide inactive plugins
-   Show/hide system plugins
-   Group plugins by active/inactive status
-   All settings configurable via Settings page

**Kanban-Style Drag-and-Drop:**
-   Drag and drop plugin cards to reorder them
-   Custom sort order saves automatically via AJAX
-   Persistent arrangement across sessions
-   Admin-only sorting capability

**Version Checking & Updates:**
-   Automatic update detection from Interactive Tools RSS feed
-   Integration with Sagentic Web Design plugin library RSS feed
-   Update badges on plugin cards when newer versions available
-   24-hour update check caching
-   Configurable update checking (enable/disable via Settings)

**Technical Implementation:**
-   File-based JSON storage (no database required)
-   Settings stored in `pluginManager_settings.json`
-   Update cache stored in `pluginManager_updates_cache.json`
-   Portable design works on any CMS Builder 3.50+ installation
-   Requires PHP 7.1+ (for nullable type hints)
-   External CDN: SortableJS v1.15.0 for drag-and-drop
-   CSRF protection for state-changing operations
-   Admin-only access controls throughout
-   CLI execution protection

**User Interface:**
-   Responsive Bootstrap-based layout
-   Accessibility support with ARIA labels and semantic HTML
-   Icon-based visual indicators (Font Awesome Duotone)
-   Color-coded status badges
-   Help tooltips and descriptions
-   Three-tab navigation: Dashboard, Settings, Help

**Documentation:**
-   Comprehensive README with installation instructions
-   Detailed help page within plugin interface
-   Feature descriptions and usage guidelines
-   Troubleshooting section
-   Settings reference documentation

**Settings Management:**
-   JSON-based portable settings
-   Auto-creation of settings file on first run
-   Sensible defaults work out-of-the-box
-   No manual configuration required

**Known Limitations:**
-   Drag-and-drop requires JavaScript and external CDN access
-   Update checking requires allow_url_fopen enabled
-   Features degrade gracefully if requirements not met