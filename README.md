# Inventory & Invoice Management System

A small business inventory and invoicing app built with Laravel, Inertia and Vue 3. This was built as an internship
assessment project - the goal was to build something that manages products, customers and invoices, with different
access levels for different types of users.

## What it does

- Admin, Staff, Accountant and Customer accounts, each with different access
- Product management with stock levels and image upload
- Customer management
- Creating invoices (stock is automatically reduced when an invoice is created)
- Invoice statuses: draft, sent, paid, overdue
- Downloadable invoice PDFs
- Emailing invoices to customers (sent through a queue, not immediately)
- A reports page with basic sales numbers
- Search, filtering and pagination on the main tables

## Stack

- Laravel 13 (backend)
- Vue 3 + Inertia.js (frontend, no separate API)
- Tailwind CSS (styling)
- Laravel Breeze (login/registration)
- SQLite for local development
- Pest for tests

## Running it locally

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm run build
php artisan serve
```

Then visit `http://localhost:8000`.

`php artisan migrate --seed` also creates a demo login for every role (see "Demo accounts" below) plus a few
sample products, customers and invoices, so the app has something to look at right away.

### Running the queue (for invoice emails)

Invoice emails are queued instead of sent right away, so a queue worker needs to be running for them to
actually go out:

```bash
php artisan queue:work
```

Without the worker running, queued emails just sit in the `jobs` table until it's started.

### Demo accounts

All demo accounts use the password `password`.

| Role       | Email                 |
|------------|-----------------------|
| Admin      | admin@demo.test       |
| Staff      | staff@demo.test       |
| Accountant | accountant@demo.test  |
| Customer   | customer@demo.test    |

## Tests

```bash
php artisan test
```
