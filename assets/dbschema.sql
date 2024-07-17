CREATE TABLE login(
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(50) NOT NULL,
    role VARCHAR(50) NOT NULL
);

CREATE TABLE weightbrackets(
    weight FLOAT(4,2) PRIMARY KEY,
    price FLOAT(8,2) NOT NULL
);

CREATE TABLE products(
    partnumber INT PRIMARY KEY,
    quantity INT NOT NULL
);

CREATE TABLE customer(
    email VARCHAR(50) PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    address VARCHAR(250) NOT NULL
);

CREATE TABLE orders(
    email VARCHAR(50) NOT NULL,
    partnumber INT NOT NULL,
    quantity INT NOT NULL,
    orderno INT NOT NULL,
    PRIMARY KEY(email, partnumber, orderno)
);

