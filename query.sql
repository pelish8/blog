CREATE DATABASE IF NOT EXISTS blog CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE blog;
CREATE TABLE users (
    id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password CHAR(128) NOT NULL,
    create_date DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (email),
    INDEX (id, email, password)
) ENGINE=InnoDB;

CREATE TABLE articles (
    id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id VARCHAR(16),
    url_path VARCHAR(255) NOT NULL,
    create_date DATETIME NOT NULL,
    PRIMARY KEY (id),
    INDEX (id, url_path, create_date)
) ENGINE=InnoDB;

CREATE TABLE tags (
    id CHAR(36) NOT NULL,
    tag VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (tag)
) ENGINE=InnoDB;

CREATE TABLE article_tag (
    article_id CHAR(36) NOT NULL,
    tag_id CHAR(36) NOT NULL,
    PRIMARY KEY (article_id, tag_id)
) ENGINE=InnoDB;

CREATE TABLE comments (
    id CHAR(36) NOT NULL,
    comment TEXT NOT NULL,
    article_id CHAR(36) NOT NULL,
    user_id CHAR(36) NULL,
    user_name CHAR(255) NULL,
    create_date DATETIME NOT NULL,
    PRIMARY KEY (id),
INDEX (article_id)
) ENGINE=InnoDB;

-- CREATE TABLE article_comment (
--     article_id CHAR(36) NOT NULL,
--     comment_id CHAR(36) NOT NULL,
--     PRIMARY KEY (article_id, comment_id)
-- ) ENGINE=InnoDB;