# Documentation: OSIRIS PHP Docker Image

## Overview

This Dockerfile creates an optimized PHP-FPM image for the OSIRIS application. It uses a multi-stage build process to minimize the resulting image size and improve build efficiency.

## Technical Details

- **Base Image**: PHP 8.1 with FPM on Alpine Linux
- **PHP Extensions**:
  - LDAP
  - ZIP
  - MongoDB
- **Author**: Paul C. Gaida

## Build Process

The Dockerfile implements a two-stage build process:

1. **Composer Stage**: 
   - Installs Composer and PHP dependencies
   - Uses only the necessary files (`composer.json` and `composer.lock`)
   - Optimized for better caching and faster builds

2. **Main Stage**:
   - Installs necessary system packages and PHP extensions
   - Copies the vendor files installed in the first stage
   - Copies the application code and sets up permissions

## Installed Packages and Extensions

- **System Packages**:
  - openldap-dev (for LDAP support)
  - libzip-dev (for ZIP support)
  - openssl-dev (for secure connections)
  - autoconf, gcc, g++, make (build tools for extension compilation)

- **PHP Extensions**:
  - ldap (for LDAP authentication)
  - zip (for ZIP file handling)
  - mongodb (for MongoDB database connections)

## Usage

### Prerequisites

- Docker installed on the host system
- The OSIRIS source files in the current directory

### Building the Image

```bash
docker build -t osiris:latest .
```

### Starting a Container

```bash
docker run -d --name osiris-app -p 9000:9000 osiris:latest
```

To use the application with a web server, connect it to the PHP-FPM port 9000.

### With Volumes for Development

```bash
docker run -d --name osiris-app \
  -p 9000:9000 \
  -v $(pwd):/var/www/html \
  osiris:latest
```

### With Docker Compose

**Start development environment:**

```bash
docker-compose -f docker-compose.dev.yml up -d
```

**Start production environment:**

```bash
docker-compose -f docker-compose.prod.yml up -d
```


## Notes

- The `/var/www/html/img` directory has extended write permissions for the `www-data` user
- Vendor dependencies are installed during the build and placed in the final image
- Only the PHP extensions necessary for the application are installed

## Customizations

To install additional PHP extensions, extend the `docker-php-ext-install` command:

```dockerfile
RUN docker-php-ext-install ldap zip mysqli pdo_mysql
```

To install additional PECL extensions:

```dockerfile
RUN pecl install redis && docker-php-ext-enable redis
```
