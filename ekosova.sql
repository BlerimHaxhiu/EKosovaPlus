CREATE DATABASE IF NOT EXISTS ekosova_plus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ekosova_plus;

DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS scholarship_documents;
DROP TABLE IF EXISTS scholarship_rules;
DROP TABLE IF EXISTS scholarships;
DROP TABLE IF EXISTS scholarship_template_documents;
DROP TABLE IF EXISTS scholarship_template_rules;
DROP TABLE IF EXISTS scholarship_templates;
DROP TABLE IF EXISTS student_documents;
DROP TABLE IF EXISTS student_family_members;
DROP TABLE IF EXISTS student_profiles;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','provider','admin') NOT NULL,
    provider_type ENUM('OJQ','Biznes','Institucion Arsimor','Drejtori Komunale e Arsimit','Ofrues i Pavarur') NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    personal_number VARCHAR(30),
    first_name VARCHAR(80),
    last_name VARCHAR(80),
    birth_date DATE,
    birth_place VARCHAR(120),
    residence VARCHAR(160),
    gender ENUM('Mashkull','Femer','Tjeter') DEFAULT 'Tjeter',
    previous_education TEXT,
    annual_circulation DECIMAL(10,2) DEFAULT 0.00,
    employment_status ENUM('I punesuar','I papune') DEFAULT 'I papune',
    university VARCHAR(160) NOT NULL,
    city VARCHAR(80) NOT NULL,
    average_grade DECIMAL(4,2) NOT NULL DEFAULT 8.00,
    social_status VARCHAR(80) NOT NULL DEFAULT 'Standard',
    academic_system ENUM('SEMS','SMU') NOT NULL DEFAULT 'SEMS',
    student_active TINYINT(1) NOT NULL DEFAULT 1,
    transcript_summary TEXT,
    bank_name VARCHAR(120) NOT NULL DEFAULT 'Banka Ekonomike',
    bank_account_holder VARCHAR(160),
    bank_account_number VARCHAR(80),
    bank_iban VARCHAR(80),
    bank_branch VARCHAR(120),
    is_veteran_child TINYINT(1) NOT NULL DEFAULT 0,
    is_orphan TINYINT(1) NOT NULL DEFAULT 0,
    receives_social_assistance TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    relation ENUM('Babai','Nena','Bashkeshorti/ja','Femija') NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    birth_date DATE,
    birth_place VARCHAR(120),
    residence VARCHAR(160),
    gender ENUM('Mashkull','Femer','Tjeter') DEFAULT 'Tjeter',
    previous_education TEXT,
    annual_circulation DECIMAL(10,2) DEFAULT 0.00,
    receives_social_assistance TINYINT(1) NOT NULL DEFAULT 0,
    employment_status ENUM('I punesuar','I papune') DEFAULT 'I papune',
    is_alive TINYINT(1) NOT NULL DEFAULT 1,
    is_war_hero TINYINT(1) NOT NULL DEFAULT 0,
    is_veteran TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_profile_id INT NOT NULL,
    document_name VARCHAR(180) NOT NULL,
    source_institution VARCHAR(160) NOT NULL,
    verification_status ENUM('verified','missing','not_required') NOT NULL DEFAULT 'verified',
    stored_data_summary TEXT,
    verified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scholarship_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(80) NOT NULL,
    provider_name VARCHAR(160) NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_template_provider (category, provider_name)
) ENGINE=InnoDB;

CREATE TABLE scholarship_template_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    rule_key VARCHAR(120) NOT NULL,
    operator VARCHAR(30) NOT NULL DEFAULT '=',
    rule_value VARCHAR(180),
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    points INT NOT NULL DEFAULT 0,
    description TEXT,
    FOREIGN KEY (template_id) REFERENCES scholarship_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scholarship_template_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    document_section_name VARCHAR(160) NOT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    is_optional_bonus TINYINT(1) NOT NULL DEFAULT 0,
    description TEXT,
    FOREIGN KEY (template_id) REFERENCES scholarship_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scholarships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NULL,
    provider_id INT NULL,
    category VARCHAR(80) NULL,
    provider_name VARCHAR(160) NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT,
    start_date DATE NULL,
    end_date DATE NULL,
    amount DECIMAL(10,2) NOT NULL,
    deadline DATE NOT NULL,
    min_grade DECIMAL(4,2) NULL,
    required_university VARCHAR(160) NULL,
    required_city VARCHAR(80) NULL,
    required_social_status VARCHAR(80) NULL,
    requires_veteran_child TINYINT(1) NOT NULL DEFAULT 0,
    requires_orphan TINYINT(1) NOT NULL DEFAULT 0,
    requires_social_assistance TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES scholarship_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE scholarship_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT NOT NULL,
    rule_key VARCHAR(120) NOT NULL,
    operator VARCHAR(30) NOT NULL DEFAULT '=',
    rule_value VARCHAR(180),
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    points INT NOT NULL DEFAULT 0,
    description TEXT,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE scholarship_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT NOT NULL,
    document_section_name VARCHAR(160) NOT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    is_optional_bonus TINYINT(1) NOT NULL DEFAULT 0,
    description TEXT,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    applied_at DATETIME NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'fituar',
    points_total INT NULL,
    result_message TEXT NULL,
    verification_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_application (student_id, scholarship_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NULL,
    student_id INT NOT NULL,
    scholarship_category VARCHAR(80) NULL,
    provider_name VARCHAR(160) NULL,
    message TEXT NOT NULL,
    reason TEXT NULL,
    status ENUM('pending','reviewing','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name, username, email, password_hash, role, provider_type) VALUES
('Administratori', 'admin', 'admin@ekosova-plus.local', '$2y$10$p6QCf/nfDnZXvQhSiz5nOO62IW.tb/fmG8L7cw9pavoGl4o.VmUh2', 'admin', NULL),
('Golden Eagle', 'Geagle', 'golden.eagle@example.local', '$2y$10$CkZqv8U9eWPKCp36Jf/2lOyGYe8CammLOY0i4ibiBZAK/S5JXqrqK', 'provider', 'Biznes'),
('VILDANA Foundation', 'Vildana', 'vildana@example.local', '$2y$10$CkZqv8U9eWPKCp36Jf/2lOyGYe8CammLOY0i4ibiBZAK/S5JXqrqK', 'provider', 'Biznes'),
('OJQ TOKA', 'Toka', 'toka@example.local', '$2y$10$CkZqv8U9eWPKCp36Jf/2lOyGYe8CammLOY0i4ibiBZAK/S5JXqrqK', 'provider', 'OJQ'),
('AMIK', 'Amik', 'amik@example.local', '$2y$10$CkZqv8U9eWPKCp36Jf/2lOyGYe8CammLOY0i4ibiBZAK/S5JXqrqK', 'provider', 'OJQ'),
('Universiteti Kadri Zeka', 'UKZ', 'ukz@example.local', '$2y$10$arZmwA.pHAT9NoumPY02Ze9Wn3S4Cp6.k9hIuMU2smHziU8tg4FMu', 'provider', 'Institucion Arsimor'),
('Universiteti Hasan Prishtina', 'UHP', 'uhp@example.local', '$2y$10$arZmwA.pHAT9NoumPY02Ze9Wn3S4Cp6.k9hIuMU2smHziU8tg4FMu', 'provider', 'Institucion Arsimor'),
('Universiteti Haxhi Zeka', 'UHZ', 'uhz@example.local', '$2y$10$arZmwA.pHAT9NoumPY02Ze9Wn3S4Cp6.k9hIuMU2smHziU8tg4FMu', 'provider', 'Institucion Arsimor'),
('Komuna e Kamenices', 'KK06', 'kk06@example.local', '$2y$10$OXzHeGc8oxhAOAbvNzuTQ.asazXakP9BictOzHpGvffYaLHoyeaUS', 'provider', 'Drejtori Komunale e Arsimit'),
('Komuna e Gjilanit', 'KGJ06', 'kgj06@example.local', '$2y$10$OXzHeGc8oxhAOAbvNzuTQ.asazXakP9BictOzHpGvffYaLHoyeaUS', 'provider', 'Drejtori Komunale e Arsimit'),
('Komuna e Vitise', 'KV06', 'kv06@example.local', '$2y$10$OXzHeGc8oxhAOAbvNzuTQ.asazXakP9BictOzHpGvffYaLHoyeaUS', 'provider', 'Drejtori Komunale e Arsimit'),
('Komuna e Ferizajit', 'KF05', 'kf05@example.local', '$2y$10$OXzHeGc8oxhAOAbvNzuTQ.asazXakP9BictOzHpGvffYaLHoyeaUS', 'provider', 'Drejtori Komunale e Arsimit'),
('Arta Berisha', 'student1', 'arta.berisha@example.local', '$2y$10$CkZqv8U9eWPKCp36Jf/2lOyGYe8CammLOY0i4ibiBZAK/S5JXqrqK', 'student', NULL),
('Dion Krasniqi', 'student2', 'dion.krasniqi@example.local', '$2y$10$CkZqv8U9eWPKCp36Jf/2lOyGYe8CammLOY0i4ibiBZAK/S5JXqrqK', 'student', NULL);

INSERT INTO student_profiles (
    user_id, personal_number, first_name, last_name, birth_date, birth_place, residence, gender,
    previous_education, annual_circulation, employment_status, university, city, average_grade,
    social_status, academic_system, student_active, transcript_summary, bank_name,
    bank_account_holder, bank_account_number, bank_iban, bank_branch,
    is_veteran_child, is_orphan, receives_social_assistance
) VALUES
((SELECT id FROM users WHERE username='student1'), '1234567890', 'Arta', 'Berisha', '2002-04-18', 'Kamenice', 'Kamenice', 'Femer',
 'Gjimnazi Ismail Qemali; diploma e gjimnazit; mirenjohje per sukses te shkelqyer; cmimi i dyte ne gare komunale te matematikes.', 1850.00, 'I papune',
 'Universiteti Kadri Zeka', 'Kamenice', 9.20, 'Femije veterani', 'SEMS', 1,
 'Transkripta: 18 provime te perfunduara, nota mesatare 9.20, pa obligime aktive.',
 'Banka Ekonomike', 'Arta Berisha', '1501001000012345', 'XK051501001000012345', 'Kamenice', 1, 0, 0),
((SELECT id FROM users WHERE username='student2'), '9876543210', 'Dion', 'Krasniqi', '2001-09-02', 'Gjilan', 'Gjilan', 'Mashkull',
 'Shkolla e mesme teknike; diploma e gjimnazit; certifikate pjesemarrjeje ne projekte rinore.', 960.00, 'I papune',
 'Universiteti Hasan Prishtina', 'Gjilan', 7.80, 'Ndihme sociale', 'SMU', 1,
 'Transkripta: 16 provime te perfunduara, nota mesatare 7.80, nje provim ne afatin vijues.',
 'Raiffeisen Bank', 'Dion Krasniqi', '1702002000098765', 'XK051702002000098765', 'Gjilan', 0, 0, 1);

INSERT INTO student_family_members (
    student_profile_id, relation, first_name, last_name, birth_date, birth_place, residence, gender,
    previous_education, annual_circulation, receives_social_assistance, employment_status,
    is_alive, is_war_hero, is_veteran
) VALUES
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Babai', 'Ilir', 'Berisha', '1974-02-11', 'Kamenice', 'Kamenice', 'Mashkull', 'Shkolla e mesme; trajnim profesional.', 3200.00, 0, 'I punesuar', 1, 0, 1),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Nena', 'Lumnije', 'Berisha', '1978-07-23', 'Gjilan', 'Kamenice', 'Femer', 'Shkolla e mesme.', 0.00, 0, 'I papune', 1, 0, 0),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Babai', 'Arben', 'Krasniqi', '1970-05-14', 'Gjilan', 'Gjilan', 'Mashkull', 'Shkolla e mesme.', 2100.00, 0, 'I punesuar', 1, 0, 0),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Nena', 'Teuta', 'Krasniqi', '1976-12-05', 'Viti', 'Gjilan', 'Femer', 'Shkolla e mesme.', 0.00, 1, 'I papune', 1, 0, 0);

INSERT INTO student_documents (student_profile_id, document_name, source_institution, verification_status, stored_data_summary) VALUES
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Kopja e leternjoftimit', 'Agjencia e Regjistrimit Civil', 'verified', 'Identiteti, numri personal, vendlindja dhe shtetesia jane verifikuar.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Vertetimi prej fakultetit', 'SEMS - Universiteti Kadri Zeka', 'verified', 'Student aktiv ne vitin akademik aktual.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Certifikata e notave', 'SEMS - Universiteti Kadri Zeka', 'verified', 'Nota mesatare 9.20 dhe transkripta e notave jane ruajtur ne profil.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Certifikata e vendbanimit', 'Komuna e Kamenices', 'verified', 'Vendbanimi aktiv: Kamenice.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Certifikata e shtetesise', 'Agjencia e Regjistrimit Civil', 'verified', 'Shtetesia: Kosove.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Deshmi per veteran pjesetar', 'Organizata e Veteraneve / Regjistri komunal', 'verified', 'Babai rezulton veteran; statusi i femijes se veteranit eshte i konfirmuar.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Trusti pensional i prinderve', 'Trusti Pensional', 'verified', 'Qarkullimet vjetore te prinderve jane lexuar per verifikim social.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Deklarata e bashkesise familjare', 'Komuna e Kamenices', 'verified', 'Bashkesia familjare permban babain dhe nenen.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Diploma e gjimnazit', 'Gjimnazi Ismail Qemali', 'verified', 'Diploma e shkollimit te mesem eshte regjistruar.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Shpenzimet per gjate vitit', 'Profili financiar lokal', 'verified', 'Shpenzimet vjetore te deklaruara jane te ruajtura per simulim.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student1'), 'Certifikata, Mirenjohje, Levdata, Diploma', 'Profili akademik lokal', 'verified', 'Mirenjohje per sukses dhe cmim ne gare komunale.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Kopja e leternjoftimit', 'Agjencia e Regjistrimit Civil', 'verified', 'Identiteti dhe numri personal jane verifikuar.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Vertetimi prej fakultetit', 'SMU - Universiteti Hasan Prishtina', 'verified', 'Student aktiv ne vitin akademik aktual.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Certifikata e notave', 'SMU - Universiteti Hasan Prishtina', 'verified', 'Nota mesatare 7.80 dhe transkripta jane ruajtur ne profil.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Certifikata e vendbanimit', 'Komuna e Gjilanit', 'verified', 'Vendbanimi aktiv: Gjilan.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Certifikata e shtetesise', 'Agjencia e Regjistrimit Civil', 'verified', 'Shtetesia: Kosove.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Trusti pensional i prinderve', 'Trusti Pensional', 'verified', 'Te dhenat financiare familjare jane ruajtur per simulim.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Deklarata e bashkesise familjare', 'Komuna e Gjilanit', 'verified', 'Bashkesia familjare permban babain dhe nenen.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Diploma e gjimnazit', 'Shkolla e mesme teknike', 'verified', 'Diploma e shkollimit te mesem eshte regjistruar.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Shpenzimet per gjate vitit', 'Profili financiar lokal', 'verified', 'Shpenzimet vjetore jane te ruajtura per simulim.'),
((SELECT sp.id FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE u.username='student2'), 'Certifikata, Mirenjohje, Levdata, Diploma', 'Profili akademik lokal', 'verified', 'Certifikate pjesemarrjeje ne projekte rinore.');

INSERT INTO scholarship_templates (category, provider_name, title, description, start_date, end_date, is_active) VALUES
('Burse komunale', 'Komuna e Kamenices', 'Bursa Komunale per Studente - Komuna e Kamenices 2026/2027', 'Afati: 15 dite, nga 20.10.2026 deri me 04.11.2026. Bursa perdor vetem seksione te profilit te studentit dhe nuk kerkon ngarkim dokumentesh.', '2026-10-20', '2026-11-04', 1),
('Burse komunale', 'Komuna e Gjilanit', 'Bursa Komunale per Studente - Komuna e Gjilanit 2026/2027', 'Thirrje komunale 2026/2027 me hapje me 20.10.2026. Rregulli i buxhetit ruhet si kriter pershkrues per renditje sipas mesatares dhe drejtimit.', '2026-10-20', '2026-11-04', 1),
('Burse komunale', 'Komuna e Vitise', 'Bursa Komunale per Studente - Komuna e Vitise 2026/2027', 'Burse komunale per studente te rregullt nga Komuna e Vitise me perparesi sipas drejtimit, notes dhe gjendjes sociale/familjare.', '2026-10-20', '2026-11-04', 1),
('Burse komunale', 'Komuna e Ferizajit', 'Bursa Komunale per Studente - Komuna e Ferizajt 2026/2027', 'Vlera: 50 euro ne muaj per 10 muaj, total 500 euro. Kontrollohet qe studenti nuk ka burse tjeter aktive.', '2026-10-20', '2026-11-04', 1),
('Burse universitare', 'Universiteti Kadri Zeka', 'Bursa Universitare - Universiteti Kadri Zeka 2026', 'Vlera baze: 1000 euro per perfitues. Fondi 3000 euro ndahet proporcionalisht sipas pragjeve te larta te suksesit.', '2026-10-01', '2026-11-30', 1),
('Burse universitare', 'Universiteti i Prishtines', 'Bursa Universitare - Universiteti i Prishtines 2026', 'Aplikimi online ne EKosova+ nga 03.03.2026 deri me 17.03.2026, ora 15:30; diten e pare hapet nga ora 13:00. Jo SEMS, jo printim.', '2026-03-03', '2026-03-17', 1),
('Burse universitare', 'Universiteti Haxhi Zeka', 'Bursa Universitare - Universiteti Haxhi Zeka 2026', 'EKosova+ hapet prej 17.11.2026 deri me 21.11.2026, ora 16:00. Jo SMU, jo printim.', '2026-11-17', '2026-11-21', 1);

INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'city', '=', CASE provider_name
    WHEN 'Komuna e Kamenices' THEN 'Kamenice'
    WHEN 'Komuna e Gjilanit' THEN 'Gjilan'
    WHEN 'Komuna e Vitise' THEN 'Viti'
    ELSE 'Ferizaj'
END, 1, 0, 'Studenti duhet te kete vendbanimin ne komunen perkatese.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'student_active', '=', 'po', 1, 0, 'Studenti duhet te jete aktiv.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'average_grade', '>=', '8.00', 1, 40, 'Nota mesatare minimale dhe piket per sukses.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'receives_social_assistance', '=', 'po', 0, 15, 'Pike shtese per ndihme sociale.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'is_veteran_child', '=', 'po', 0, 10, 'Pike shtese per kategori te luftes.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'is_orphan', '=', 'po', 0, 10, 'Pike shtese per student pa prind.' FROM scholarship_templates WHERE category='Burse komunale';

INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'university', '=', CASE provider_name
    WHEN 'Universiteti Kadri Zeka' THEN 'Universiteti Kadri Zeka'
    WHEN 'Universiteti i Prishtines' THEN 'Universiteti Hasan Prishtina'
    ELSE 'Universiteti Haxhi Zeka'
END, 1, 0, 'Studenti duhet te studioje ne universitetin perkates.' FROM scholarship_templates WHERE category='Burse universitare';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'student_active', '=', 'po', 1, 0, 'Status aktiv i verifikuar nga universiteti.' FROM scholarship_templates WHERE category='Burse universitare';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'full_time', '=', 'po', 1, 0, 'Student i rregullt.' FROM scholarship_templates WHERE category='Burse universitare';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'average_grade', '>=', '8.50', 1, 60, 'Piket kryesore per sukses akademik.' FROM scholarship_templates WHERE category='Burse universitare';
INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description)
SELECT id, 'previous_year_exams_completed', '=', 'po', 0, 15, 'Pike shtese per provime te perfunduara.' FROM scholarship_templates WHERE category='Burse universitare';

INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'ID / Leternjoftimi', 1, 0, 'Identiteti dhe numri personal nga profili.' FROM scholarship_templates;
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Vertetimi i Studentit Aktiv', 1, 0, 'Statusi aktiv i studentit.' FROM scholarship_templates;
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Certifikata e Notave', 1, 0, 'Nota mesatare dhe rezultatet akademike.' FROM scholarship_templates;
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Certifikata e Vendbanimit', 1, 0, 'Komuna dhe adresa e vendbanimit.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Deklarata e Bashkesise Familjare', 0, 1, 'Pike shtese per gjendje familjare.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Vertetimi per Ndihme Sociale', 0, 1, 'Pike/perparesi sociale.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Vertetimi per Kategori te Luftes', 0, 1, 'Pike/perparesi per kategori te luftes.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Certifikata e Vdekjes se Prinderve', 0, 1, 'Opsionale per pike shtese.' FROM scholarship_templates WHERE category='Burse komunale';
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Deshmi per Drejtime Deficitare', 0, 1, 'Pike shtese nese programi eshte deficitar.' FROM scholarship_templates WHERE category='Burse universitare';
INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description)
SELECT id, 'Vertetimi per Nevoja te Vecanta', 0, 1, 'Opsionale per perparesi sipas thirrjes.' FROM scholarship_templates WHERE category='Burse universitare';

INSERT INTO scholarships (provider_id, title, description, amount, deadline, min_grade, required_university, required_city, required_social_status, requires_veteran_child, requires_orphan, requires_social_assistance, status) VALUES
((SELECT id FROM users WHERE username='Geagle'), 'Bursa Golden Eagle per Studente te Dalluar', 'Burse per studente me sukses te larte akademik.', 600.00, '2026-12-15', 8.50, NULL, NULL, NULL, 0, 0, 0, 'active'),
((SELECT id FROM users WHERE username='Vildana'), 'Bursa VILDANA per Vajza ne STEM', 'Mbështetje per studente ne fusha teknike dhe shkencore.', 800.00, '2026-11-30', 8.00, NULL, NULL, NULL, 0, 0, 0, 'active'),
((SELECT id FROM users WHERE username='Toka'), 'Bursa TOKA per Angazhim Komunitar', 'Per studente me angazhim social dhe komunitar.', 450.00, '2026-10-20', 7.50, NULL, NULL, 'Ndihme sociale', 0, 0, 1, 'active'),
((SELECT id FROM users WHERE username='UKZ'), 'Bursa UKZ per Studentet e Kamenices', 'Burse e dedikuar per studentet e Universitetit Kadri Zeka nga Kamenica.', 500.00, '2026-09-30', 8.00, 'Universiteti Kadri Zeka', 'Kamenice', NULL, 0, 0, 0, 'active'),
((SELECT id FROM users WHERE username='KK06'), 'Bursa Komunale per Femije te Veteraneve', 'Simulim i burses komunale me verifikim automatik te statusit familjar.', 700.00, '2026-12-01', 7.00, NULL, 'Kamenice', 'Femije veterani', 1, 0, 0, 'active');

INSERT INTO applications (student_id, scholarship_id, applied_at, status, points_total, result_message, verification_json) VALUES
((SELECT id FROM users WHERE username='student1'), (SELECT id FROM scholarships WHERE title='Bursa UKZ per Studentet e Kamenices'), NOW(), 'fituar', 0, 'Urime! Ju keni fituar bursën.', JSON_ARRAY(JSON_OBJECT('name','Statusi studentor','passed',true,'details','I verifikuar nga universiteti lokal','institution','Universiteti'))),
((SELECT id FROM users WHERE username='student2'), (SELECT id FROM scholarships WHERE title='Bursa Komunale per Femije te Veteraneve'), NOW(), 'fituar', 10, 'Urime! Ju keni fituar bursën.', JSON_ARRAY(JSON_OBJECT('name','Femije veterani','passed',true,'details','Kategori e verifikuar nga profili','institution','Qendra per Pune Sociale / Regjistrat Civil')));

INSERT INTO complaints (application_id, student_id, scholarship_category, provider_name, message, reason, status) VALUES
(NULL, (SELECT id FROM users WHERE username='student2'), 'Bursë komunale', 'Komuna e Kamenicës', 'Mendoj se një bursë komunale duhet të shfaqet në dashboard.', 'Profili im ka vendbanimin dhe statusin studentor të plotësuar.', 'pending');
