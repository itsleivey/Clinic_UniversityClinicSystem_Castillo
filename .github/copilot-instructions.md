## Quick context

This repository is a procedural PHP web application for a university clinic. The web entry points live under `public/` (login, forms, PDF generation) and there are separate admin/client folders (`UC-Admin/`, `UC-Client/`). DB credentials are kept in `db_cridentials.php` and the database helper is `config/database.php` which exposes `pdo_connect_mysql()`.

## Top-level architecture (what an agent should know)
- Web front-ends: `public/` (primary), `UC-Admin/` (admin UI), `UC-Client/` (client assets). Many scripts expect to be executed with `public/` as the working web folder.
- Database: MySQL. Connection helper: `config/database.php` -> `pdo_connect_mysql()`; credentials in `db_cridentials.php`.
- Vendor libs: `public/vendor/` contains Composer packages (PHPMailer, TCPDF). Front-end libs referenced via `package.json` (Chart.js).
- Sessions: session handling is used widely (see `Session/session_start.php` and `public/index.php` for examples).

## Developer workflows & commands (local dev on Windows/XAMPP)
- Start XAMPP/Apache and point your browser to the app folder. Typical URL when using default XAMPP htdocs:

  http://localhost/LSPU%20LBC%20University_Clinic_System/public/index.php

- Install PHP dependencies (Composer files are in `public/`):

  ```powershell
  cd .\public
  composer install
  ```

- Install frontend deps (optional):

  ```powershell
  npm install
  ```

- Import the DB (SQL dumps: `UCS_backup.sql` or `UC-Admin/backups/*.sql`):

  ```powershell
  mysql -u root -p University_Clinic_System < .\UCS_backup.sql
  # or use phpMyAdmin bundled with XAMPP
  ```

## Project-specific patterns & conventions
- Database helper: use `pdo_connect_mysql()` from `config/database.php` rather than creating new PDO instances; example usage in `public/index.php` and many other files.
- Prepared statements are used with positional parameters: `$pdo->prepare("SELECT * FROM Clients WHERE Email = ?"); $stmt->execute([$email]);` — follow this pattern.
- Passwords: hashed + verified using PHP native functions (`password_verify` in `public/index.php`).
- File includes are often relative (e.g. `require '../config/database.php'`). Do not change include paths without checking call-sites — many files assume the script sits inside `public/` or subfolders.
- PDF generation and mail: `public/vendor/tecnickcom/tcpdf` and `public/vendor/phpmailer` are used; look at `public/generate_pdf.php`, `public/generate_pdf_client.php`, and `public/medicalcertificate_functions.php` for examples.

## Integration & cross-component notes
- Email delivery uses PHPMailer shipped under `public/vendor/phpmailer` — configuration is often inline in scripts; search for `PHPMailer\PHPMailer` to find entry points.
- PDF generation uses TCPDF and is invoked from multiple scripts (`generate_pdf*.php`). These scripts assemble HTML and hand it to TCPDF.
- File uploads land in `uploads/` (and `public/uploads/`); check permissions when testing locally.

## Editing guidance for automated agents
- When changing DB code, keep `pdo_connect_mysql()` usage and preserve prepared-statement style.
- When refactoring includes, update all files that rely on the same relative paths. Search for `require`/`include` patterns before moving files.
- Avoid committing real DB credentials. This repo currently contains `db_cridentials.php` — treat it as a secret that should not be published. If you need to change, note it in the PR and provide migration instructions.

## Files to open first (examples to understand common flows)
- `config/database.php` — DB helper
- `db_cridentials.php` — credentials (sensitive)
- `public/index.php` — login flow and session example
- `public/generate_pdf.php` and `public/generate_pdf_client.php` — PDF generation
- `UC-Admin/restore.php`, `UC-Admin/back_up.php` — backup/restore patterns

If anything is unclear or you want the agent to prioritize additional specifics (naming conventions, refactor rules, or automated test expectations), tell me which areas and I'll iterate.
