# Docker Setup Guide - PurpleBug Online Shopping

## Prerequisites

- Docker Desktop installed (Windows/Mac) or Docker Engine (Linux)
- Docker Compose v3.8 or higher
- At least 4GB of available RAM

## Quick Start

### 1. Environment Configuration

Copy the Docker environment file:
```bash
cp .env.docker .env
```

### 2. Generate Application Key

```bash
docker-compose run --rm app php artisan key:generate
```

### 3. Build and Start Containers

```bash
docker-compose up -d --build
```

This will start:
- **app**: Laravel application with Nginx (port 8000)
- **db**: MySQL 8.0 database (port 3306)
- **redis**: Redis cache server (port 6379)

### 4. Run Database Migrations

```bash
docker-compose exec app php artisan migrate --seed
```

### 5. Access Application

Open your browser and navigate to:
```
http://localhost:8000
```

## Docker Commands

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f db
```

### Stop Containers
```bash
docker-compose stop
```

### Start Containers
```bash
docker-compose start
```

### Restart Containers
```bash
docker-compose restart
```

### Stop and Remove Containers
```bash
docker-compose down
```

### Remove Containers and Volumes (⚠️ This deletes all data)
```bash
docker-compose down -v
```

## Laravel Artisan Commands

Run artisan commands inside the container:

```bash
# General format
docker-compose exec app php artisan [command]

# Examples
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list
```

## Database Access

### Using MySQL CLI
```bash
docker-compose exec db mysql -u purplebug_user -p purplebug_db
# Password: secret
```

### Using External Tools
Connect with your favorite database client:
- **Host**: localhost
- **Port**: 3306
- **Database**: purplebug_db
- **Username**: purplebug_user
- **Password**: secret

## Redis Access

```bash
# Connect to Redis CLI
docker-compose exec redis redis-cli

# Test connection
docker-compose exec redis redis-cli ping
```

## Troubleshooting

### Port Already in Use

If port 8000 is already in use, change it in `.env`:
```
APP_PORT=8080
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

### Database Connection Issues

Check if database is ready:
```bash
docker-compose exec app php artisan db:show
```

### Storage Permission Issues

Fix permissions:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Clear All Caches

```bash
docker-compose exec app php artisan optimize:clear
```

## Production Deployment

### 1. Update Environment Variables

Edit `.env` with production values:
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Use strong passwords for `DB_PASSWORD` and `DB_ROOT_PASSWORD`
- Generate a new `APP_KEY`

### 2. SSL/HTTPS Setup

For production, configure a reverse proxy (nginx/apache) or use a service like:
- Cloudflare
- AWS Application Load Balancer
- Nginx Proxy Manager

### 3. Optimize for Production

```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## Architecture

```
┌─────────────────────────────────────┐
│         Docker Compose              │
├─────────────────────────────────────┤
│                                     │
│  ┌───────────────────────────────┐ │
│  │  App Container (Port 8000)    │ │
│  │  - Nginx                      │ │
│  │  - PHP 8.2 FPM                │ │
│  │  - Laravel 12                 │ │
│  │  - Vue.js 3 (Built Assets)    │ │
│  └───────────────────────────────┘ │
│           │          │              │
│           ▼          ▼              │
│  ┌──────────┐   ┌─────────┐        │
│  │ MySQL 8  │   │ Redis 7 │        │
│  │ (3306)   │   │ (6379)  │        │
│  └──────────┘   └─────────┘        │
│                                     │
└─────────────────────────────────────┘
```

## Volume Persistence

The following data persists across container restarts:
- **db-data**: MySQL database files
- **redis-data**: Redis cache data
- **storage**: Laravel storage directory (logs, uploads)

## Backup

### Database Backup
```bash
docker-compose exec db mysqldump -u purplebug_user -psecret purplebug_db > backup.sql
```

### Database Restore
```bash
docker-compose exec -T db mysql -u purplebug_user -psecret purplebug_db < backup.sql
```

## Support

For issues or questions, please refer to:
- Laravel Documentation: https://laravel.com/docs
- Docker Documentation: https://docs.docker.com/
- Project README: README.md
