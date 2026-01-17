CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    is_public TINYINT(1) DEFAULT 1,
    description TEXT,
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    correct_option_index INT NOT NULL, -- Stores 0, 1, 2, or 3
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text TEXT NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);


ALTER TABLE quizzes ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;

INSERT IGNORE INTO quizzes (id, user_id, title, description) VALUES
(1, 1, 'Vseobecny prehlad', 'Otestuj si svoje vedomosti z roznych oblasti.'),
(2, 1, 'Programovanie v PHP', 'Zaklady jazyka PHP, premenne a backend logika.');


INSERT IGNORE INTO questions (id, quiz_id, question_text, correct_option_index) VALUES
(1, 1, 'Ake je hlavné mesto Slovenska?', 0),
(2, 1, 'Ktora planeta je najblizsie k Slnku?', 2);

INSERT IGNORE INTO question_options (question_id, option_text) VALUES
(1, 'Bratislava'), (1, 'Kosice'), (1, 'Zilina'), (1, 'Banska Bystrica'),
(2, 'Venusa'), (2, 'Zem'), (2, 'Merkur'), (2, 'Mars');


INSERT IGNORE INTO questions (id, quiz_id, question_text, correct_option_index) VALUES
(3, 2, 'Co znamena skratka PHP?', 1),
(4, 2, 'Akym znakom zacinaju premenne v PHP?', 0),
(5, 2, 'Ktora funkcia sa pouziva na vypisanie textu?', 2),
(6, 2, 'Ako sa spravne ukoncuje prikaz v PHP?', 3);

INSERT IGNORE INTO question_options (question_id, option_text) VALUES

(3, 'Private Home Page'),
(3, 'PHP: Hypertext Preprocessor'),
(3, 'Personal Hypertext Processor'),
(3, 'Program High Protocol'),

(4, '$ (Dolar)'),
(4, '# (Mriezka)'),
(4, '@ (Zavinac)'),
(4, '& (Ampersand)'),

(5, 'print_text()'),
(5, 'write()'),
(5, 'echo'),
(5, 'display()'),

(6, 'Bodkou (.)'),
(6, 'Ciarkou (,)'),
(6, 'Novzm riadkom'),
(6, 'Bodkociarkou (;)');
