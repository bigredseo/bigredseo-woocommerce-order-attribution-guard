# Changelog

## [1.0.3] – 2025-09-29
### Fixed 
- Store API checkout flow no longer leaves behind silent **Draft** orders when attribution is missing.
- Added proper **order notes** and auto-**Cancelled** status when a draft order does slip through.

### Added
- New **pre-dispatch gate** on `rest_request_before_callbacks` to block Checkout Block requests before WooCommerce creates a draft order.

### Changed
- Updated `gate_store_api()` to act as a **safety net**: annotate and cancel draft orders instead of leaving them as-is.
- Refactored Store API detection with `is_store_checkout_route()` helper and a polyfill for `str_starts_with()` for PHP 7.x compatibility.


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
