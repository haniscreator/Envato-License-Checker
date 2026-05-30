# Envato API License Checker

A single-domain license verification and management server built with Laravel, MySQL, and Tailwind CSS v4. Designed to verify CodeCanyon/Envato purchase codes for your client products and control domain bindings through an administrative panel.

---

## ─── System Architecture ───

```
[ Client Website (e.g. Healthy-AI Monolith) ]
                   │
                   ▼  (POST /api/licenses/verify)
      [ License Checker API Server ]
                   │
         ┌─────────┴─────────┐
         ▼                   ▼
  [ Local MySQL DB ]   [ Envato API ]
(Cache & Status Rules) (Purchase Validation)
```

---

## ─── Core Features ───

1. **Envato API Verification Layer**
   - Automatically verifies purchase codes against the official Envato Market API.
   - Restricts verification to specific CodeCanyon Item IDs (configurable in settings).
   - Automatically saves purchaser information, item name, purchase date, and license type (Regular vs. Extended) upon first activation.

2. **Localhost & Sandbox Bypass**
   - Skips domain registration and lock bindings if requests come from `localhost`, `127.0.0.1`, `.local` domains, or sandbox environments. 
   - Clients can safely install, develop, and test your product locally without using up their single production domain binding.

3. **Self-Service Automatic Domain Transfer**
   - Enables clients to transfer their license to a new domain automatically by providing the `old_domain` parameter in the API payload.
   - Eliminates manual administrator intervention for standard domain migrations.

4. **Dynamic Branding & Theme Color**
   - Dynamic dashboard primary color configurable via the Admin Settings page (supports any hex color picker, e.g. `#f67e39`).
   - Automatically generates a dynamic theme-colored SVG favicon matching the selected brand color.
   - No hardcoded colors—theme classes utilize Tailwind CSS v4 variables mapped directly from the database.

5. **Premium Admin Dashboard**
   - High-performance UI built with **Tailwind CSS v4** and compiled with **Vite**.
   - Features real-time statistics (Active/Revoked counts, past 24h API traffic volumes).
   - Includes a dynamic 7-day API activity bar chart.
   - Paginates and filters search results for all licenses and detailed API verification logs.

6. **Password-Secured Admin Actions**
   - Important actions (Revoking / Activating a license binding, Deleting records) require the administrator's password verification in a confirmation modal to prevent accidental or malicious clicks.

7. **SQLite Isolated Testing Suite**
   - Includes 16 integration tests testing all validation states, localhost bypasses, domain transfers, and secure admin routes.
   - Runs on an in-memory SQLite database (`:memory:`) to ensure local MySQL data is never altered or deleted during tests.

---

## ─── API Reference ───

### Verify License
Verify a client's purchase code and bind it to their domain.

* **Endpoint**: `/api/licenses/verify`
* **Method**: `POST`
* **Content-Type**: `application/json`

#### Request Payload
```json
{
  "purchase_code": "CLIENT-PURCHASE-CODE-HERE",
  "domain": "clientwebsite.com",
  "old_domain": "previous-bound-domain.com"
}
```
* `purchase_code` (Required | String): The Envato purchase license key.
* `domain` (Required | String): The host domain where the product is running (automatically stripped of protocols and subdomains).
* `old_domain` (Optional | String): Required only when migrating the license binding to a new domain.

#### Response Formats

##### A. Successful New Activation
```json
{
  "status": true,
  "message": "License registered and verified successfully.",
  "buyer": "buyer_username",
  "item_id": "12345678",
  "item_name": "Healthy-AI - Premium AI Monolith",
  "license_type": "Regular License",
  "purchase_date": "2026-03-30T01:00:00Z",
  "registered_domain": "clientwebsite.com"
}
```

##### B. Successful Localhost Bypass
```json
{
  "status": true,
  "message": "Verified on localhost. License not registered yet.",
  "buyer": "buyer_username",
  "item_id": "12345678",
  "registered_domain": null
}
```

##### C. Error: Already Bound to Another Domain
```json
{
  "status": false,
  "message": "This purchase code is already registered to another domain. To transfer, please specify your old domain name.",
  "registered_domain": "myclientwebsite.com"
}
```

##### D. Error: License Revoked by Admin
```json
{
  "status": false,
  "message": "This license has been suspended/revoked by the administrator.",
  "registered_domain": "clientwebsite.com"
}
```

---

## ─── Getting Started ───

### Prerequisites
* PHP 8.2 or higher
* MySQL 8.0+
* Composer
* Node.js & NPM

### Setup & Installation

1. **Clone the repository and install PHP dependencies**:
   ```bash
   composer install
   ```

2. **Install and build frontend dependencies**:
   ```bash
   npm install
   npm run build
   ```

3. **Configure Environment File**:
   Copy `.env.example` to `.env` and configure your database settings and default credentials:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Make sure to configure the database variables:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=8889 # Check MAMP/Local port
   DB_DATABASE=license_checker
   DB_USERNAME=root
   DB_PASSWORD=root
   ```

4. **Run Migrations & Seed Default Data**:
   ```bash
   php artisan migrate --seed
   ```

5. **Start Development Servers**:
   If your environment utilizes Laravel Herd, ensure you clear path conflicts to use your system PHP version:
   ```bash
   # Build & Watch Assets
   npm run dev
   
   # Run Dev Server
   unset HERD_PHP_84_INI_SCAN_DIR && export PATH="/opt/homebrew/bin:$PATH" && php artisan serve
   ```

   The Admin Panel will be accessible at: `http://127.0.0.1:8000/admin`
   * **Default Login**: `admin@mydomain.com`
   * **Default Password**: `Admin123!`
   *(Update your password immediately in Settings)*

---

## ─── Running Tests ───

Run PHPUnit tests using the isolated SQLite database environment:
```bash
unset HERD_PHP_84_INI_SCAN_DIR && export PATH="/opt/homebrew/bin:$PATH" && ./vendor/bin/phpunit
```
