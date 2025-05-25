# Budget Tracker – Evozon PHP Internship Hackathon 2025

## Starting from the skeleton

Prerequisites:

- PHP >= 8.1 with the usual extension installed, including PDO.
- [Composer](https://getcomposer.org/download)
- Sqlite3 (or another database tool that allows handling SQLite databases)
- Git
- A good PHP editor: PHPStorm or something similar

About the skeleton:

- The skeleton is built on Slim (`slim/slim : ^4.0`)
- The templating engine of choice is Twig (`slim/twig-view`)
- The dependency injection container of choice is `php-di/php-di`
- The database access layer of choice is plain PDO
- The configuration should be provided in a .env file (`vlucas/phpdotenv`)
- There is logging support by using `monolog/monolog`
- Input validation should be simply done using `webmozart/assert` and throwing Slim dedicated HTTP exceptions

## Step-by-step set-up

Install dependencies:

composer install


Set up the database:

cd database
./apply_migrations.sh


Note: be aware that, if you are using WSL2 (Windows Subsystem for Linux), you'll have trouble opening SQLite databases
with a DB management app (PHPStorm, for example) in Windows **when they are stored within the virtualized WSL2 drive**.
The solution is to store the `db.sqlite` file on the Windows drive (`/mnt/c`) and configure the path to the file in the
application config (`.env`):

cd database
./apply_migrations.sh /mnt/c/Users/&lt;user>/AppData/Local/Temp/db.sqlite


Copy `.env.example` to `.env` and configure as necessary:

cp .env.example .env


Run the built-in server on http://localhost:8000

composer start


## Features

This Budget Tracker application provides the following key features:

* **User Authentication:** Users can register, log in, and log out securely. Registration includes username and password validation.
* **Expense Management (CRUD):** Users can create, view, edit, and delete their expenses. Expenses are listed with pagination, sorting by date, and filtering by year/month.
* **Dashboard Overview:** A dashboard page provides a summary of monthly expenses, including total expenditure and per-category totals and averages.
* **CSV Import:** Users can import expense data from a CSV file directly from the expenses list page. Duplicate entries and unknown categories are skipped, and import details are logged.
* **Input Validation:** Robust server-side validation is applied to all form submissions to ensure data integrity.
* **Secure Practices:** Password hashing is used for user registration, and prepared statements are utilized for database queries to prevent SQL injection. Users can only manage their own expenses.

## Tasks

### Before you start coding

Make sure you inspect the skeleton and identify the important parts:

- `public/index.php` - the web entry point
- `app/Kernel.php` - DI container and application setup
- classes under `app` - this is where most of your code will go
- templates under `templates` are almost complete, at least in terms of static mark-up; all you need is to make use of
  the Twig syntax to make them dynamic.

### Main tasks — for having a functional application

Start coding: search for `// TODO: ...` and fill in the necessary logic. Don't limit yourself to that; you can do
whatever you want, design it the way you see fit. The TODOs are a starting point that you may choose to use.

### Extra tasks — for extra points

Solve extra requirements for extra points. Some of them you can implement from the start, others we prefer you to attack
after you have a fully functional application, should you have time left. More instructions on this in the assignment.

### Deliver well designed quality code

Before delivering your solution, make sure to:

- format every file and make sure there is no commented code left, and code looks spotless

- run static analysis tools to check for code issues:

composer analyze


- run unit tests (in case you added any):

composer test


A solution with passing analysis and unit tests will receive extra points.

## Delivery details

Participant:
- Full name: [Your Full Name]
- Email address: [Your Email Address]

Features fully implemented:
- Register/Login/Logout functionality
- CRUD (Create/Read/Update/Delete) for Expense entities
- Dashboard page with monthly expense summary (total expenditure, per-category totals and averages)
- CSV file import for expense data (including skipping duplicates/unknown categories and logging)
- Prepared statements for DB queries
- Users can only change/delete their own expenses
- Proper password hashing for registration and verification for login
- "Password again" input for registration
- CSRF protection for register and login forms
- Session fixation prevention for login
- Categories and budget thresholds are configured via `.env` file.
- Pagination links for Expenses listing (previous/next only)

Other instructions about setting up the application (if any):
- Ensure you have the necessary PHP extensions enabled (e.g., `pdo_sqlite`).
- If using WSL2, remember to store the `db.sqlite` file on the Windows drive as described in the "Set up the database" section.
- Categories and their budgets are defined in the `.env` file (e.g., `APP_CATEGORIES="Gr
