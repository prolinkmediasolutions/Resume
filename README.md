# Prosper Adams — Resume, Portfolio & Dashboard

A complete purple, powder-blue, and red portfolio for Prosper Adams: Website Developer, Graphic Designer and IT Consultant. Includes seven project stories, category filtering, accessible project dialogs, mobile navigation, telephone contact, a printable resume, and a PHP content dashboard.

## cPanel installation

1. Back up any existing website. In cPanel, choose **MultiPHP Manager** or **Select PHP Version** and use PHP **8.1 or newer** (prefer a currently supported version). Standard PHP sessions, JSON, and password hashing are required; no database or Composer installation is needed.
2. Upload the ZIP to the domain's document root, usually `public_html`, and extract it. `index.html`, `api.php`, and the `admin` directory must sit directly in that root. Keep `.htaccess` too; enable **Show Hidden Files** in File Manager to see it. If an old `index.php` is present, back it up outside the document root so it does not take precedence.
3. Enable your hosting SSL certificate and **Force HTTPS Redirect** in cPanel before signing in. Do not enter your dashboard password over plain HTTP.
4. Open `https://your-domain.example/admin/`. The first request creates private storage **outside** the public document root. With a standard `/home/ACCOUNT/public_html` installation, find `/home/ACCOUNT/.prosper-private-XXXXXXXXXXXX/setup-code.txt` in File Manager. Enable hidden files; the suffix is unique to the installation.
5. Open that file privately, copy the one-time code into the setup form, choose a username (suggested: `prosper`) and your own password of at least **12 characters**. No default password is included. The code file is deleted after setup. Keep your password in a password manager.
6. Sign in, edit your profile or project stories, and choose **Save website content**. Refresh the public website and printable resume to see changes. Sign out when finished.

## Features & editing

- Dashboard fields: full name, professional title, professional summary, phone, address, and the title, service type, description, challenge, and approach of all seven projects.
- These updates are stored on your server and appear on the public website and printable resume. Structural page text, service descriptions, artwork, category assignment, and logo initials are edited in the HTML/CSS/JavaScript files.
- `resume.html` has **Print / Save as PDF**. Select Save as PDF in the browser's print dialog.
- The public phone link opens a calling application. No email was supplied, so no invented email address or nonfunctional contact form is included.
- Project descriptions use the experience you supplied; no employment dates, numerical results, qualifications, or client endorsements have been invented.
- All artwork and fonts work without third-party downloads. There is no analytics tracking.

## Security & storage

Passwords are hashed using PHP's password API. Login uses server-side sessions, HttpOnly / SameSite cookies, a one-hour session limit, login throttling, and CSRF tokens for authenticated changes. Secure cookies are used when PHP detects HTTPS. Use your host's HTTPS configuration and ask support to ensure PHP sees HTTPS correctly if TLS is terminated at a proxy.

Private account details, login attempt records, and edited content live in the `.prosper-private-…` directory outside the document root. **Do not move that directory into public_html or publish a backup of it.** The public `content.json` contains only the initial public profile, not credentials. The API deliberately returns your public profile to visitors. There are no upload endpoints or browser-stored passwords.

PHP needs permission to create this private directory in the account's home folder. If the API reports a storage error, check the PHP error log and ask your host to permit this directory outside the document root. Alternatively configure the `PROSPER_PRIVATE_DIR` environment variable as an existing absolute, writable path outside the document root. Do not use world-writable permissions or put private data under public_html.

Back up the website files **and** the private directory securely before moving the site. The private directory's default name depends on the installation path; when relocating, use `PROSPER_PRIVATE_DIR` or move private data into the new generated location. If you forget your password, use your authenticated cPanel File Manager to back up then remove `credentials.json` from the private directory; reload `/admin/` and use the newly generated setup code to create a replacement account. This preserves saved public content. Repeated failed sign-ins pause login for 15 minutes.

The ZIP does not contain a pre-created account, setup code, runtime state, or secret credentials. Hosting-specific SSL, filesystem permissions, and PHP settings must be checked after upload. This implementation has not undergone an independent security audit.

## Local development

Use the existing checkout; the cloud task is already isolated and does not need a Git worktree. With PHP 8.1+ available:

```sh
cd /workspace/Resume
php -S 127.0.0.1:8001 -t .
```

For local development you may set `PROSPER_PRIVATE_DIR` to a writable directory outside the server document root. The built-in server is for development only. Use the cPanel web server in production. Static HTML can be opened on its own, but dashboard login and persistent edits require PHP.

## Files

- `index.html`, `styles.css`, `script.js`: public portfolio
- `resume.html`, `resume.js`: printable resume
- `admin/index.html`, `admin.js`: dashboard interface
- `api.php`: authentication, sessions, profile storage
- `content.json`: initial public profile and seven projects
- `.htaccess`: Apache defaults and response headers
- `assets/favicon.svg`: PA brand icon
