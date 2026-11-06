# pantilorovic — Laravel 13 Secure API

A classroom API project demonstrating registration, token login, protected profile access, and logout with Laravel Sanctum personal access tokens.

## Requirements

- PHP 8.4.1 or newer with `pdo_mysql` and `pdo_sqlite` (the feature tests use in-memory SQLite)
- Composer
- MySQL or MariaDB (WAMP/XAMPP is suitable)
- Node.js and npm
- Postman

## Setup

1. Create a MySQL database named `demo_app_integ2` using `utf8mb4_unicode_ci`.
2. From the project directory, install dependencies and create your local environment file:

   ```powershell
   composer install
   Copy-Item .env.example .env
   ```

   If `.env` already exists, keep its `APP_KEY` and update only the database settings. Set the MySQL username, password, and port for your local installation. Do not commit `.env`.

3. Generate an application key only if `.env` does not already contain one, then install Node dependencies and build the assets:

   ```powershell
   if (-not (Select-String -Path .env -Pattern '^APP_KEY=\S+' -Quiet)) { php artisan key:generate }
   npm install
   npm run build
   ```

4. Clear cached configuration and apply migrations:

   ```powershell
   php artisan config:clear
   php artisan migrate
   php artisan migrate:status
   ```

   The MySQL connection uses InnoDB. The default migration string length is 191 for classroom database compatibility.

5. Start the server and inspect the API routes:

   ```powershell
   php artisan serve
   php artisan route:list --path=api
   ```

   The default base URL is `http://127.0.0.1:8000`. If that port is already in use, Artisan will select another port; use the URL it prints in Postman.

## API endpoints

Send `Accept: application/json` with every request. Send `Content-Type: application/json` with JSON request bodies.

| Method | Endpoint | Purpose | Expected status |
|---|---|---|---|
| GET | `/api/hello` | Public API check | 200 |
| POST | `/api/register` | Register a user | 201 |
| POST | `/api/login` | Issue a personal access token | 200 |
| GET | `/api/profile` | Read the authenticated user's profile | 200 / 401 |
| POST | `/api/logout` | Revoke the current token | 200 |

Registration requires `name`, `email`, `password`, and `password_confirmation`. Login requires `email` and `password`. Invalid credentials return 422 with an `Invalid credentials.` validation message. Registration and login are limited to six requests per minute.

The profile and logout endpoints require `Authorization: Bearer <access_token>`. Logout revokes only the token used for that request; the same token will then receive 401.

## Postman

Import [`postman/pantilorovic.postman_collection.json`](./postman/pantilorovic.postman_collection.json). The collection covers all eight lab checks, including unauthenticated access, invalid login, and access after token revocation. Registration and login use sample credentials; choose a fresh email if the sample account already exists. The successful login request saves the returned token to the collection's `token` variable.

Never put a real token, password, or `.env` secret in screenshots, exports, or GitHub.

## Tests and submission

Run the API feature tests with:

```powershell
php artisan test
```

Complete [`SUBMISSION.md`](./SUBMISSION.md) with your name, section, repository URL, and the eight redacted Postman screenshots listed there before submitting.

Create an empty GitHub repository named `laravel13-secure-api-lab`, then push this project from its root:

```powershell
git init
git status
git add .
git status
git commit -m "Complete Laravel 13 secure API lab"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/laravel13-secure-api-lab.git
git push -u origin main
```

Replace `YOUR_USERNAME` with your GitHub username. Review the staged files before committing; `.env`, `vendor/`, and `node_modules/` must remain excluded.

## Security notes

- `.env`, `vendor/`, and `node_modules/` are excluded by `.gitignore`.
- The sample is for local learning, not production deployment.
- Use HTTPS, appropriate rate limits, and carefully scoped CORS origins for a real browser client.
- CORS is disabled for credentialed cross-origin requests by default; Postman does not enforce browser CORS.
