
CREATE ROLE bookstore WITH LOGIN ENCRYPTED PASSWORD 'bookstore';
CREATE DATABASE bookstore WITH OWNER bookstore;

CREATE ROLE bookstore_test WITH LOGIN ENCRYPTED PASSWORD 'bookstore_test';
CREATE DATABASE bookstore_test WITH OWNER bookstore_test;
