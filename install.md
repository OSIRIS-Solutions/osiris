sudo nano /etc/yum.repos.d/mongodb-org-5.0.repo

    [mongodb-org-6.0]
    name=MongoDB Repository
    baseurl=https://repo.mongodb.org/yum/redhat/$releasever/mongodb-org/6.0/x86_64/
    gpgcheck=1
    enabled=1
    gpgkey=https://www.mongodb.org/static/pgp/server-6.0.asc



sudo yum install -y mongodb-org

sudo yum -y update

sudo yum -y install gcc php-pear php-devel
sudo yum install php-ldap

sudo pecl install mongodb-1.12.0


sudo nano /etc/opt/remi/php74/php.ini
    add extension=/usr/lib64/php/modules/mongodb.so   
    add extension=/usr/lib64/php/modules/zip.so   

sudo systemctl start mongod
sudo systemctl restart php-fpm.service

> Install composer

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

composer require --ignore-platform-reqs mongodb/mongodb:1.12.0
composer require --ignore-platform-reqs phpoffice/phpword
mongorestore  dump/

composer update --ignore-platform-reqs

### Using Docker the docker environment for debugging with xdebug
#### Start dev environment
`docker compose -f docker-compose.dev.yml up -d --build`
Open web browser and go to **localhost:8080**

#### Configure Your IDE (e.g., PhpStorm)
To use XDebug for step debugging, configure your IDE accordingly:
1. **Set Debugging Port**:
    - Ensure your IDE uses `9003` as the XDebug port.
    - In PhpStorm: Go to `Settings > Languages & Frameworks > PHP > Debug` and set the Debug port to `9003`.

2. **Map Path in IDE**:
    - Configure `Path Mapping` to map the folder inside the container (`/var/www/html`) to your local project folder.

3. **Set Interpreter**:
    - Add your Docker container as a PHP interpreter in PhpStorm.

#### Special Instructions for Linux Hosts
On Linux, `host.docker.internal` is unavailable. To connect XDebug to your IDE:
- Replace `host.docker.internal` in the Dockerfile with the IP address of your host machine.
- Alternatively, add your host IP manually in the `xdebug.client_host`.

To find the host's IP:
``` bash
ip -4 addr show docker0 | grep -Po 'inet \K[\d.]+'
```
Update this IP in the `xdebug.client_host` configuration.
