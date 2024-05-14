# Cloud Basic Assignment Report

#taffoni e ruggero

## Introduction
In order to complete this project Docker containers have been used to run instances of Nextcloud and Locust, utilized to perform load testings. Other than that more containers have been created to run, respectively, Redis to implemnt caching, Nginx to perform load balancing, Postgres to menage a database.
The system has been implemented so that the users are provided with the possibility to log in and out the platform thanks to the modul Register, which can be installed and integrated to Nextcloud with its native GUI.
By default, when being created, it is possible to assign different roles to each for a new user, such as admin, simple user or a guest. If needed it is aslo possible to define more roles throught the user management functionality.
Each user has its own private storage space, which can be directly accessed by them or by directly accessing to Nextcloud and locating the specific directory. Obviously, depending on the context, this can be considered either a feature or a vulnerability, In this latter case, we could mitigate this risk by activating the built-in encryption method, which allows the admins to access all the files and at the same time prevents the content to be read by everyone but the owner of them. Upload and storage space can be limited for each user.

By properly configuring the Nginx and Redis instances, the project can be run on different machines 
..........

## Deployment
### Docker Compose file
Right after having started Docker, the whole file-storage project can be deployed by running the following docker-compose.yaml file. Its purpose is to create the correct directories where the required volumes for the containers will be sotred.
```yaml
version: '3.8'

volumes:
  nextcloud:
  db:

services:
  db:
    image: postgres:latest
    container_name: db
    environment:
      - POSTGRES_USER=nextcloud
      - POSTGRES_PASSWORD=nextcloud
      - POSTGRES_DB=nextcloud
    restart: always
    volumes:
      - db:/var/lib/mysql
    
  redis:
    image: redis:alpine
    container_name: redis

  nextcloud:
    image: nextcloud:latest
    container_name: nextcloud
    depends_on:
      - db
    volumes:
      - nextcloud:/var/www/html
    environment:
      - POSTGRES_HOST=db
      - NEXTCLOUD_ADMIN_USER=admin
      - NEXTCLOUD_ADMIN_PASSWORD=admin
      - POSTGRES_USER=nextcloud
      - POSTGRES_PASSWORD=nextcloud
      - POSTGRES_DB=nextcloud

  locust:
    image: locustio/locust
    container_name: locust
    ports:
      - "8089:8089"
    volumes:
      - ./locust:/mnt/
    command: -f /mnt/locustfile.py --host http://nextcloud:80 --users 10 --spawn-rate 1 -t 5m
    depends_on:
      - nginx

  nginx:
    image: nginx:latest
    container_name: nginx
    links:
      - nextcloud
    ports:
      - 8080:80
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - nextcloud
```
### Docker compose file breakdown
In this section the docker compose file is analyzed in all its components:
* the first part is used to specify the version of the Docker compose used and to define the name of the volumes used for persisten storage
```yaml
version: '3.8'

volumes:
  nextcloud:
  db:
```
* db: it's the name of the volume containing PostgressSQL, used to perfomr the database management operations. The container has been configured with a user, password and a database name. Also, a restarting policy has beeing defined and the volume mounted in a specific directory inside the host machine.
```yaml
  db:
    image: postgres:latest
    container_name: db
    environment:
      - POSTGRES_USER=nextcloud
      - POSTGRES_PASSWORD=nextcloud
      - POSTGRES_DB=nextcloud
    restart: always
    volumes:
      - db:/var/lib/mysql
  ```
* redis: creates the Redis container used for caching in Nextcloud. 
```yaml
   redis:
    image: redis:alpine
    container_name: redis
  ```
* nextcloud: the docker image used is the latest version of Nextcloud and it depends on the 'db' service. The volume is mounted to the container's '/var/www/html/' directory. Reguarding the environment, its variables are set, including the database host, the admin username, the password and obviously the database name. The Nextcloud container depends ont he db one
```yaml
 nextcloud:
    image: nextcloud:latest
    container_name: nextcloud
    depends_on:
      - db
    volumes:
      - nextcloud:/var/www/html
    environment:
      - POSTGRES_HOST=db
      - NEXTCLOUD_ADMIN_USER=admin
      - NEXTCLOUD_ADMIN_PASSWORD=admin
      - POSTGRES_USER=nextcloud
      - POSTGRES_PASSWORD=nextcloud
      - POSTGRES_DB=nextcloud
```
* locust: this container is used specifically to generate load on the Nextcloud instance. Due to its testing purposes, its requires some extra care in order to allow it to work properly. It will be necessary to deactivate some security measures inside the nextcloud instance as well as to create a locustfile.py script, which will be analyzed later. The port used to access the Locust web interface is the 8089 and the volume corresponds to a local directory './locust' mounted to the container's '/mnt/' directory. Also the default values for the host on which to perform the load tests, the number of users, the spawn rate and the duration for conducting the tests are defined. The locust container is configured to depend on the Nginx container.
```yaml
locust:
    image: locustio/locust
    container_name: locust
    ports:
      - "8089:8089"
    volumes:
      - ./locust:/mnt/
    command: -f /mnt/locustfile.py --host http://nextcloud:80 --users 10 --spawn-rate 1 -t 5m
    depends_on:
      - nginx
```
* nginx: The Nginx container depends on the Nextcloud instance, as reported in 'depends_on', and its link is connected to the Nextcloud one, as specified in 'links'. The port used its the 8089. Its configuration requires a special configuration file, 'nginx.conf', which will be analyzed later.
```yaml
  nginx:
    image: nginx:latest
    container_name: nginx
    links:
      - nextcloud
    ports:
      - 8080:80
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - nextcloud
```
## Nginx configuration file
The configuration of Nginx is handled by both the docker-compose.yaml and the nginx.conf files. 
The nginx.conf configuartion file's code are reported below
```php
upstream nextcloud_backend {
        least_conn;
        server nextcloud:80;
    }

    # Define the log format with upstream information
    log_format upstreamlog '$remote_addr - $remote_user [$time_local] "$request" '
                           'upstream_response_time $upstream_response_time msec $msec request_time $request_time '
                           'upstream_addr $upstream_addr upstream_status $upstream_status';

    server {
        listen 80;
        server_name localhost;

        # Use the defined log format for access logs
        access_log /var/log/nginx/access.log upstreamlog;

        location / {
            proxy_pass http://nextcloud_backend;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            client_max_body_size 0; # Zero means no limit
        }

        # Nginx status page configuration
        location /nginx_status {
            stub_status on;
            allow 127.0.0.1;        # Only allow access from localhost
            allow 172.23.0.0/16;    # Allow access from the Docker network
            deny all;               # Deny access to anyone else
        }
    }
```
## Load testing procedure
### locustfile.py
```python
upstream nextcloud_backend {
        least_conn;
        server nextcloud:80;
    }

    # Define the log format with upstream information
    log_format upstreamlog '$remote_addr - $remote_user [$time_local] "$request" '
                           'upstream_response_time $upstream_response_time msec $msec request_time $request_time '
                           'upstream_addr $upstream_addr upstream_status $upstream_status';

    server {
        listen 80;
        server_name localhost;

        # Use the defined log format for access logs
        access_log /var/log/nginx/access.log upstreamlog;

        location / {
            proxy_pass http://nextcloud_backend;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            client_max_body_size 0; # Zero means no limit
        }

        # Nginx status page configuration
        location /nginx_status {
            stub_status on;
            allow 127.0.0.1;        # Only allow access from localhost
            allow 172.23.0.0/16;    # Allow access from the Docker network
            deny all;               # Deny access to anyone else
        }
    }
```
