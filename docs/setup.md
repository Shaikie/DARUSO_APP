# DARUSO Setup Guide

## Prerequisites

- PHP 8.4+
- Composer 2+
- PostgreSQL 18+
- Node.js 20+ (for frontend build)

## Installation

### 1. Clone the repository

```bash
git clone <repository-url> daruso
cd daruso
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=daruso
DB_USERNAME=daruso
DB_PASSWORD=your_password
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Seed development data

```bash
php artisan db:seed
```

### 7. Build frontend assets

```bash
npm run build
```

### 8. Start the development server

```bash
php artisan serve
```

## Development Credentials

After seeding, these accounts are available:

| Role | Email | Password |
|------|-------|----------|
| System Admin | admin@daruso.local | password |
| Secretary General | secretary@daruso.local | password |
| Ministry Leader | ministry@daruso.local | password |
| Student | student@daruso.local | password |

## Database Setup

### Create PostgreSQL database

```sql
CREATE DATABASE daruso;
CREATE USER daruso WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE daruso TO daruso;
ALTER USER daruso CREATEDB;
```

## Testing

```bash
php artisan test
```

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Run `npm run build`
6. Configure Nginx with PHP-FPM
7. Set up SSL/TLS
8. Configure proper file permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
