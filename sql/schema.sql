DROP DATABASE IF EXISTS skickaupp;
CREATE DATABASE skickaupp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skickaupp;

CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(40)  NOT NULL UNIQUE,
    email           VARCHAR(120) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('user','moderator') NOT NULL DEFAULT 'user',
    verified        TINYINT(1)   NOT NULL DEFAULT 0,
    verify_token    VARCHAR(64)  DEFAULT NULL,
    reset_token     VARCHAR(64)  DEFAULT NULL,
    reset_expires   DATETIME     DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE zones (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE routes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    grade       VARCHAR(10) NOT NULL,
    color       VARCHAR(20) NOT NULL,
    zone_id     INT         DEFAULT NULL,
    description TEXT,
    created_by  INT         DEFAULT NULL,
    created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active      TINYINT(1)  NOT NULL DEFAULT 1,
    FOREIGN KEY (zone_id)    REFERENCES zones(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sends (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT  NOT NULL,
    route_id  INT  NOT NULL,
    attempts  INT  NOT NULL DEFAULT 1,
    send_date DATE NOT NULL,
    note      VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE events (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(120) NOT NULL,
    description   TEXT,
    event_date    DATETIME     NOT NULL,
    location      VARCHAR(120),
    max_participants INT       DEFAULT NULL,
    created_by    INT          DEFAULT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE registrations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    event_id    INT NOT NULL,
    status      ENUM('registered','cancelled','attended') NOT NULL DEFAULT 'registered',
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_event (user_id, event_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    route_id   INT NOT NULL,
    body       TEXT NOT NULL,
    flagged    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
) ENGINE=InnoDB;


INSERT INTO zones (name) VALUES
    ('Slab-väggen'),
    ('Överhänget'),
    ('Kompetensväggen'),
    ('Barnhörnan');
INSERT INTO routes (name, grade, color, zone_id, description) VALUES
    ('Blå Drömmen', 'V3','blå',1,'Mjuk start, slopers i mitten.'),
    ('Röd Raket', 'V5','röd',2,'Power-rutt på överhänget.'),
    ('Gröna Vägen', '6a', 'grön', 3,'Klassisk teknikled.'),
    ('Gul Galenskap','7a+','gul', 2,'Crux nära toppen.');

INSERT INTO events (title, description, event_date, location, max_participants) VALUES
    ('Friday Send Session', 'Veckans gemensamma klätterkväll.', '2026-06-05 18:00:00', 'Klätterhallen', 30),
    ('Boulderkurs nybörjare', 'Tre tillfällen, instruktör moderator.', '2026-06-10 17:30:00', 'Boulderzonen', 12);
