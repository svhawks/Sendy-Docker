# Instaling docker

[Install docker](https://docs.docker.com/engine/installation/)

# Sendy service

[Send newsletters, 100x cheaper](https://sendy.co/)

# Building the container
 Once you have setup the project run:  

```
docker build -t sendy
```


# Getting it running
To run apache in a background process, simply start the container using the following command:
```

docker run -p 8080:80 -d sendy
```

-p 8080:80 publishes port 80 in the container to 8080 on the host machine.
-d detaches from the process, use docker ps and docker stop to … stop.

Note: on OSX use `docker-machine ip default` to get the right IP to use (assuming default is your machine name).
Sometimes you’ll want to debug issues with the container; maybe there are PHP configuration issues or you want to view error logs. To do that you can start the container in interactive mode and then start apache manually:
```
docker run -i -t -p 8080:80 mysite /bin/bash
apachectl start

```
# Making changes
If you’re actively developing you want to be able to change files in your usual editor and have them reflected within the container without having to rebuild it. The -v flag allows us to mount a directory from the host into the container:

```
docker run -p 8080:80 -d -v /Users/dan/site:/var/www/site sendy
```

[Tutorial article on medium](https://medium.com/dev-tricks/apache-and-php-on-docker-44faef716150#.ydev7br5c)
