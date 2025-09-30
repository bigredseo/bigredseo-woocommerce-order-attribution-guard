# Big Red SEO – WooCommerce Order Attribution Guard

Blocks checkout unless **Woo Order Attribution** contains both **Origin** and **Device**. Prevents spam/invalid orders reaching WooCommerce by gating Store API (Checkout block), PayPal Payments AJAX routes, and classic checkout.

- **Author:** Big Red SEO  
- **GitHub:** https://github.com/bigredseo/bigredseo-woocommerce-order-attribution-guard  
- **License:** GPL-3.0-or-later

## Why this exists
Recent bot activity can hit checkout endpoints directly, bypassing normal flows. This plugin forces valid Woo Order Attribution data to be present before orders can proceed.

## Extended info on why this plugin was created
The makers of the plugin "WooCommerce PayPal Payments" faild to issue an update to their plugin to stop the Card Testing Attacks. While other members chimed in to assist others and offer code regarding disable_wc_endpoint and ppc-create-order, no official fix was released and instead, thread comments were all archived by a moderator and the response from the folks at https://wordpress.org/plugins/woocommerce-paypal-payments/ was initially suggested to just disable the plugin until they had a fix, disable guest checkout, or activate 3DS which puts another layer of security by sending purchase codes via mobile etc. None of which are viable solutions. 

## Features
- Blocks checkout if **Origin** or **Device Type** is missing  
- Works with:
  - Store API (`/wc/store/v1/checkout`)  
  - PayPal Payments express flows (`ppc-create-order`, `ppc-approve-order`)  
  - Classic checkout (`woocommerce_checkout_create_order`) 
- Lightweight, no settings required (sane defaults)
- Extensible via filters   
- Adds order notes when an order is blocked  
- Logs **pass/fail attempts** (IP, URI, User Agent) in a browser-accessible log file  
- Admin bypass option for testing 

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

## Support
Issues and feature requests: open a GitHub Issue.

## License
GPL-3.0-or-later