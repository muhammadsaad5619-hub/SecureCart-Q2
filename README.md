# SecureCart — Web Security Project (Part 2)

A PHP + MySQL e-commerce application demonstrating common web vulnerabilities
and their fixes. It ships in two versions with identical functionality:

- **vulnerable/** — deliberately insecure code
- **safe/** — the same app with every vulnerability remediated

Stack: Apache + PHP 7.4 + MySQL 8, on Ubuntu 20.04 (SEED VM).

---

## 1. Environment Setup

Install the LAMP stack:

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql php-mysqli unzip
```

Start the services:

```bash
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

Set a MySQL root password (used by the app's config.php):

```bash
sudo mysql
```
```sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Root@12345';
FLUSH PRIVILEGES;
EXIT;
```

---

## 2. Database Setup

Load the schema and sample data:

```bash
mysql -u root -p < setup.sql
```

Verify:

```bash
mysql -u root -p -e "USE securecart; SELECT * FROM users; SELECT * FROM products;"
```

Expected: 3 users (admin, alice, bob) and 4 products.

Seeded accounts:

| Username | Password | Role  |
|----------|----------|-------|
| admin    | admin123 | admin |
| alice    | alice123 | user  |
| bob      | bobpass  | user  |

---

## 3. Deploy the Application

```bash
sudo mkdir -p /var/www/html/securecart/vulnerable
sudo mkdir -p /var/www/html/securecart/safe
sudo cp vulnerable/* /var/www/html/securecart/vulnerable/
sudo cp safe/*      /var/www/html/securecart/safe/
sudo chown -R www-data:www-data /var/www/html/securecart
```


## 4. Vulnerability Demonstrations (Vulnerable version)

### 4.1 SQL Injection — Easy (authentication bypass)

- **Page:** login.php
- **Payload:** Username `admin'#`  Password `x`
- **Result:** Logs in as admin with no valid password.
- **Why:** Input is concatenated into
  `SELECT * FROM users WHERE username='$username' AND password='$password'`.
  `admin'#` closes the string and comments out the password check.

### 4.2 SQL Injection — Intermediate (blind boolean-based)

- **Page:** search.php
- **Baseline:** `zzzznothing` -> no results
- **TRUE:**  `zzzznothing' OR '1'='1'#` -> all products returned
- **FALSE:** `zzzznothing' OR '1'='2'#` -> no products
- **Why:** The term goes into `LIKE '%$q%'`. The attacker infers the
  condition's truth value from whether results appear — no data is shown
  directly. The trailing `#` comments out the query's closing `%'`.

### 4.3 SQL Injection — Advanced (UNION-based extraction)

- **Page:** search.php
- **Stage 1 — column count:**
  `zzzznothing' ORDER BY 4#` (ok) then `ORDER BY 5#` (error) -> 4 columns
- **Stage 2 — UNION probe:**
  `zzzznothing' UNION SELECT NULL,NULL,NULL,NULL#`
- **Stage 3 — table names:**
  `zzzznothing' UNION SELECT NULL,table_name,NULL,NULL FROM information_schema.tables WHERE table_schema=database()#`
- **Stage 4 — column names:**
  `zzzznothing' UNION SELECT NULL,column_name,NULL,NULL FROM information_schema.columns WHERE table_name='users'#`
- **Stage 5 — data dump:**
  `zzzznothing' UNION SELECT NULL,CONCAT(username,':',password),NULL,NULL FROM users#`
- **Result:** Full credential dump (admin:admin123, alice:alice123, bob:bobpass).

### 4.4 Reflected XSS

- **Page:** search.php
- **Payload:** `<script>alert('XSS')</script>`
- **Why:** The search term is echoed into the page unescaped and executed.

### 4.5 Stored XSS

- **Page:** product.php?id=1 (review box)
- **Payload:** `<script>alert('Stored XSS')</script>`
- **Why:** The comment is stored and rendered unescaped for every visitor.

### 4.6 DOM-based XSS

- **URL:** `product.php?id=1#<img src=x onerror=alert('DOM-XSS')>`
- **Why:** Client-side JS reads `location.hash` into `innerHTML`. The payload
  never reaches the server — the flaw is purely in the browser.

### 4.7 CSRF

- **Pages:** profile.php + csrf_attack.html
- **Steps:** Log in as alice, stay logged in, open csrf_attack.html,
  reload profile -> email silently changed to attacker@evil.com.
- **Why:** The email-change form has no anti-CSRF token; the browser sends
  alice's session cookie automatically on the cross-site request.

---

## 5. The Fixes (Safe version)

| Vulnerability     | Fix                                                        |
|-------------------|------------------------------------------------------------|
| SQL Injection     | Prepared statements with bound parameters (mysqli_prepare) |
| Reflected XSS     | htmlspecialchars() on all output                           |
| Stored XSS        | htmlspecialchars() on review output                        |
| DOM-based XSS     | textContent instead of innerHTML                           |
| CSRF              | Random per-session token, verified server-side             |

Re-run every payload from Section 4 against the **safe/** pages. Each one is
now inert: SQL payloads are treated as literal search text (no error, no
data), XSS payloads render as visible text (no popup), and the CSRF attempt
is rejected with "Request blocked: invalid or missing CSRF token."

Core principle: **separate code from data** — prepared statements keep user
input out of SQL syntax, output encoding keeps it out of HTML/JS, and CSRF
tokens prove a request came from the application's own form.

---

## 6. Project Structure

```
securecart/
├── setup.sql
├── README.md
├── vulnerable/
│   ├── config.php      login.php     index.php
│   ├── search.php      product.php   profile.php
│   ├── logout.php      csrf_attack.html   style.css
└── safe/
    ├── config.php      login.php     index.php
    ├── search.php      product.php   profile.php
    ├── logout.php      csrf_attack.html   style.css
```

---

## 7. Security Notice

This application is intentionally vulnerable and is for isolated lab/education
use only. Do not deploy it on a public or production network.
