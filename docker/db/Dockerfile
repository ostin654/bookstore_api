FROM postgres:12-alpine AS bookstore-db

ENV POSTGRES_PASSWORD=123456

COPY bootstrap.sql /docker-entrypoint-initdb.d/

EXPOSE 5432
