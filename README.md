# Little Treats Bakery

A PHP / MySQL online bakery: customers browse products, order for delivery or pickup, earn loyalty points and redeem rewards; admins manage orders, products, customers, messages and reports.

## Requirements
- XAMPP (Apache, PHP 8.x with `mysqli`, `fileinfo`; `gd` is optional) and MariaDB/MySQL.

## Set up
1. Put the project in `htdocs/little_treats`.
2. Create the database and tables:
   ```
   mysql -u root -e "CREATE DATABASE little_treats_db CHARACTER SET utf8mb4"
   mysql -u root little_treats_db < database/schema.sql
   mysql -u root little_treats_db < database/seed.sql        (optional starter data)
   ```
3. Create a database user that only has the rights the site needs (never run the site as `root`):
   ```
   mysql -u root -e "CREATE USER 'little_treats_app'@'localhost' IDENTIFIED BY 'a-strong-password'; GRANT SELECT, INSERT, UPDATE, DELETE ON little_treats_db.* TO 'little_treats_app'@'localhost';"
   ```
4. Copy `config/db_credentials.sample.php` to `config/db_credentials.php` and enter that user and password.
5. Create the first admin (owner). Generate a password hash, then insert the row:
   ```
   php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
   mysql -u root little_treats_db -e "INSERT INTO admins (name, email, password, role) VALUES ('Owner', 'owner@example.com', '<hash>', 'owner');"
   ```
6. Open `http://localhost/little_treats/` (customers) and `http://localhost/little_treats/admin/login.php` (admin).

Upgrading an existing database made before these changes: run the files in `database/migration_*.sql` in order.

## Settings
Everything configurable is in `config/settings.php`: ordering limits, lead time, loyalty rate, bank-transfer details, paging, login lockout, status lists, and `MAIL_ENABLED`.

## Email
Order, status, cancellation, password-reset and reply emails are written to `logs/mail.log`. Set `MAIL_ENABLED` to `true` in `config/settings.php` once PHP `mail()` (SMTP in `php.ini`) is configured and they will also be sent.

## Folder guide
| Path | What it holds |
|---|---|
| `*.php` (root) | Customer pages |
| `admin/` | Admin pages (owner and staff roles) |
| `includes/` | Shared code: `bootstrap.php` (start-up), `security.php` (sessions, CSRF, DB helpers, login lockout), `helpers.php` (flash, cart, pagination, audit log, email), `header.php` / `footer.php` / `admin_header.php`, upload and validation helpers |
| `config/` | Settings and database credentials |
| `database/` | `schema.sql`, `seed.sql`, migrations |
| `uploads/products/` | Product pictures (script execution blocked) |
| `private_uploads/` | Bank-transfer slips (not web accessible; admins view through `admin/view_slip.php`) |
| `logs/` | `mail.log` (not web accessible) |
| `tests/` | `run_tests.php` |

## Tests
```
php tests/run_tests.php
php tests/run_tests.php http://localhost/little_treats    (also checks the pages over HTTP)
```

## Security notes
- All database queries use prepared statements; all output is escaped with `e()`.
- Every form carries a CSRF token; state-changing actions are POST only.
- Logins are rate limited; sessions use HttpOnly / SameSite cookies; admin sessions time out after 30 minutes.
- Owners can delete products/categories, see the audit log and add admins; staff cannot.