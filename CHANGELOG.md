# Changelog

## 1.0.0

- Initial public release

## 1.1.0
- Bug fixes and performance improvements
- Update: Bump WordPress version compatibility to 7.0
- Updated text domain from screen-options to advanced-screen-options
- Refactored code
- Update pot file

## 1.2.0
- Security: Enhanced permission checks (`manage_options` capability) and role validation for screen options meta and post types
- Performance: Optimized post queries and uninstallation with batched processing (100 posts per page) and pagination support
- Fix: Filtered screen options query by current post type
- Fix: Prevented infinite loops during uninstallation by tracking failed post deletions
- Fix: Added safety checks for empty asset handles, post loops, and screen options data
- Chore: Updated npm, Composer, and GitHub Actions dependencies
