# URL Shortener API

A URL shortener service built with PHP, MySQL, Redis


## Features

- URL shortening with custom length codes using base62
- User authentication with JWT
- Cache support with Redis
- RESTful API
- Docker containerization


## Prerequisites

- Docker
- Docker Compose
- Git


# Quick Start

1. Clone the repository:
```bash
git clone https://github.com/alirezaporthos/Url-Shortener-Task.git
cd Url-Shortener-Task
```

2. Start the containers:
```bash
docker compose up -d
```

3. Install composer packages:
```bash
docker exec -it url-shortener-app composer install
```

4. Access the services:

- API: http://localhost:8000
- phpMyAdmin: http://localhost:8080

5. Create a user and start creating short URLs


## API Endpoints

### Authentication

- POST /api/auth/register - Register a new user
- POST /api/auth/login - Login user

### URLS
- POST /api/urls - Create short URL
- GET /api/urls - List user's URLss
- PUT /api/urls/{id} - Update URL
- DELETE /api/urls/{id} - Delete URL
- GET /{shorturl-key} - Get the original URL(this can be modified to redirect)


## Environment Variables (field for ease of use)

Key environment variables:
- `DB_HOST` - Database host
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database user
- `DB_PASSWORD` - Database password
- `REDIS_HOST` - Redis host
- `REDIS_PORT` - Redis port
- `JWT_SECRET` - JWT signing key
- `URL_LENGTH` - Short URL code length


## Project Structure
```
.
├── config/               # Configuration files
│   ├── database.php      # Database connection settings
│   └── redis.php         # Redis connection settings
├── database/             # Database files
│   └── schema.sql        # Database schema
├── docker/               # Docker configuration
│   └── nginx/           
│       └── conf.d/       # Nginx configuration
│           └── app.conf
├── public/               # Public directory
│   └── index.php         # Application entry point
├── src/                  # Application source code
│   ├── Controllers/      # Request handlers(Controllers)
│   │   ├── AuthController.php
│   │   └── UrlController.php
│   ├── Core/             # Core framework components
│   │   ├── Cache.php
│   │   └── Database.php
│   ├── Models/           # Domain models
│   │   ├── User.php
│   │   └── Url.php
│   ├── Repositories/     # Data access layer
│   │   ├── UserRepository.php
│   │   └── UrlRepository.php
│   ├── Services/         # Business logic
│   │   ├── JwtService.php
│   │   ├── UserService.php
│   │   └── UrlService.php
│   ├── Middleware/       # Request middleware
│   │   └── AuthMiddleware.php
│   └── bootstrap.php     # Application bootstrap
├── .env                  # Environment variables
├── .gitignore            # Git ignore rules
├── composer.json         # PHP dependencies
├── docker-compose.yml    # Docker services config
├── Dockerfile            # PHP container build
└── README.md             # Readme file
```