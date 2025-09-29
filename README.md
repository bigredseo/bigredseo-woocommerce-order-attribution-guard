# Big Red SEO – WooCommerce Order Attribution Guard

Blocks checkout unless **Woo Order Attribution** contains both **Origin** and **Device**. Prevents spam/invalid orders reaching WooCommerce by gating Store API (Checkout block), PayPal Payments AJAX routes, and classic checkout.

- **Author:** Big Red SEO  
- **GitHub:** https://github.com/bigredseo/bigredseo-woocommerce-order-attribution-guard  
- **License:** GPL-3.0-or-later

## Why this exists
Recent bot activity can hit checkout endpoints directly, bypassing normal flows. This plugin forces valid Woo Order Attribution data to be present before orders can proceed.

## Key features
- Gates:
  - Store API checkout: `/wp-json/wc/store/v1/checkout`
  - PayPal Payments AJAX routes: `ppc-create-order`, `ppc-approve-order`
  - Classic checkout fallback
- Lightweight, no settings required (sane defaults)
- Extensible via filters

## Requirements
- WordPress 6.0+
- WooCommerce 7.0+
- Woo Order Attribution enabled (Woo → Settings → Advanced → Features)

## Installation
1. Upload the folder `bigredseo-woocommerce-order-attribution-guard/` to `/wp-content/plugins/`
2. Activate **Big Red SEO – WooCommerce Order Attribution Guard** in **Plugins**
3. Ensure **Woo Order Attribution** is enabled in Woo settings

## How it works (TL;DR)
On checkout requests, the plugin verifies presence of **Origin** and **Device** in Woo Order Attribution payload/cookies. If missing/invalid, it blocks progression and returns a friendly error.

## Filters
```php
/**
 * Allow custom logic to decide whether checkout should be blocked.
 * @param bool  $block    Default decision from core checks
 * @param array $context  Request context (route, attribution data, user, etc.)
 */
apply_filters('brseo_wag_should_block_checkout', $block, $context);
```

## Roadmap
- Optional logging screen in wp-admin
- Admin notice if Woo Order Attribution is disabled
- Per-gateway toggles

## Support
Issues and feature requests: open a GitHub Issue.

## License
GPL-3.0-or-later — see `license.txt`.
