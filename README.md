# OABSC

Online Appointment Booking and Clinic System built with CodeIgniter 4.

## Overview

OABSC is a role-based clinic management system for appointment booking, patient records, doctor scheduling, secretary workflows, and admin management. It includes web authentication, registration verification, MFA for regular users, role selection for admin accounts, and API endpoints for mobile or external integrations.

## Core Features

- Public registration and login with email verification.
- MFA flow for regular users.
- Role selection flow for admin and assistant admin accounts.
- Client appointment booking and profile management.
- Secretary workspace for appointments, queue handling, records, schedules, and approvals.
- Doctor schedule management and appointment review.
- Admin patient management with add, edit, soft delete, and restore.
- Clinic settings management, including the clinic access code.
- Notification actions for marking items read and deleting notifications.
- API endpoints for health checks, registration, user listing, and login.

## Tech Stack

- PHP 8.1+
- CodeIgniter 4
- MySQL
- Bootstrap-based views
- CodeIgniter Migrations and Seeders

## Main Roles

- Admin: full clinic administration and patient management.
- Assistant Admin: limited admin access with role selection after login.
- Secretary: handles appointments, queue, records, schedules, and approvals.
- Doctor: manages schedule and appointment status.
- Client: books appointments and updates profile information.

## Installation

1. Clone or copy the project into your web server directory.
2. Install dependencies with Composer if needed:

   ```bash
   composer install
   ```

3. Create or update the `.env` file in the project root.
4. Configure your database connection in `.env`.
5. Set your base URL:

   ```dotenv
   app.baseURL = 'http://localhost/OABSC'
   ```

6. Run migrations:

   ```bash
   php spark migrate
   ```

7. Seed initial data:

   ```bash
   php spark db:seed UserSeeder
   php spark db:seed ClinicSettingsSeeder
   ```

## Environment Configuration

Update these values in `.env` for local development:

```dotenv
CI_ENVIRONMENT = development

database.default.hostname = 127.0.0.1
database.default.database = oabsc
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

If you use email verification or login codes, also configure your SMTP settings in `.env`.

## Running the App

Run the built-in development server:

```bash
php spark serve
```

Then open the app in your browser using the configured base URL.

## Authentication Flow

- New users register through `/register`.
- Registration requires email verification.
- Regular users may receive MFA by email when logging in.
- Admin and assistant admin users go through `/role-selection` after login.
- Role selection requires the clinic access code and the role password.

## Seeded Accounts

The seeders create default users for local development.

Common seeded emails:

- `admin@example.com`
- `secretary@example.com`
- `doctor@example.com`
- `client@example.com`
- `assistant.admin@example.com` if no assistant admin exists

Default passwords used by the current seeders:

- Login passwords for seeded system users: `admin123`, `secretary123`, `doctor123`, `client123`
- Admin role password: `Admin123`
- Assistant admin role password: `assistant123`
- Clinic access code: `CLINIC2026`

## Main Routes

### Auth

- `/` and `/login`
- `/register`
- `/logout`
- `/login/verify-mfa`
- `/register/verify`
- `/role-selection`

### Client

- `/dashboard`
- `/profile`
- `/appointments/new`
- `/appointments/my`

### Secretary

- `/secretary/appointments`
- `/secretary/queue`
- `/secretary/records`
- `/secretary/register`
- `/secretary/schedules`
- `/secretary/approvals`

### Doctor

- `/doctor/schedule`
- `/doctor/appointments`

### Admin

- `/admin/patients`
- `/admin/patients/list`
- `/admin/patients/history`
- `/admin/patients/clients`
- `/admin/patients/add`
- `/admin/patients/add-role`
- `/admin/patients/edit/:id`
- `/admin/patients/delete/:id`
- `/admin/patients/restore/:id`
- `/admin/doctors`
- `/admin/doctors/specialization`
- `/admin/doctors/schedule`
- `/admin/settings`

## API Endpoints

- `GET /api/health`
- `POST /api/register`
- `GET /api/users`
- `POST /api/login`
- `GET /api/doctor/:id/schedule`

## Database Notes

The project includes migrations for:

- Users table and soft deletes
- Contact and doctor profile fields
- MFA fields
- Role password and clinic settings
- Secure user storage schema
- Appointment, schedule, access request, and notification tables

Use migrations instead of manual table changes whenever possible.

## Project Structure

- `app/Controllers` - app logic and route handlers
- `app/Models` - database models
- `app/Views` - UI templates
- `app/Database/Migrations` - schema changes
- `app/Database/Seeds` - default and maintenance seed data
- `public` - public assets

## Notes

- Keep `.env` out of version control.
- Do not commit real SMTP credentials or database passwords.
- If you change authentication data in seeders, rerun the relevant seeder.
