-- Implement the database schema as discussed in the design.

CREATE TABLE languages
(
    language_code varchar(10)  NOT NULL,
    language_name varchar(255) NOT NULL,
    PRIMARY KEY (language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE translations
(
    translation_id  int         NOT NULL AUTO_INCREMENT,
    source_language varchar(10) NOT NULL,
    target_language varchar(10) NOT NULL,
    source_text     text        NOT NULL,
    translated_text text        NOT NULL,
    timestamp       timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (translation_id),
    KEY             fk_source_language (source_language),
    KEY             fk_target_language (target_language),
    CONSTRAINT fk_source_language FOREIGN KEY (source_language) REFERENCES languages (language_code) ON DELETE CASCADE,
    CONSTRAINT fk_target_language FOREIGN KEY (target_language) REFERENCES languages (language_code) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;