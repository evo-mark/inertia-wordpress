- **Feature**: New `WebP Express` module
- **Feature**: added `backWithErrors` PHP function
- **Feature**: New `init` method added for modules which executes regardless of enabled status
- **Feature**: `Wordpress` page added to menu to configure useful Wordpress functionality related to Inertia sites

- **Improvement**: Moved Inertia up to top-level menu page
- **Improvement**: Split "Settings" into separate "Settings" and "Modules" pages
- **Improvement**: Moved module settings into a table layout
- **Improvement**: `Hooks` page added to documentation
- **Improvement**: New filters available for ACF module
- **Improvement**: New detection for `vendor/autoload.php` to allow use in roots/bedrock project
- **Improvement**: Return generic form validation errors as `_message` to form helper.
- **Improvement**: Ensure settings belong to the `inertia` group before attempting to load them via REST

- **BugFix**: Fix errant underlines in README
- **BugFix**: ACF status inside admin-module page showing wrong data
- **BugFix**: `Utils::getClass` returning wrong type on empty
- **BugFix**: Remove old template when navigating from a page with template to one without
- **BugFix**: Remove template from partial component header if present
- **BugFix**: Change hook for plugin boot to `plugins_loaded` to ensure settings are available ASAP
