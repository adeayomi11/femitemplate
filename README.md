# FCWEM Student Support Portal

A PHP and MySQL complaint portal for the Federal College of Wildlife Management and Eco-tourism. It reuses the compiled Bootstrap UI assets already in `dist/` and runs locally under XAMPP.

## Run with XAMPP

1. Copy or keep this folder under `C:\xampp\htdocs\femitemplate`.
2. In the XAMPP Control Panel, start **Apache** and **MySQL**.
3. Open `http://localhost/femitemplate/` in your browser.
4. On first visit, the portal creates the `fcwem_complaints` database and its tables, then asks you to create the first administrator account.
5. Sign in as the administrator and open **Department accounts** to create staff logins. Students can register from the sign-in page.

The default XAMPP connection uses MySQL host `127.0.0.1`, user `root`, and a blank password. Configure non-default credentials before starting Apache by setting `FCWEM_DB_HOST`, `FCWEM_DB_NAME`, `FCWEM_DB_USER`, and `FCWEM_DB_PASSWORD` in the PHP environment. The database account must be allowed to create the database and tables.

## Accounts and ticket workflow

- **Students** register with their full name, email, matric number, department, and password. They can create a complaint, receive a unique reference such as `FCW-2026-A1B2C3D4E5F60718293A4B5C`, and view its status and updates.
- **Administrators** review all complaints, route cases to departments, and create department staff accounts.
- **Department staff** see only tickets assigned to their own department and can change case status and leave an update visible to the student.
- Anyone with a ticket reference can use **Track a ticket** to view the ticket status and history without signing in.

Starter departments and topic categories are seeded automatically. Administrators can rename the sample departments and add the college's departments from **Department accounts**. Passwords are stored as PHP password hashes, database operations use PDO prepared statements, and form submissions use CSRF tokens.

## Requirements

- PHP 7.4+ with `pdo_mysql`
- MySQL or MariaDB (XAMPP defaults are supported)
- Apache with PHP enabled

The dashboard styling and font/icon assets are served from `dist/assets/`; no Node build step is required to run the portal.
