# Cloud Basic Assignment Report

#taffoni e ruggero

## Introduction
In order to complete this project Docker containers have been used to run instances of Nextcloud and Locust, utilized to perform load testings. Other than that more containers have been created to run, respectively, Redis to implemnt caching, Nginx to perform load balancing, Postgres to menage a database.
The system has been implemented so that the users are provided with the possibility to log in and out the platform thanks to the modul Register, which can be installed and integrated to Nextcloud with its native GUI.
By default, when being created, it is possible to assign different roles to each for a new user, such as admin, simple user or a guest. It is aslo possible to define more roles as needed thaks to 
