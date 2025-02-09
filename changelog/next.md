- **Feature**: New `WebP Express` module
- **Feature**: added `backWithErrors` PHP function
- **Feature**: New `init` method added for modules which executes regardless of enabled status

- **Improvement**: Moved Inertia up to top-level menu page
- **Improvement**: Split "Settings" into separate "Settings" and "Modules" pages
- **Improvement**: Moved module settings into a table layout
- **Improvement**: `Hooks` page added to documentation
- **Improvement**: New filters available for ACF module
- **Improvement**: New detection for `vendor/autoload.php` to allow use in roots/bedrock project
- **Improvement**: Return generic form validation errors as `_message` to form helper.

- **BugFix**: Fix errant underlines in README
- **BugFix**: ACF status inside admin-module page showing wrong data
- **BugFix**: `Utils::getClass` returning wrong type on empty
- **BugFix**: Remove old template when navigating from a page with template to one without
- **BugFix**: Remove template from partial component header if present
