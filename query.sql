CREATE DATABASE IF NOT EXISTS blog CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE blog;
CREATE TABLE users (
    id VARCHAR(16) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password CHAR(128) NOT NULL,
    create_date DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (email),
    INDEX (id, email, password)
);

CREATE TABLE articles (
    id VARCHAR(16) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id VARCHAR(16),
    create_date DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE tags (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    tag VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (tag)
);

CREATE TABLE article_tag (
    article_id VARCHAR(16) NOT NULL,
    tag_id VARCHAR(16) NOT NULL,
    PRIMARY KEY (article_id, tag_id)
);