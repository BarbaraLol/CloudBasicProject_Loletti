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
* nextcloud: using the latest version of ne
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
* locust
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
* nginx
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
