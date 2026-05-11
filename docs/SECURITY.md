# Security Policy

This repository contains security-related guidance and controls for the OABSC project (Online Appointment Booking and Clinic System).

Purpose
- Document project security controls and operational guidance.
- Provide an actionable checklist used during development, code review, deployment, and incident response.

Responsible disclosure
If you discover a security vulnerability in this project, please report it privately to the project maintainers by opening an issue and marking it "private" or by emailing security@your-org.example (replace with your real secure contact). Provide:
- A clear description and proof-of-concept (POC).
- Steps to reproduce.
- A suggested fix if possible.

Do not publish the vulnerability publicly before the maintainers have had reasonable time to respond and mitigate.

Project security overview

1) Secrets & configuration
- Use environment variables or an `.env` file that is never committed to the repository. Add `.env` to `.gitignore`.
- Provide a sample `.env.example` without secrets to document required configuration values.
- Store strong `app.encryptionKey` and do not share it.
- Do not store API keys, DB passwords, or other secrets in code or repository history.

2) Dependency management
- Use Composer for PHP dependencies; commit `composer.lock` for reproducible installs.
- Run `composer audit` regularly to identify known vulnerabilities.
- Use Dependabot or Snyk to monitor and open pull requests for dependency upgrades.

3) Secure coding guidelines
- Always escape output in views: use `esc()` and templating helpers.
- Use prepared statements / the Query Builder to prevent SQL injection.
- Validate and sanitize all incoming data with CodeIgniter `Validation` rules.
- Protect POST forms with CSRF (`Config\Filters.php` or enable `CSRF` in base `Config` settings).
- Use Content Security Policy (CSP) and other security headers to mitigate XSS. See `app/Config/ContentSecurityPolicy.php`.

4) Authentication & password storage
- Use `password_hash()` (`PASSWORD_BCRYPT`/`PASSWORD_ARGON2I`) for passwords.
- Enforce strong password rules and rate-limiting for login endpoints.
- Do not implement your own crypto primitives. Use PHP/CodeIgniter libraries and functions.
- Implement account lockout or increasing delays after repeated failed logins.

5) Sessions & cookies
- Use the `database` session driver in production for improved session management.
- Set cookies with `HttpOnly`, `Secure`, and `SameSite` attributes.
- Regenerate session ID after login to prevent session fixation.

6) TLS / Transport
- Always use HTTPS in production and redirect HTTP→HTTPS.
- Terminate TLS at load-balancer or web server and disable weak ciphers.

7) File uploads
- Validate file type and size on server side.
- Store uploads outside the webroot when possible.
- Rename files on upload and do not use user-provided filenames.
- Scan uploads for viruses (ClamAV or cloud scanning services) if needed.

8) Logging & monitoring
- Centralize logs (e.g., file-based + external aggregator like ELK, Datadog).
- Mask or avoid writing sensitive data (passwords, secrets) to logs.
- Monitor login failures, sudden spikes, and unusual activity.

9) Infrastructure & deployment
- Use least privilege for database and service accounts.
- Run periodic backups and test restore procedures.
- Keep OS and packages patched; use automatic security updates where appropriate.
- Harden the database server and restrict access by network rules.

10) CI / Automated security checks
- Add static analysis (PHPStan/ Psalm), linter (PHPCS), and unit tests to CI.
- Run dependency security scans (`composer audit`, Snyk) in CI.
- Add automated OWASP ZAP or other dynamic scans as part of scheduled CI jobs.

11) Incident response
- Prepare an incident response plan with steps for containment, eradication, recovery, and disclosure.
- Keep a current list of emergency responders and contact info.

12) Backups and disaster recovery
- Encrypt backups at rest and in transit.
- Rotate and securely store backup encryption keys.
- Test restore procedures on a regular cadence.

Quick commands

```bash
# Audit composer dependencies
composer audit

# Run PHPStan (when configured)
./vendor/bin/phpstan analyse

# Run unit tests (if present)
phpunit
```

Contact and disclosures
- Replace `security@your-org.example` with your actual security contact.
- If this is an open-source project with no private channel, create a private issue and mark it for maintainers only.

---

This file provides an overview. See `docs/SECURITY_GUIDE.md` for a more detailed checklist and CI examples.
