# Wordpress Developer base 
This is the base repo for SAM wordpress deploy.
All new wordpress are sarted with this base as wp-content


## Starting the developer setup

**First install docker and docker-compose**

Start the setup with 

```
docker-compose up
```


This will bring up the developer docker images and setup the wordpress for you.
You can now wisit your wordpress on your local computer at


**http://localhost**

**http://localhost/wp-admin** (admin/admin)


If you need a nother port modify the docker-compose.yaml and change port 80 to other port

```
     ports:
       - "??:80"

```

### DB Seed
When you run docker-compose the sql in the file seed/wordpress.sql will be seeden into the SQL server


## Push your changes to the online Wordpress

When updating the online wordpress simply push your changes to github master barnch


```
git add .
git commit -a -m "Update"
git push origin master
```


**YOUR DB CHANGES WILL NOT BE PUSHED AND MUST BE APPLIED MANUALL**


### Get the online version

Backup of the corrent running version online are saved into the branch fromserver.
Simply check out that bransh and start the docker compose to get the current state of your wordpress.
