# Changelog

## [1.0.2] – 2025-09-29
### Fixed
- Folder structure now uses all lowercase for consistency

### Changed
- Renamed plugin and folder to **Big Red SEO – WooCommerce Order Attribution Guard** with slug `bigredseo-woocommerce-order-attribution-guard`.
- Reorganized code under **includes/** (WordPress-style) instead of **src/**.
- Updated readme with GitHub-only distribution details.

## [1.0.0] – 2025-09-29
### Added
- Initial release: blocks checkout when Woo Order Attribution is missing **Origin** or **Device**.
- Coverage for Store API checkout, PayPal Payments AJAX routes, and classic fallback.
- Filter `brseo_wag_should_block_checkout` for custom allow/deny logic.
