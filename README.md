
# Translation Management Service

A high-performance API-driven translation management service built with **Laravel 12** and **Redis**, designed to handle large datasets efficiently.

---

## Features

- **Store translations** for multiple locales (`en`, `fr`, `es`, etc.)
- **Tag translations** for context (`mobile`, `desktop`, `web`, etc.)
- **RESTful API endpoints** for CRUD operations
- **JSON export endpoint** for frontend applications
- **Redis caching** for high performance (<500ms response time)
- **Event-driven cache management**
- **Token-based authentication** with Laravel Sanctum

---

## Prerequisites

- PHP 8.0 or higher
- Composer
- Redis server
- MySQL or MariaDB
- Laravel 12

---

## Localhost Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd translation-management-service
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

**Configure your database and Redis settings in `.env`:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=translation_service
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Install API Support

```bash
php artisan install:api
```

### 7. Populate Database with Test Data

```bash
php artisan translations:populate 100000
```

### 8. Start the Queue Worker with Cache Initialization

```bash
php artisan queue:start-with-cache --queue=cache,default
```

### 9. Start the Development Server

```bash
php artisan serve
```

The application is now running at [http://localhost:8000](http://localhost:8000)

---

## Server Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd translation-management-service
```

### 2. Install Dependencies

```bash
composer install --no-dev
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate --force
```

### 6. Install API Support

```bash
php artisan install:api
```

### 7. Populate Database with Data

```bash
php artisan translations:populate 100000
```

### 8. Configure Queue Worker

**Install and configure Supervisor to manage the queue worker:**

```bash
sudo apt-get install supervisor
```

**Create a Supervisor configuration file:**

```bash
sudo nano /etc/supervisor/conf.d/translation-worker.conf
```

**Add the following configuration:**
```ini
[program:translation-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --queue=cache,default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log
stopwaitsecs=3600
```

**Start the Supervisor service:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start translation-worker
```

### 9. Configure Nginx

Configure your web server (Nginx/Apache) to point to the `public` directory of your project.

### 10. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## API Usage

### Authentication

**Register a new user:**

```bash
curl -X POST http://localhost:8000/api/register -H "Content-Type: application/json" -d '{"name":"John Doe","email":"john@example.com","password":"password","password_confirmation":"password"}'
```

**Login to get a token:**

```bash
curl -X POST http://localhost:8000/api/login -H "Content-Type: application/json" -d '{"email":"john@example.com","password":"password"}'
```

---

### Translation Endpoints

- `GET /api/translations` — List translations with optional filtering  
- `POST /api/translations` — Create a new translation  
- `GET /api/translations/{id}` — Get a specific translation  
- `PUT /api/translations/{id}` — Update a translation  
- `DELETE /api/translations/{id}` — Delete a translation  
- `GET /api/translations/export` — Export all translations as JSON  
- `GET /api/translations/export?locale=en` — Export translations for a specific locale  
- `GET /api/translations/export?tag=web` — Export translations for a specific tag  

---

## Technologies Used

- **Laravel 12**
- **Redis**
- **MySQL**
- **Laravel Sanctum**
