# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within this project, please report it via email or issue tracker. We will address the issue as soon as possible.

## Security Measures Implemented

MicroBoard prioritizes security and implements the following measures to protect user data and system integrity.

### 1. Authentication & Authorization
- **Session Management**: Secure session handling with timeouts (30 minutes) to prevent session hijacking.
- **Access Control**: Strict role-based access control (RBAC). Admin pages (`admin/`) are protected by `requireAdmin()` checks.
- **Password Hashing**: User passwords are hashed using `password_hash()` (bcrypt) before storage.

### 2. Input Validation & Sanitization (XSS Prevention)
- **Output Escaping**: All user-generated content is escaped using `htmlspecialchars()` before rendering to prevent Cross-Site Scripting (XSS).
- **HTML Tag Stripping**: `strip_tags()` is used to remove potentially dangerous HTML tags from post content while allowing safe formatting.
- **Type Casting**: ID parameters are cast to integers or validated using `filter_var()` to prevent type juggling attacks.

### 3. Database Security (SQL Injection Prevention)
- **Prepared Statements**: All database queries use PDO prepared statements to completely neutralize SQL Injection attacks.
- **No Direct Query Construction**: Dynamic SQL construction is avoided where possible, or handled with strict whitelisting.

### 4. Cross-Site Request Forgery (CSRF) Protection
- **Token Validation**: Anti-CSRF tokens are generated per session and validated on all state-changing requests (POST, DELETE).
- **Strict Implementation**: CSRF validation is strictly enforced on critical actions including Login, Registration, Post Creation/Edit/Delete, Comment Management, and Admin Settings.
- **Session Bind**: Tokens are cryptographically bound to the user session.

### 5. File Upload Security
- **Authentication Check**: Only authorized users can upload files.
- **Strict Extension Whitelisting**: Allows only safe file extensions (Images: `jpg`, `png`, `gif`; Docs: `pdf`, `txt`, `doc`, `xls`, `ppt`, `hwp`, `zip`). Direct execution files like `.php`, `.html`, `.exe` are strictly blocked.
- **MIME Type Validation**: Verifies the actual file content type using `finfo_file` to prevent extension spoofing.
- **Content Inspection**: Scans file contents for malicious PHP tags (`<?php`, `<?`, `<%`) to prevent Web Shell uploads.
- **Randomized Filenames**: Uploaded files are automatically renamed with timestamps and random hashes to prevent overwriting and direct file access attacks.
- **Execution Prevention**: Recommended server configuration to disable script execution in the upload directory.

### 6. Permission & Access Control
- **Strict Role Checking**: Granular permission checks for board actions (Read/Write/Comment/Download).
- **Ownership Validation**: Strict ownership verification for editing/deleting posts and comments. Non-admin users can never modify others' content.
- **Admin Isolation**: Admin panel (`/admin`) requires explicit administrator privileges (level 10).

### 6. Installation Security
- **Database Connection**: Supports both shared hosting (direct DB connection) and VPS (DB creation) environments securely.
- **Locking**: It is recommended to remove `install.php` after installation to prevent re-installation attacks.

## Security Best Practices for Operators

1.  **HTTPS**: Always serve the application over HTTPS.
2.  **Server Config**: Disable directory listing and ensure `allow_url_fopen` is off if not needed.
3.  **Updates**: Keep PHP and MySQL versions updated.
4.  **File Permissions**: Ensure `config.php` and `data/` directories have appropriate permissions (e.g., `644` for files, `755` for dirs).

## License

This project is open-source software licensed under the MIT license.
