-- ============================================================
-- usellbuy.sql — Script SQL complet
-- UsellBuy — ENSI 2025/2026
-- Création des tables + insertion des données
-- ============================================================

CREATE DATABASE IF NOT EXISTS usellbuy
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE usellbuy;

-- ============================================================
-- TABLE 1 : products
-- Correspond à l'objet JavaScript Product(id, name, description,
-- price, country, category, image)
-- ============================================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id          INT            NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255)   NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)  NOT NULL CHECK (price >= 0),
    country     VARCHAR(100)   DEFAULT 'Unknown',
    category    VARCHAR(100)   DEFAULT 'Général',
    image_url   VARCHAR(500),
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (name, description, price, country, category, image_url) VALUES
('Polaroid Camera',     'Instant film camera — fun and retro.',          89.99,   'Italy',   'Electronics', 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=600&q=80'),
('Wireless Headphones', 'Amazing sound quality, long battery life.',     129.99,  'USA',     'Electronics', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80'),
('Smartwatch Series 9', 'Sleek, functional and stylish on the wrist.',   249.99,  'Germany', 'Electronics', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80'),
('Handcrafted Chair',   'Super comfortable and solid build.',            199.99,  'Sweden',  'Furniture',   'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=600&q=80'),
('Gaming Laptop',       'Runs all games smoothly, high performance.',    1299.99, 'Japan',   'Electronics', 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=600&q=80'),
('Coffee Beans',        'Café quality coffee from the comfort of home.', 39.99,   'Italy',   'Food',        'https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?auto=format&fit=crop&w=600&q=80'),
('Designer Sneakers',   'Very comfortable and trendy street style.',     199.99,  'Italy',   'Fashion',     'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?auto=format&fit=crop&w=600&q=80'),
('Electric Guitar',     'Incredible sound, perfect for all levels.',     599.99,  'USA',     'Music',       'https://images.unsplash.com/photo-1564186763535-ebb21ef5277f?auto=format&fit=crop&w=600&q=80');

-- ============================================================
-- TABLE 2 : contact_messages
-- Correspond au formulaire Contact (prénom, nom, email, message)
-- ============================================================
DROP TABLE IF EXISTS contact_messages;
CREATE TABLE contact_messages (
    id         INT          NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    sent_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO contact_messages (first_name, last_name, email, message) VALUES
('Ahmed',   'Mohsen',   'ahmed.mohsen@gmail.com',   'Hello, I would like more information about your services.'),
('Sara',    'Ben Ali',  'sara.benali@gmail.com',    'I have a problem with my order #1042. Please help.'),
('Yassine', 'Trabelsi', 'yassine.t@outlook.com',    'Great platform! I would like to become a seller.'),
('Leila',   'Mansour',  'leila.m@yahoo.com',        'Can you add more payment methods? PayPal would be great.'),
('Omar',    'Chaabane', 'omar.chaabane@gmail.com',  'I am interested in a partnership with UsellBuy.');

-- ============================================================
-- TABLE 3 : questionnaire_responses
-- Correspond au formulaire Questionnaire (nom, email, satisfaction,
-- note, fréquence, recommandation, commentaire)
-- ============================================================
DROP TABLE IF EXISTS questionnaire_responses;
CREATE TABLE questionnaire_responses (
    id             INT          NOT NULL AUTO_INCREMENT,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(255) NOT NULL,
    satisfaction   VARCHAR(50),
    features_used  VARCHAR(255),
    rating         TINYINT      CHECK (rating BETWEEN 1 AND 5),
    frequency      VARCHAR(50),
    recommendation TINYINT      CHECK (recommendation BETWEEN 0 AND 10),
    comments       TEXT         NOT NULL,
    submitted_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO questionnaire_responses (full_name, email, satisfaction, features_used, rating, frequency, recommendation, comments) VALUES
('Ahmed Mohsen',   'ahmed@gmail.com',   'tres-satisfait', 'shop,payment',         5, 'weekly',  9,  'Excellent platform, very easy to use!'),
('Sara Ben Ali',   'sara@gmail.com',    'satisfait',      'shop,sell,support',    4, 'monthly', 8,  'Good experience overall, delivery could be faster.'),
('Yassine Trabelsi','yassine@gmail.com','neutre',         'search,mobile',        3, 'rarely',  6,  'The mobile version needs improvement.'),
('Leila Mansour',  'leila@yahoo.com',   'tres-satisfait', 'shop,payment,support', 5, 'daily',   10, 'I use UsellBuy every day, it is my go-to marketplace!'),
('Omar Chaabane',  'omar@gmail.com',    'satisfait',      'sell,shop',            4, 'weekly',  7,  'Selling is straightforward. Would love more analytics.');
