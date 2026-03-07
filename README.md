# EasyOrder

A lightweight WordPress plugin for displaying a product catalog and receiving email order requests.

Built by [Dynamic Technologies](https://bedynamic.tech) with the help of Claude.

---
<img width="750" alt="image" src="https://github.com/user-attachments/assets/c3b8497e-b1cd-4c89-8444-7d5816b85d01" />

## Features

- Automatic 'out of stock' display
- Custom product post type with SKU, price, stock, type, and category fields
- 'Submit' button follows website theme
- Filterable, sortable product table via a simple shortcode
- Email notification sent to configurable recipients on submission
- Optional confirmation email sent to the user
- Two-click submit confirmation to prevent accidental orders
- Smart column visibility — SKU, Type, and Category columns hide automatically when no data is present
- Mobile-responsive table layout
- Settings page under **Settings → EasyOrder**

---

## Requirements

- WordPress 6.0+
- PHP 7.4+
- A working outbound mail configuration (SMTP plugin recommended)

---

## Installation

1. Download or clone this repository
2. Install the plugin via the easyorder.zip file
3. Activate the plugin from **Plugins** in the WordPress admin
4. Configure recipients and email options under **Settings → EasyOrder**

---

## Usage

Place the shortcode on any page or post:

```
[product_order_form]
```

Users must be logged in to view and submit the form. If a visitor is not logged in, a message is shown in place of the form.

---

## Managing Products

Products are managed under the **Products** menu in the WordPress admin.

Each product supports:

| Field | Description |
|---|---|
| Title | Product name |
| SKU | Optional stock keeping unit identifier |
| Price | Optional price (displayed in the form and order email) |
| Stock | Available quantity |
| Type | Optional single taxonomy term (managed under Products → Types) |
| Category | Optional single taxonomy term (managed under Products → Categories) |

---

## Settings

Navigate to **Settings → EasyOrder** to configure:

- **Order Email Recipients** — Comma-separated list of email addresses to receive order notifications. Leave blank to use the site admin email.
- **Confirmation Email** — Toggle whether a confirmation email is sent to the user after submission.

---

## Column Visibility

The product table automatically hides columns with no data:

- **SKU** — hidden if no products have a SKU entered
- **Type** — hidden if no Types exist under Products → Types
- **Category** — hidden if no Categories exist under Products → Categories

---

## Emails

### Order Notification
Sent to the configured recipients. Includes product name, SKU, type, price, quantity, line totals, estimated order total, and any notes left by the user.

### Confirmation Email
Sent to the logged-in user who submitted the order. Includes product name, SKU, type, and quantity — no pricing is included. Can be disabled in settings.

---

## Shortcode Reference

| Attribute | Default | Description |
|---|---|---|
| *(none)* | — | Recipients and settings are managed via the admin settings page |

---

## Changelog

### 0.2.5
- Type and Category fields in the product editor changed to dropdowns

### 0.2.4
- SKU, Type, and Category columns now auto-hide when no data is present
- Sort icons enlarged for better readability
- Column visibility notice added to settings page

### 0.2.3
- Added fallback to ensure recipient list is never empty

### 0.2.2
- Reverted admin email to horizontal table layout

### 0.2.1
- Admin email redesigned with stacked card layout per item

### 0.2.0
- Recipient email addresses moved to Settings → EasyOrder
- Confirmation email toggle added to settings
- Plugin file renamed to `easyorder.php`

### 0.1.0
- Initial release

---

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
