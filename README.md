# EasyOrder

A lightweight WordPress plugin for displaying a product catalog and receiving email order requests.

Built with the help of Claude.

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
[easyorder_form]
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

- **Order Email Recipients** — Optional, comma-separated list of email addresses to receive order notifications.
- **Confirmation Email** — Toggle whether a confirmation email is sent to the user after submission.

---

## Emails

### Order Notification
Includes product name, SKU, type, price, quantity, line totals, estimated order total, and any notes left by the user.

### Confirmation Email
Sent to the logged-in user who submitted the order. Includes product name, SKU, type, and quantity — no pricing is included.

---

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
