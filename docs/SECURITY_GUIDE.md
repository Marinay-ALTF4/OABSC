Simple Security Guide for OABSC

This guide shows basic steps to keep the system safe and secure.

1. Important Files
Make sure your project has these files:

SECURITY.md – basic information about system security
docs/SECURITY_GUIDE.md – full security guide
.env.example – sample settings (no real passwords included)
.gitignore – hides important files like .env and vendor/

2. Basic Secure Setup
Set your system to safe settings:

Use production mode
CI_ENVIRONMENT = production
Set correct website URL
app.baseURL = 'https://example.com'
Add encryption key for security
Turn on CSRF protection for forms
Set cookies to secure and HTTP only

3. Developer Checklist
Before uploading or updating the system:

Make sure no passwords are inside the code
Check that the code has no errors
Test all features
Check user inputs (avoid wrong data)
Protect output to avoid XSS attacks

4. Simple Auto Checks (CI)
Run these checks before deployment:

Check security issues (composer audit)
Check code errors (PHPStan)
Run system tests (PHPUnit)

5. Security Testing
Use tools like ZAP to scan security problems
Test login pages and restricted pages

6. Protecting Secrets
Never put passwords in code
Use .env file for sensitive data
Keep secrets safe in production

7. Database Safety
Only allow needed database access
Backup database regularly
Encrypt backup files
8. System Protection

Hide errors in live system
display_errors = Off
Protect important folders like app/ and writable/

9. File Upload Safety
Check file type before upload
Allow only safe file extensions
Re-check images before saving

10. If Something Goes Wrong
Find the problem
Stop the attack or issue
Fix the system
Restore backup if needed
Report the problem