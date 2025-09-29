# WooCommerce Order Attribution Guard

A lightweight security plugin for WooCommerce that ensures every order includes **Order Attribution** details (Origin & Device).  

This helps prevent fake or automated orders placed through direct API calls or checkout bypasses.

---

## Features
- ✅ Blocks checkout if **Origin** or **Device Type** is missing  
- ✅ Works with:
  - Store API (`/wc/store/v1/checkout`)  
  - PayPal Payments express flows (`ppc-create-order`, `ppc-approve-order`)  
  - Classic checkout (`woocommerce_checkout_create_order`)  
- ✅ Adds order notes when an order is blocked  
- ✅ Logs **pass/fail attempts** (IP, URI, User Agent) in a browser-accessible log file  
- ✅ Admin bypass option for testing  

---

## Installation
1. Download or clone this repository into your WordPress plugins directory:

   ```bash
   git clone https://github.com/YOURUSERNAME/woocommerce-order-attribution-guard.git wp-content/plugins/woocommerce-order-attribution-guard
   ```

2. Activate **WooCommerce Order Attribution Guard** in **WordPress Admin → Plugins**.

3. Make sure **WooCommerce → Settings → Advanced → Order attribution** is enabled.

---

## Logging
- Logs are stored in:

  ```
  wp-content/uploads/brs-logs/attrib-check.log
  ```

- Accessible in a browser:

  ```
  https://yourdomain.com/wp-content/uploads/brs-logs/attrib-check.log
  ```

- Each line shows:
  ```
  [2025-09-29T12:34:56+00:00] store_api_checkout ok=0 ip=41.114.57.77 uri=/wc/store/v1/checkout | Mozilla/5.0 ...
  ```

Where:
- `ok=1` → attribution passed (order allowed)  
- `ok=0` → attribution failed (order blocked)  

---

## Configuration
- **Admins** can bypass the check for testing (toggle in code).  
- Log folder defaults to `/wp-content/uploads/brs-logs/`.  
- To secure logs, restrict access via `.htaccess` or move the path.  

---

## Contributing
Pull requests are welcome! Please fork the repo and submit improvements.

---

## 📄 License
This plugin is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

---

## Credits
Developed by [Big Red SEO](https://www.bigredseo.com).  
