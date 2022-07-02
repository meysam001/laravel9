# Installation

```
# clone it from git
git clone git@github.com:meysam001/laravel9.git

# run docker
docker-compose up -d

# go inside of php container
docker exec -it smsator_php bash
# or docker-compse exec php bash

# run supervisor
service supervisor start

# create .env file
cp .env.example .env

# install packages
composer install
```


