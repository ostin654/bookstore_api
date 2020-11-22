# How to install

## Start containers

`docker-compose up -d`

## Bootstrap

Download dependences, clear cache, apply migrations and fill database.

`docker-compose run --rm php bin/bootstrap`

# How to run tests

## Prepare environment

`docker-compose run --rm tests bin/reset_tests`

## Run tests

`docker-compose run --rm tests bin/phpunit`

# API examples

## Load a book

`curl http://localhost:7777/ru/book/511`

## Create an author

`curl -d "{\"name\":[{\"locale\":\"ru\",\"value\":\"Иванов И.\"},{\"locale\":\"en\",\"value\":\"Ivanov I.\"}]}" -H "Content-Type: application/json" -X POST http://localhost:7777/author/create`

## Create a book

`curl -d "{\"name\":[{\"locale\":\"ru\", \"value\":\"Преступление и наказание\"},{\"locale\":\"en\",\"value\":\"Crime and punishment\"}], \"authors\":[4, 5]}" -H "Content-Type: application/json" -X POST http://localhost:7777/book/create`

## Search books

`curl http://localhost:7777/ru/book/search?search=punishment`
