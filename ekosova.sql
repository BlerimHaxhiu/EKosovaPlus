CREATE DATABASE IF NOT EXISTS ekosova_plus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ekosova_plus;

DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS scholarships;
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

CREATE TABLE scholarships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    deadline DATE NOT NULL,
    min_grade DECIMAL(4,2) NULL,
    required_university VARCHAR(160) NULL,
    required_city VARCHAR(80) NULL,
    required_social_status VARCHAR(80) NULL,
    requires_veteran_child TINYINT(1) NOT NULL DEFAULT 0,
    requires_orphan TINYINT(1) NOT NULL DEFAULT 0,
    requires_social_assistance TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    verification_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_application (student_id, scholarship_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
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

INSERT INTO scholarships (provider_id, title, description, amount, deadline, min_grade, required_university, required_city, required_social_status, requires_veteran_child, requires_orphan, requires_social_assistance, status) VALUES
((SELECT id FROM users WHERE username='Geagle'), 'Bursa Golden Eagle per Studente te Dalluar', 'Burse per studente me sukses te larte akademik.', 600.00, '2026-12-15', 8.50, NULL, NULL, NULL, 0, 0, 0, 'active'),
((SELECT id FROM users WHERE username='Vildana'), 'Bursa VILDANA per Vajza ne STEM', 'Mbështetje per studente ne fusha teknike dhe shkencore.', 800.00, '2026-11-30', 8.00, NULL, NULL, NULL, 0, 0, 0, 'active'),
((SELECT id FROM users WHERE username='Toka'), 'Bursa TOKA per Angazhim Komunitar', 'Per studente me angazhim social dhe komunitar.', 450.00, '2026-10-20', 7.50, NULL, NULL, 'Ndihme sociale', 0, 0, 1, 'active'),
((SELECT id FROM users WHERE username='UKZ'), 'Bursa UKZ per Studentet e Kamenices', 'Burse e dedikuar per studentet e Universitetit Kadri Zeka nga Kamenica.', 500.00, '2026-09-30', 8.00, 'Universiteti Kadri Zeka', 'Kamenice', NULL, 0, 0, 0, 'active'),
((SELECT id FROM users WHERE username='KK06'), 'Bursa Komunale per Femije te Veteraneve', 'Simulim i burses komunale me verifikim automatik te statusit familjar.', 700.00, '2026-12-01', 7.00, NULL, 'Kamenice', 'Femije veterani', 1, 0, 0, 'active');

INSERT INTO applications (student_id, scholarship_id, status, verification_json) VALUES
((SELECT id FROM users WHERE username='student1'), (SELECT id FROM scholarships WHERE title='Bursa UKZ per Studentet e Kamenices'), 'approved', JSON_ARRAY(JSON_OBJECT('name','Statusi studentor','passed',true,'details','I verifikuar nga universiteti lokal','institution','Universiteti'))),
((SELECT id FROM users WHERE username='student2'), (SELECT id FROM scholarships WHERE title='Bursa Komunale per Femije te Veteraneve'), 'rejected', JSON_ARRAY(JSON_OBJECT('name','Femije veterani','passed',false,'details','Nuk rezulton ne databaze','institution','Qendra per Pune Sociale / Regjistrat Civil')));

INSERT INTO complaints (application_id, student_id, message, status) VALUES
((SELECT a.id FROM applications a JOIN users u ON u.id=a.student_id WHERE u.username='student2' LIMIT 1), (SELECT id FROM users WHERE username='student2'), 'Mendoj se statusi social nuk eshte lexuar sakte nga databaza simuluese.', 'pending');
