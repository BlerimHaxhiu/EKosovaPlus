<?php
declare(strict_types=1);

$sessionPath = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
session_save_path($sessionPath);
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/utils/helpers.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/services/VerificationService.php';

$page = $_GET['page'] ?? 'home';
$action = $_POST['action'] ?? null;

if ($action) {
    verify_csrf();
    handle_action($action);
}

render_layout($page);

function handle_action(string $action): void
{
    match ($action) {
        'login' => action_login(),
        'register' => action_register(),
        'logout' => action_logout(),
        'save_scholarship' => action_save_scholarship(),
        'delete_scholarship' => action_delete_scholarship(),
        'apply' => action_apply(),
        'complaint' => action_complaint(),
        'update_profile' => action_update_profile(),
        'admin_save_user' => action_admin_save_user(),
        'admin_delete_user' => action_admin_delete_user(),
        'admin_update_complaint' => action_admin_update_complaint(),
        default => redirect('home'),
    };
}

function action_login(): void
{
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, name, username, email, role, provider_type, password_hash FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash('Te dhenat e kyqjes nuk jane te sakta.', 'error');
        redirect('login');
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    flash('Mire se erdhet ne EKosova+.');
    redirect('home');
}

function action_register(): void
{
    $role = $_POST['role'] === 'provider' ? 'provider' : 'student';
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $providerType = $role === 'provider' ? trim($_POST['provider_type'] ?? 'Ofrues i Pavarur') : null;

    if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        flash('Plotesoni te dhenat kryesore. Fjalekalimi duhet te kete se paku 6 karaktere.', 'error');
        redirect('register');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, username, email, password_hash, role, provider_type) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $providerType]);
        $userId = (int) $pdo->lastInsertId();

        if ($role === 'student') {
            $stmt = $pdo->prepare('INSERT INTO student_profiles (user_id, personal_number, university, city, average_grade, social_status, bank_name, is_veteran_child, is_orphan, receives_social_assistance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $userId,
                trim($_POST['personal_number'] ?? ''),
                trim($_POST['university'] ?? 'Universiteti Kadri Zeka'),
                trim($_POST['city'] ?? 'Kamenice'),
                (float) ($_POST['average_grade'] ?? 8.5),
                trim($_POST['social_status'] ?? 'Standard'),
                trim($_POST['bank_name'] ?? 'Banka Ekonomike'),
                isset($_POST['is_veteran_child']) ? 1 : 0,
                isset($_POST['is_orphan']) ? 1 : 0,
                isset($_POST['receives_social_assistance']) ? 1 : 0,
            ]);
        }

        $pdo->commit();
        flash('Regjistrimi u krye me sukses. Tani mund te kyqeni.');
        redirect('login');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('Username ose email ekziston tashme.', 'error');
        redirect('register');
    }
}

function action_logout(): void
{
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php?page=home');
    exit;
}

function action_save_scholarship(): void
{
    require_role(['provider']);
    $id = (int) ($_POST['id'] ?? 0);
    $data = [
        trim($_POST['title'] ?? ''),
        trim($_POST['description'] ?? ''),
        (float) ($_POST['amount'] ?? 0),
        trim($_POST['deadline'] ?? ''),
        $_POST['min_grade'] !== '' ? (float) $_POST['min_grade'] : null,
        $_POST['required_university'] !== '' ? trim($_POST['required_university']) : null,
        $_POST['required_city'] !== '' ? trim($_POST['required_city']) : null,
        $_POST['required_social_status'] !== '' ? trim($_POST['required_social_status']) : null,
        isset($_POST['requires_veteran_child']) ? 1 : 0,
        isset($_POST['requires_orphan']) ? 1 : 0,
        isset($_POST['requires_social_assistance']) ? 1 : 0,
        $_POST['status'] === 'inactive' ? 'inactive' : 'active',
    ];

    if ($data[0] === '' || $data[2] <= 0 || $data[3] === '') {
        flash('Titulli, shuma dhe afati jane te detyrueshme.', 'error');
        redirect('provider');
    }

    if ($id > 0) {
        $data[] = $id;
        $data[] = current_user()['id'];
        $stmt = db()->prepare('UPDATE scholarships SET title=?, description=?, amount=?, deadline=?, min_grade=?, required_university=?, required_city=?, required_social_status=?, requires_veteran_child=?, requires_orphan=?, requires_social_assistance=?, status=? WHERE id=? AND provider_id=?');
        $stmt->execute($data);
        flash('Bursa u perditesua.');
    } else {
        array_unshift($data, current_user()['id']);
        $stmt = db()->prepare('INSERT INTO scholarships (provider_id, title, description, amount, deadline, min_grade, required_university, required_city, required_social_status, requires_veteran_child, requires_orphan, requires_social_assistance, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute($data);
        flash('Bursa u krijua.');
    }

    redirect('provider');
}

function action_delete_scholarship(): void
{
    require_role(['provider', 'admin']);
    $id = (int) ($_POST['id'] ?? 0);

    if (current_user()['role'] === 'provider') {
        $stmt = db()->prepare('DELETE FROM scholarships WHERE id = ? AND provider_id = ?');
        $stmt->execute([$id, current_user()['id']]);
        redirect('provider');
    }

    $stmt = db()->prepare('DELETE FROM scholarships WHERE id = ?');
    $stmt->execute([$id]);
    redirect('admin');
}

function action_apply(): void
{
    require_role(['student']);
    $scholarshipId = (int) ($_POST['scholarship_id'] ?? 0);

    $stmt = db()->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
    $stmt->execute([current_user()['id']]);
    $student = $stmt->fetch();

    $stmt = db()->prepare('SELECT * FROM scholarships WHERE id = ? AND status = "active"');
    $stmt->execute([$scholarshipId]);
    $scholarship = $stmt->fetch();

    if (!$student || !$scholarship) {
        flash('Bursa ose profili studentor nuk u gjet.', 'error');
        redirect('scholarships');
    }

    $result = VerificationService::verify($student, $scholarship);
    $status = $result['status'];
    $verificationJson = json_encode($result['checks'], JSON_UNESCAPED_UNICODE);

    $stmt = db()->prepare('SELECT id FROM applications WHERE student_id = ? AND scholarship_id = ?');
    $stmt->execute([current_user()['id'], $scholarshipId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = db()->prepare('UPDATE applications SET status = ?, verification_json = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $verificationJson, $existing['id']]);
    } else {
        $stmt = db()->prepare('INSERT INTO applications (student_id, scholarship_id, status, verification_json) VALUES (?, ?, ?, ?)');
        $stmt->execute([current_user()['id'], $scholarshipId, $status, $verificationJson]);
    }

    flash($status === 'approved' ? 'Aplikimi u aprovua automatikisht.' : 'Aplikimi u refuzua nga verifikimi automatik.', $status === 'approved' ? 'success' : 'error');
    redirect('dashboard');
}

function action_complaint(): void
{
    require_role(['student']);
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($message === '') {
        flash('Shkruani arsyen e ankeses.', 'error');
        redirect('complaint&application_id=' . $applicationId);
    }

    $stmt = db()->prepare('INSERT INTO complaints (application_id, student_id, message, status) VALUES (?, ?, ?, "pending")');
    $stmt->execute([$applicationId, current_user()['id'], $message]);
    flash('Ankesa u dergua te administratori.');
    redirect('dashboard');
}

function action_update_profile(): void
{
    require_role(['student']);

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $averageGrade = (float) ($_POST['average_grade'] ?? 0);
    $annualCirculation = max(0, (float) ($_POST['annual_circulation'] ?? 0));

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Emri, mbiemri dhe email adresa valide jane te detyrueshme.', 'error');
        redirect('profile&edit=1');
    }

    if ($averageGrade < 6 || $averageGrade > 10) {
        flash('Nota mesatare duhet te jete nga 6 deri ne 10.', 'error');
        redirect('profile&edit=1');
    }

    $gender = allowed_value($_POST['gender'] ?? '', ['Mashkull', 'Femer', 'Tjeter'], 'Tjeter');
    $employmentStatus = allowed_value($_POST['employment_status'] ?? '', ['I punesuar', 'I papune'], 'I papune');
    $academicSystem = allowed_value($_POST['academic_system'] ?? '', ['SEMS', 'SMU'], 'SEMS');
    $socialStatus = allowed_value($_POST['social_status'] ?? '', ['Standard', 'Ndihme sociale', 'Jetim', 'Femije veterani'], 'Standard');

    $birthDate = trim($_POST['birth_date'] ?? '');
    $birthDate = $birthDate !== '' ? $birthDate : null;

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $fullName = $firstName . ' ' . $lastName;
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$fullName, $email, current_user()['id']]);

        $stmt = $pdo->prepare(
            'UPDATE student_profiles SET
                first_name = ?, last_name = ?, birth_date = ?, birth_place = ?, residence = ?, gender = ?,
                previous_education = ?, annual_circulation = ?, employment_status = ?, university = ?, city = ?,
                average_grade = ?, social_status = ?, academic_system = ?, student_active = ?, transcript_summary = ?,
                bank_name = ?, bank_account_holder = ?, bank_account_number = ?, bank_iban = ?, bank_branch = ?,
                is_veteran_child = ?, is_orphan = ?, receives_social_assistance = ?
            WHERE user_id = ?'
        );

        $stmt->execute([
            $firstName,
            $lastName,
            $birthDate,
            trim($_POST['birth_place'] ?? ''),
            trim($_POST['residence'] ?? ''),
            $gender,
            trim($_POST['previous_education'] ?? ''),
            $annualCirculation,
            $employmentStatus,
            trim($_POST['university'] ?? ''),
            trim($_POST['city'] ?? ''),
            $averageGrade,
            $socialStatus,
            $academicSystem,
            isset($_POST['student_active']) ? 1 : 0,
            trim($_POST['transcript_summary'] ?? ''),
            trim($_POST['bank_name'] ?? ''),
            trim($_POST['bank_account_holder'] ?? ''),
            trim($_POST['bank_account_number'] ?? ''),
            trim($_POST['bank_iban'] ?? ''),
            trim($_POST['bank_branch'] ?? ''),
            isset($_POST['is_veteran_child']) ? 1 : 0,
            isset($_POST['is_orphan']) ? 1 : 0,
            isset($_POST['receives_social_assistance']) ? 1 : 0,
            current_user()['id'],
        ]);

        $pdo->commit();
        refresh_session_user((int) current_user()['id']);
        flash('Te dhenat personale u perditesuan me sukses.');
        redirect('profile');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('Te dhenat nuk u ruajten. Kontrolloni email-in ose provoni perseri.', 'error');
        redirect('profile&edit=1');
    }
}

function action_admin_save_user(): void
{
    require_role(['admin']);
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['student', 'provider', 'admin'], true) ? $_POST['role'] : 'student';
    $providerType = $role === 'provider' ? trim($_POST['provider_type'] ?? 'Ofrues i Pavarur') : null;

    if ($id > 0) {
        $stmt = db()->prepare('UPDATE users SET name=?, username=?, email=?, role=?, provider_type=? WHERE id=?');
        $stmt->execute([$name, $username, $email, $role, $providerType, $id]);
    } else {
        $password = $_POST['password'] ?: '123456';
        $stmt = db()->prepare('INSERT INTO users (name, username, email, password_hash, role, provider_type) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $providerType]);
    }

    flash('Perdoruesi u ruajt.');
    redirect('admin');
}

function action_admin_delete_user(): void
{
    require_role(['admin']);
    $id = (int) ($_POST['id'] ?? 0);
    if ($id !== (int) current_user()['id']) {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
    redirect('admin');
}

function action_admin_update_complaint(): void
{
    require_role(['admin']);
    $status = in_array($_POST['status'] ?? '', ['pending', 'reviewing', 'accepted', 'rejected'], true) ? $_POST['status'] : 'pending';
    $stmt = db()->prepare('UPDATE complaints SET status = ? WHERE id = ?');
    $stmt->execute([$status, (int) $_POST['id']]);
    redirect('admin');
}

function render_layout(string $page): void
{
    $publicPages = ['home', 'login', 'register'];
    if (!in_array($page, $publicPages, true)) {
        require_login();
    }

    $flash = flash();
    require __DIR__ . '/../src/pages/layout_top.php';

    match ($page) {
        'login' => page_login(),
        'register' => page_register(),
        'dashboard' => page_dashboard(),
        'services' => page_services(),
        'education' => page_education(),
        'scholarships' => page_scholarships(),
        'profile' => page_profile(),
        'provider' => page_provider(),
        'admin' => page_admin(),
        'complaint' => page_complaint(),
        default => page_home(),
    };

    require __DIR__ . '/../src/pages/layout_bottom.php';
}

function page_home(): void
{
    ?>
    <section class="portal-home">
        <div class="portal-hero">
            <div>
                <h1>Platforma e shërbimeve online</h1>
                <p>eKosova është platformë shtetërore ku shërbimet publike që gjenden në zyrat dhe sportelet fizike të institucioneve ofrohen në mënyrë elektronike.</p>
                <?php if (!current_user()): ?>
                    <div class="portal-auth-actions">
                        <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=register">Regjistrohu</a>
                        <a class="btn" href="<?= BASE_URL ?>/index.php?page=login">Hyr</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="portal-tools">
                <label class="portal-search" aria-label="Kërko shërbimin">
                    <input type="search" placeholder="Kërko shërbimin">
                    <span>⌕</span>
                </label>
                <a class="video-link placeholder" href="<?= BASE_URL ?>/index.php?page=home" data-placeholder="Video udhëzuesit janë placeholder në këtë prototip.">Shiko video udhëzuesit <span>▶</span></a>
            </div>
        </div>

        <div class="notice portal-warning">
            <span class="warning-icon">!</span>
            <div>
                <strong>Vëmendje</strong>
                <p>Ju lutem të keni parasysh që platforma eKosova mund të hapet vetëm përmes adresës zyrtare https://ekosova.rks-gov.net dhe https://rks-gov.net.</p>
                <p>Çdo adresë, vegëz apo URL tjetër që nuk përfundon me rks-gov.net nuk i përket platformës eKosova dhe si e tillë nuk janë shërbime që ofrohen nga platforma shtetërore.</p>
            </div>
        </div>

        <div class="portal-stats">
            <?php foreach ([
                ['Familja', '991.9K', 'family', '👪', 'home'],
                ['Arsimi', '849.4K', 'education', '▰', 'education'],
                ['Kontributet', '217.4K', 'contrib', '◔', 'home'],
                ['Grantet', '424.9K', 'grants', '▣', 'home'],
                ['Komunalitet', '389.7K', 'municipal', '▤', 'home'],
                ['Vizita në platformë', '1.5B', 'visits', '●', 'home'],
            ] as $cat): ?>
                <a class="stat-card <?= e($cat[2]) ?> <?= $cat[4] === 'home' ? 'placeholder' : '' ?>" href="<?= BASE_URL ?>/index.php?page=<?= e($cat[4]) ?>" data-placeholder="Ky shërbim është placeholder në këtë prototip.">
                    <span class="stat-icon"><?= e($cat[3]) ?></span>
                    <strong><?= e($cat[1]) ?></strong>
                    <small><?= $cat[0] === 'Vizita në platformë' ? 'Vizita në platformë' : 'Shfrytëzime të shërbimit "' . e($cat[0]) . '"' ?></small>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="portal-skyline" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>

        <div class="steps portal-steps">
            <article><b>1</b><h3>Krijo llogarinë tënde</h3><p>Krijoni llogarinë tuaj duke klikuar mbi "Regjistrohu" dhe duke plotësuar fushat që kërkohen. Pas krijimit të llogarisë mund të keni qasje në shërbimet elektronike.</p></article>
            <article><b>2</b><h3>Zgjedh shërbimin</h3><p>Pasi të jeni identifikuar, zgjidhni shërbimin që ju nevojitet përmes rrjedhës Kryesore, Shërbime, Arsimi dhe Bursat.</p></article>
            <article><b>3</b><h3>Prano shërbimin</h3><p>Pasi të zgjidhni shërbimin, plotësoni të dhënat e nevojshme dhe pranoni rezultatin në panelin tuaj.</p></article>
        </div>
    </section>
    <?php
}

function page_home_legacy(): void
{
    ?>
    <section class="hero">
        <h1>Platforma e sherbimeve online</h1>
        <p>EKosova+ eshte prototip akademik per simulimin e nje moduli te ri brenda EKosova: aplikimi automatik per bursa studentore.</p>
        <div class="hero-actions">
            <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=register">Regjistrohu</a>
            <a class="btn" href="<?= BASE_URL ?>/index.php?page=login">Hyr</a>
        </div>
        <div class="notice">Ky projekt eshte vetem simulim universitar dhe nuk eshte platforme reale shteterore.</div>
        <div class="category-row">
            <?php foreach ([
                ['Automjetet', '2.9M', 'green'],
                ['Policia', '2.9M', 'teal'],
                ['Gjendja civile', '2.7M', 'orange'],
                ['Familja', '991.9K', 'purple'],
                ['Arsimi', '849.4K', 'emerald'],
                ['Kontribute', '217.4K', 'slate'],
            ] as $cat): ?>
                <a class="stat-card <?= e($cat[2]) ?>" href="<?= BASE_URL ?>/index.php?page=services">
                    <span class="icon">⌂</span>
                    <strong><?= e($cat[1]) ?></strong>
                    <small>Shfrytezime te sherbimit "<?= e($cat[0]) ?>"</small>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="steps">
            <article><b>1</b><h3>Krijo llogarine tende</h3><p>Regjistrohu si student ose ofrues i burses.</p></article>
            <article><b>2</b><h3>Zgjedh sherbimin</h3><p>Shko te Sherbime, Arsimi, Bursat.</p></article>
            <article><b>3</b><h3>Prano rezultatin</h3><p>Sistemi simulon verifikimin dhe vendos automatikisht.</p></article>
        </div>
    </section>
    <?php
}

function page_login(): void
{
    ?>
    <section class="auth-panel narrow">
        <h1>Kycu ne llogarine tuaj</h1>
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="login">
            <label>Username</label>
            <input name="username" required>
            <label>Fjalekalimi</label>
            <input name="password" type="password" required>
            <label class="check"><input type="checkbox"> Me mbaj ne mend</label>
            <button class="btn full">Hyr</button>
            <a class="btn btn-outline full" href="<?= BASE_URL ?>/index.php?page=register">Regjistrohu</a>
        </form>
    </section>
    <?php
}

function page_register(): void
{
    ?>
    <section class="auth-panel">
        <h1>Mire se erdhet!</h1>
        <p class="center-muted">Per te filluar procesin e regjistrimit plotesoni te dhenat ne vazhdim.</p>
        <form method="post" class="form grid-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="register">
            <label>Lloji i regjistrimit
                <select name="role" id="roleSelect">
                    <option value="student">Regjistrohu si Perfitues - Student</option>
                    <option value="provider">Regjistrohu si Ofrues</option>
                </select>
            </label>
            <label>Emri / Organizata<input name="name" required></label>
            <label>Username<input name="username" required></label>
            <label>Email<input name="email" type="email" required></label>
            <label>Fjalekalimi<input name="password" type="password" required></label>
            <label class="provider-field">Tipi i ofruesit
                <select name="provider_type">
                    <option>OJQ</option>
                    <option>Biznes</option>
                    <option>Institucion Arsimor</option>
                    <option>Drejtori Komunale e Arsimit</option>
                    <option>Ofrues i Pavarur</option>
                </select>
            </label>
            <div class="student-fields form-subgrid">
                <label>Numri personal<input name="personal_number"></label>
                <label>Universiteti
                    <select name="university">
                        <option>Universiteti Kadri Zeka</option>
                        <option>Universiteti Hasan Prishtina</option>
                        <option>Universiteti Haxhi Zeka</option>
                    </select>
                </label>
                <label>Qyteti
                    <select name="city">
                        <option>Kamenice</option><option>Gjilan</option><option>Viti</option><option>Ferizaj</option>
                    </select>
                </label>
                <label>Nota mesatare<input name="average_grade" type="number" min="6" max="10" step="0.01" value="8.70"></label>
                <label>Statusi social
                    <select name="social_status">
                        <option>Standard</option><option>Ndihme sociale</option><option>Jetim</option><option>Femije veterani</option>
                    </select>
                </label>
                <label>Banka<input name="bank_name" value="Banka Ekonomike"></label>
                <label class="check"><input type="checkbox" name="is_veteran_child"> Femije veterani</label>
                <label class="check"><input type="checkbox" name="is_orphan"> Jetim</label>
                <label class="check"><input type="checkbox" name="receives_social_assistance"> Pranon ndihme sociale</label>
            </div>
            <button class="btn">Vazhdo</button>
        </form>
    </section>
    <?php
}

function page_dashboard(): void
{
    $role = current_user()['role'];
    if ($role === 'provider') {
        page_provider();
        return;
    }
    if ($role === 'admin') {
        page_admin();
        return;
    }

    $stmt = db()->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
    $stmt->execute([current_user()['id']]);
    $profile = $stmt->fetch();

    $stmt = db()->prepare('SELECT a.*, s.title, s.amount, u.name provider_name FROM applications a JOIN scholarships s ON s.id=a.scholarship_id JOIN users u ON u.id=s.provider_id WHERE a.student_id=? ORDER BY a.created_at DESC');
    $stmt->execute([current_user()['id']]);
    $applications = $stmt->fetchAll();
    ?>
    <section class="page-head">
        <h1>Paneli i studentit</h1>
        <a class="btn" href="<?= BASE_URL ?>/index.php?page=scholarships">Shiko bursat aktive</a>
    </section>
    <section class="dashboard-grid">
        <article class="panel">
            <h2>Te dhenat e verifikuara</h2>
            <dl class="info-list">
                <dt>Statusi studentor</dt><dd>I verifikuar</dd>
                <dt>Universiteti</dt><dd><?= e($profile['university'] ?? '-') ?></dd>
                <dt>Qyteti</dt><dd><?= e($profile['city'] ?? '-') ?></dd>
                <dt>Nota mesatare</dt><dd><?= e($profile['average_grade'] ?? '-') ?></dd>
                <dt>Statusi social</dt><dd><?= e($profile['social_status'] ?? '-') ?></dd>
                <dt>Banka</dt><dd><?= e($profile['bank_name'] ?? '-') ?></dd>
            </dl>
        </article>
        <article class="panel">
            <h2>Statuset sociale</h2>
            <div class="chips">
                <span class="<?= !empty($profile['is_veteran_child']) ? 'ok' : 'muted' ?>">Femije veterani</span>
                <span class="<?= !empty($profile['is_orphan']) ? 'ok' : 'muted' ?>">Jetim</span>
                <span class="<?= !empty($profile['receives_social_assistance']) ? 'ok' : 'muted' ?>">Ndihme sociale</span>
            </div>
        </article>
    </section>
    <section class="panel">
        <h2>Aplikimet e mia</h2>
        <?php if (!$applications): ?>
            <p class="muted-text">Ende nuk keni aplikuar per burse.</p>
        <?php endif; ?>
        <?php foreach ($applications as $app): ?>
            <?php $checks = json_decode($app['verification_json'] ?: '[]', true); ?>
            <div class="application-card">
                <div>
                    <h3><?= e($app['title']) ?></h3>
                    <p><?= e($app['provider_name']) ?> · <?= e(number_format((float) $app['amount'], 2)) ?> EUR</p>
                </div>
                <span class="status <?= e($app['status']) ?>"><?= status_label($app['status']) ?></span>
                <div class="verification-list">
                    <?php foreach ($checks as $check): ?>
                        <div class="<?= $check['passed'] ? 'pass' : 'fail' ?>">
                            <strong><?= $check['passed'] ? '✓' : '!' ?> <?= e($check['name']) ?></strong>
                            <small><?= e($check['institution']) ?>: <?= e($check['details']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($app['status'] === 'rejected'): ?>
                    <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=complaint&application_id=<?= (int) $app['id'] ?>">Ankohu per Gabim</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php
}

function page_profile(): void
{
    require_login();
    $user = current_user();

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $account = $stmt->fetch();

    if ($user['role'] !== 'student') {
        ?>
        <section class="page-head"><h1>Te dhenat e perdoruesit</h1></section>
        <section class="panel profile-layout">
            <div class="profile-section">
                <h2>Llogaria</h2>
                <div class="profile-grid">
                    <?php profile_field('Emri / Organizata', $account['name'] ?? '-'); ?>
                    <?php profile_field('Username', $account['username'] ?? '-'); ?>
                    <?php profile_field('Email', $account['email'] ?? '-'); ?>
                    <?php profile_field('Roli', $account['role'] ?? '-'); ?>
                    <?php profile_field('Tipi i ofruesit', $account['provider_type'] ?? '-'); ?>
                </div>
            </div>
        </section>
        <?php
        return;
    }

    $stmt = db()->prepare('SELECT sp.*, u.name, u.username, u.email FROM student_profiles sp JOIN users u ON u.id = sp.user_id WHERE sp.user_id = ?');
    $stmt->execute([$user['id']]);
    $profile = $stmt->fetch() ?: [];

    $family = [];
    $documents = [];

    try {
        $stmt = db()->prepare('SELECT * FROM student_family_members WHERE student_profile_id = ? ORDER BY FIELD(relation, "Babai", "Nena", "Bashkeshorti/ja", "Femija"), id');
        $stmt->execute([(int) ($profile['id'] ?? 0)]);
        $family = $stmt->fetchAll();
    } catch (Throwable $e) {
        $family = [];
    }

    try {
        $stmt = db()->prepare('SELECT * FROM student_documents WHERE student_profile_id = ? ORDER BY id');
        $stmt->execute([(int) ($profile['id'] ?? 0)]);
        $documents = $stmt->fetchAll();
    } catch (Throwable $e) {
        $documents = [];
    }

    $nameParts = explode(' ', trim($profile['name'] ?? ''), 2);
    $firstName = $profile['first_name'] ?? ($nameParts[0] ?? '-');
    $lastName = $profile['last_name'] ?? ($nameParts[1] ?? '-');

    if (($_GET['edit'] ?? '') === '1') {
        render_student_profile_form($profile, $firstName, $lastName);
        return;
    }
    ?>
    <section class="page-head">
        <div>
            <h1>Te dhenat e perdoruesit</h1>
            <p class="muted-text">Profili i studentit ruan te dhenat qe perdoren gjate aplikimit automatik.</p>
        </div>
        <div class="profile-actions">
            <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=profile&edit=1">Ndrysho te dhenat</a>
            <a class="btn" href="<?= BASE_URL ?>/index.php?page=scholarships">Apliko per burse</a>
        </div>
    </section>

    <section class="panel profile-layout">
        <div class="profile-section">
            <h2>Te dhenat personale</h2>
            <div class="profile-grid">
                <?php profile_field('Emri', $firstName); ?>
                <?php profile_field('Mbiemri', $lastName); ?>
                <?php profile_field('Data e lindjes', $profile['birth_date'] ?? '-'); ?>
                <?php profile_field('Vendlindja', $profile['birth_place'] ?? '-'); ?>
                <?php profile_field('Vendbanimi', $profile['residence'] ?? ($profile['city'] ?? '-')); ?>
                <?php profile_field('Gjinia', $profile['gender'] ?? '-'); ?>
                <?php profile_field('Qarkullimet e vitit', money_label($profile['annual_circulation'] ?? null)); ?>
                <?php profile_field('Ndihme sociale', yes_no((int) ($profile['receives_social_assistance'] ?? 0))); ?>
                <?php profile_field('Statusi i punes', $profile['employment_status'] ?? '-'); ?>
                <?php profile_field('Shkollimi i meparshem', $profile['previous_education'] ?? '-', true); ?>
            </div>
        </div>

        <div class="profile-section">
            <h2>Familja e ngushte</h2>
            <div class="family-list">
                <?php if (!$family): ?>
                    <p class="muted-text">Nuk ka te dhena familjare te regjistruara.</p>
                <?php endif; ?>
                <?php foreach ($family as $member): ?>
                    <article class="family-row">
                        <header>
                            <h3><?= e($member['relation']) ?> - <?= e($member['first_name'] . ' ' . $member['last_name']) ?></h3>
                            <div>
                                <?php if (!empty($member['is_war_hero'])): ?><span class="badge warn">Hero lufte</span><?php endif; ?>
                                <?php if (!empty($member['is_veteran'])): ?><span class="badge ok">Veteran</span><?php endif; ?>
                            </div>
                        </header>
                        <div class="mini-grid">
                            <?php profile_field('Data e lindjes', $member['birth_date'] ?? '-'); ?>
                            <?php profile_field('Vendlindja', $member['birth_place'] ?? '-'); ?>
                            <?php profile_field('Vendbanimi', $member['residence'] ?? '-'); ?>
                            <?php profile_field('Gjinia', $member['gender'] ?? '-'); ?>
                            <?php profile_field('Statusi i punes', $member['employment_status'] ?? '-'); ?>
                            <?php profile_field('Ndihme sociale', yes_no((int) ($member['receives_social_assistance'] ?? 0))); ?>
                            <?php profile_field('Qarkullimet e vitit', money_label($member['annual_circulation'] ?? null)); ?>
                            <?php profile_field('Shkollimi i meparshem', $member['previous_education'] ?? '-'); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="profile-section">
            <h2>Student</h2>
            <div class="profile-grid">
                <?php profile_field('Sistemi akademik', $profile['academic_system'] ?? 'SEMS'); ?>
                <?php profile_field('Statusi studentor', !empty($profile['student_active']) ? 'Student aktiv' : 'Jo aktiv'); ?>
                <?php profile_field('Universiteti', $profile['university'] ?? '-'); ?>
                <?php profile_field('Nota mesatare', isset($profile['average_grade']) ? (string) $profile['average_grade'] : '-'); ?>
                <?php profile_field('Certifikata e notave', $profile['transcript_summary'] ?? '-', true); ?>
            </div>
        </div>

        <div class="profile-section">
            <h2>Banka</h2>
            <div class="profile-grid">
                <?php profile_field('Banka', $profile['bank_name'] ?? '-'); ?>
                <?php profile_field('Mbajtesi i xhirollogarise', $profile['bank_account_holder'] ?? ($profile['name'] ?? '-')); ?>
                <?php profile_field('Numri i xhirollogarise', $profile['bank_account_number'] ?? '-'); ?>
                <?php profile_field('IBAN', $profile['bank_iban'] ?? '-'); ?>
                <?php profile_field('Dega', $profile['bank_branch'] ?? '-'); ?>
            </div>
        </div>

        <div class="profile-section">
            <h2>Dokumentet dhe te dhenat e ruajtura</h2>
            <div class="document-list">
                <?php if (!$documents): ?>
                    <p class="muted-text">Nuk ka dokumente te regjistruara.</p>
                <?php endif; ?>
                <?php foreach ($documents as $document): ?>
                    <article class="document-row">
                        <header>
                            <h3><?= e($document['document_name']) ?></h3>
                            <span class="badge ok"><?= e(document_status_label($document['verification_status'])) ?></span>
                        </header>
                        <div class="profile-grid">
                            <?php profile_field('Institucioni burimor', $document['source_institution'] ?? '-'); ?>
                            <?php profile_field('Data e verifikimit', $document['verified_at'] ?? '-'); ?>
                            <?php profile_field('Te dhenat e ruajtura', $document['stored_data_summary'] ?? '-', true); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function page_services(): void
{
    ?>
    <section class="page-head"><h1>Shërbime</h1></section>
    <div class="service-grid">
        <?php foreach (['Familja', 'Gjendja Civile', 'Arsimi', 'Shëndetësia', 'Automjetet', 'Tatimet', 'Policia', 'Kontributet'] as $service): ?>
            <a class="service-tile <?= $service === 'Arsimi' ? '' : 'placeholder' ?>" href="<?= BASE_URL ?>/index.php?page=<?= $service === 'Arsimi' ? 'education' : 'services' ?>" data-placeholder="Ky shërbim është placeholder në këtë prototip.">
                <span>●</span><strong><?= e($service) ?></strong><small><?= $service === 'Arsimi' ? 'Funksionale' : 'Placeholder' ?></small>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}

function page_services_legacy(): void
{
    ?>
    <section class="page-head"><h1>Sherbime</h1></section>
    <div class="service-grid">
        <?php foreach (['Familja', 'Gjendja Civile', 'Arsimi', 'Shendetesia', 'Automjetet', 'Tatimet', 'Policia', 'Kontribute'] as $service): ?>
            <a class="service-tile" href="<?= BASE_URL ?>/index.php?page=<?= $service === 'Arsimi' ? 'education' : 'services' ?>">
                <span>◈</span><strong><?= e($service) ?></strong><small><?= $service === 'Arsimi' ? 'Funksionale' : 'Placeholder' ?></small>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}

function page_education(): void
{
    ?>
    <section class="service-page">
        <div class="page-head"><h1>Arsimi</h1><div class="filters">○ Për qytetarë &nbsp; ○ Për biznese &nbsp; ● Të gjitha shërbimet</div></div>
        <div class="search-box">Kërko <span>⌕</span></div>
        <a class="service-list-item" href="<?= BASE_URL ?>/index.php?page=scholarships">
            <span class="cap">▱</span>
            <strong>Bursat</strong>
            <em>Raporto problem</em>
            <b>›</b>
        </a>
        <a class="service-list-item placeholder" href="<?= BASE_URL ?>/index.php?page=education" data-placeholder="Ky shërbim është placeholder në këtë prototip.">
            <span class="cap">▱</span>
            <strong>Aplikimi për licencë të karrierës në mësimdhënie</strong>
            <em>Raporto problem</em>
            <b>›</b>
        </a>
    </section>
    <?php
}

function page_education_legacy(): void
{
    ?>
    <section class="service-page">
        <div class="page-head"><h1>Arsimi</h1><div class="filters">○ Per qytetare &nbsp; ○ Per biznese &nbsp; ● Te gjitha sherbimet</div></div>
        <div class="search-box">Kerko <span>⌕</span></div>
        <a class="service-list-item" href="<?= BASE_URL ?>/index.php?page=scholarships">
            <span class="cap">▱</span>
            <strong>Aplikimi automatik per burse studentore</strong>
            <em>Raporto problem</em>
            <b>›</b>
        </a>
        <a class="service-list-item placeholder" href="<?= BASE_URL ?>/index.php?page=education">
            <span class="cap">▱</span>
            <strong>Aplikimi per licence te karrieres ne mesimdhenie</strong>
            <em>Raporto problem</em>
            <b>›</b>
        </a>
    </section>
    <?php
}

function page_scholarships(): void
{
    require_role(['student', 'admin']);
    $scholarships = db()->query('SELECT s.*, u.name provider_name FROM scholarships s JOIN users u ON u.id=s.provider_id WHERE s.status="active" ORDER BY s.deadline ASC')->fetchAll();
    ?>
    <section class="page-head"><h1>Bursat aktive</h1></section>
    <div class="scholarship-list">
        <?php foreach ($scholarships as $s): ?>
            <article class="scholarship-card">
                <h2><?= e($s['title']) ?></h2>
                <p><?= e($s['description']) ?></p>
                <div class="meta">
                    <span>Ofruesi: <?= e($s['provider_name']) ?></span>
                    <span>Shuma: <?= e(number_format((float) $s['amount'], 2)) ?> EUR</span>
                    <span>Afati: <?= e($s['deadline']) ?></span>
                </div>
                <div class="criteria">
                    <?= criteria_text($s) ?>
                </div>
                <?php if (current_user()['role'] === 'student'): ?>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="action" value="apply">
                        <input type="hidden" name="scholarship_id" value="<?= (int) $s['id'] ?>">
                        <button class="btn">Apliko</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}

function page_provider(): void
{
    require_role(['provider']);
    $editId = (int) ($_GET['edit'] ?? 0);
    $edit = null;
    if ($editId) {
        $stmt = db()->prepare('SELECT * FROM scholarships WHERE id=? AND provider_id=?');
        $stmt->execute([$editId, current_user()['id']]);
        $edit = $stmt->fetch();
    }

    $stmt = db()->prepare('SELECT * FROM scholarships WHERE provider_id=? ORDER BY created_at DESC');
    $stmt->execute([current_user()['id']]);
    $items = $stmt->fetchAll();
    ?>
    <section class="page-head"><h1>Paneli i ofruesit</h1></section>
    <section class="panel">
        <h2><?= $edit ? 'Modifiko bursen' : 'Krijo burse' ?></h2>
        <form method="post" class="form grid-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="save_scholarship">
            <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
            <label>Titulli<input name="title" value="<?= e($edit['title'] ?? '') ?>" required></label>
            <label>Shuma EUR<input type="number" name="amount" step="0.01" value="<?= e($edit['amount'] ?? '') ?>" required></label>
            <label>Afati<input type="date" name="deadline" value="<?= e($edit['deadline'] ?? '') ?>" required></label>
            <label>Statusi<select name="status"><option value="active" <?= selected('active', $edit['status'] ?? 'active') ?>>Aktive</option><option value="inactive" <?= selected('inactive', $edit['status'] ?? '') ?>>Jo aktive</option></select></label>
            <label class="wide">Pershkrimi<textarea name="description"><?= e($edit['description'] ?? '') ?></textarea></label>
            <label>Nota minimale<input type="number" min="6" max="10" step="0.01" name="min_grade" value="<?= e($edit['min_grade'] ?? '') ?>"></label>
            <label>Universiteti<select name="required_university"><option value="">Cilido</option><option <?= selected('Universiteti Kadri Zeka', $edit['required_university'] ?? null) ?>>Universiteti Kadri Zeka</option><option <?= selected('Universiteti Hasan Prishtina', $edit['required_university'] ?? null) ?>>Universiteti Hasan Prishtina</option><option <?= selected('Universiteti Haxhi Zeka', $edit['required_university'] ?? null) ?>>Universiteti Haxhi Zeka</option></select></label>
            <label>Qyteti<select name="required_city"><option value="">Cilido</option><option <?= selected('Kamenice', $edit['required_city'] ?? null) ?>>Kamenice</option><option <?= selected('Gjilan', $edit['required_city'] ?? null) ?>>Gjilan</option><option <?= selected('Viti', $edit['required_city'] ?? null) ?>>Viti</option><option <?= selected('Ferizaj', $edit['required_city'] ?? null) ?>>Ferizaj</option></select></label>
            <label>Statusi social<select name="required_social_status"><option value="">Cilido</option><option <?= selected('Ndihme sociale', $edit['required_social_status'] ?? null) ?>>Ndihme sociale</option><option <?= selected('Jetim', $edit['required_social_status'] ?? null) ?>>Jetim</option><option <?= selected('Femije veterani', $edit['required_social_status'] ?? null) ?>>Femije veterani</option></select></label>
            <label class="check"><input type="checkbox" name="requires_veteran_child" <?= checked_bool(!empty($edit['requires_veteran_child'])) ?>> Kerko femije veterani</label>
            <label class="check"><input type="checkbox" name="requires_orphan" <?= checked_bool(!empty($edit['requires_orphan'])) ?>> Kerko jetim</label>
            <label class="check"><input type="checkbox" name="requires_social_assistance" <?= checked_bool(!empty($edit['requires_social_assistance'])) ?>> Kerko ndihme sociale</label>
            <button class="btn">Ruaj bursen</button>
        </form>
    </section>
    <section class="panel">
        <h2>Bursat e mia</h2>
        <table><tr><th>Titulli</th><th>Statusi</th><th>Afati</th><th></th></tr>
            <?php foreach ($items as $item): ?>
                <tr><td><?= e($item['title']) ?></td><td><?= e($item['status']) ?></td><td><?= e($item['deadline']) ?></td><td class="actions"><a href="<?= BASE_URL ?>/index.php?page=provider&edit=<?= (int) $item['id'] ?>">Modifiko</a><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_scholarship"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button class="link danger">Fshi</button></form></td></tr>
            <?php endforeach; ?>
        </table>
    </section>
    <?php
}

function page_admin(): void
{
    require_role(['admin']);
    $users = db()->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
    $scholarships = db()->query('SELECT s.*, u.name provider_name FROM scholarships s JOIN users u ON u.id=s.provider_id ORDER BY s.created_at DESC')->fetchAll();
    $applications = db()->query('SELECT a.*, st.name student_name, s.title FROM applications a JOIN users st ON st.id=a.student_id JOIN scholarships s ON s.id=a.scholarship_id ORDER BY a.created_at DESC')->fetchAll();
    $complaints = db()->query('SELECT c.*, u.name student_name, s.title scholarship_title FROM complaints c JOIN users u ON u.id=c.student_id JOIN applications a ON a.id=c.application_id JOIN scholarships s ON s.id=a.scholarship_id ORDER BY c.created_at DESC')->fetchAll();
    ?>
    <section class="page-head"><h1>Paneli i administratorit</h1></section>
    <section class="panel">
        <h2>Shto perdorues</h2>
        <form method="post" class="form inline-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="admin_save_user">
            <input name="name" placeholder="Emri" required><input name="username" placeholder="Username" required><input name="email" type="email" placeholder="Email" required><input name="password" placeholder="Fjalekalimi">
            <select name="role"><option value="student">Student</option><option value="provider">Ofrues</option><option value="admin">Admin</option></select>
            <input name="provider_type" placeholder="Tipi i ofruesit">
            <button class="btn">Shto</button>
        </form>
    </section>
    <section class="panel"><h2>Perdoruesit</h2><table><tr><th>Emri</th><th>Username</th><th>Roli</th><th>Tipi</th><th></th></tr><?php foreach ($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['username']) ?></td><td><?= e($u['role']) ?></td><td><?= e($u['provider_type']) ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="admin_delete_user"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"><button class="link danger">Fshi</button></form></td></tr><?php endforeach; ?></table></section>
    <section class="panel"><h2>Bursat</h2><table><tr><th>Titulli</th><th>Ofruesi</th><th>Statusi</th><th></th></tr><?php foreach ($scholarships as $s): ?><tr><td><?= e($s['title']) ?></td><td><?= e($s['provider_name']) ?></td><td><?= e($s['status']) ?></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_scholarship"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="link danger">Fshi</button></form></td></tr><?php endforeach; ?></table></section>
    <section class="panel"><h2>Aplikimet</h2><table><tr><th>Studenti</th><th>Bursa</th><th>Statusi</th><th>Data</th></tr><?php foreach ($applications as $a): ?><tr><td><?= e($a['student_name']) ?></td><td><?= e($a['title']) ?></td><td><?= status_label($a['status']) ?></td><td><?= e($a['created_at']) ?></td></tr><?php endforeach; ?></table></section>
    <section class="panel"><h2>Ankesat</h2><table><tr><th>Studenti</th><th>Bursa</th><th>Mesazhi</th><th>Statusi</th></tr><?php foreach ($complaints as $c): ?><tr><td><?= e($c['student_name']) ?></td><td><?= e($c['scholarship_title']) ?></td><td><?= e($c['message']) ?></td><td><form method="post" class="mini-form"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="admin_update_complaint"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><select name="status"><option value="pending" <?= selected('pending', $c['status']) ?>>Ne pritje</option><option value="reviewing" <?= selected('reviewing', $c['status']) ?>>Ne shqyrtim</option><option value="accepted" <?= selected('accepted', $c['status']) ?>>E pranuar</option><option value="rejected" <?= selected('rejected', $c['status']) ?>>E refuzuar</option></select><button class="btn small">Ruaj</button></form></td></tr><?php endforeach; ?></table></section>
    <?php
}

function page_complaint(): void
{
    require_role(['student']);
    $applicationId = (int) ($_GET['application_id'] ?? 0);
    ?>
    <section class="auth-panel narrow">
        <h1>Ankohu per Gabim</h1>
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="complaint">
            <input type="hidden" name="application_id" value="<?= $applicationId ?>">
            <label>Pershkruani gabimin e mundshem<textarea name="message" required></textarea></label>
            <button class="btn">Dergo ankesen</button>
        </form>
    </section>
    <?php
}

function status_label(string $status): string
{
    return [
        'pending' => 'Ne verifikim',
        'approved' => 'I aprovuar',
        'rejected' => 'I refuzuar',
    ][$status] ?? $status;
}

function render_student_profile_form(array $profile, string $firstName, string $lastName): void
{
    ?>
    <section class="page-head">
        <div>
            <h1>Ndrysho te dhenat personale</h1>
            <p class="muted-text">Dokumentet e verifikuara mbeten te lidhura me institucionet simuluese.</p>
        </div>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=profile">Kthehu te profili</a>
    </section>

    <section class="panel">
        <form method="post" class="form grid-form profile-edit-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="update_profile">

            <h2 class="wide">Te dhenat personale</h2>
            <label>Emri<input name="first_name" value="<?= e($firstName) ?>" required></label>
            <label>Mbiemri<input name="last_name" value="<?= e($lastName) ?>" required></label>
            <label>Email<input name="email" type="email" value="<?= e($profile['email'] ?? '') ?>" required></label>
            <label>Data e lindjes<input name="birth_date" type="date" value="<?= e($profile['birth_date'] ?? '') ?>"></label>
            <label>Vendlindja<input name="birth_place" value="<?= e($profile['birth_place'] ?? '') ?>"></label>
            <label>Vendbanimi<input name="residence" value="<?= e($profile['residence'] ?? ($profile['city'] ?? '')) ?>"></label>
            <label>Gjinia
                <select name="gender">
                    <option value="Mashkull" <?= selected('Mashkull', $profile['gender'] ?? '') ?>>Mashkull</option>
                    <option value="Femer" <?= selected('Femer', $profile['gender'] ?? '') ?>>Femer</option>
                    <option value="Tjeter" <?= selected('Tjeter', $profile['gender'] ?? 'Tjeter') ?>>Tjeter</option>
                </select>
            </label>
            <label>Qarkullimet e vitit<input name="annual_circulation" type="number" min="0" step="0.01" value="<?= e((string) ($profile['annual_circulation'] ?? '0')) ?>"></label>
            <label>Statusi i punes
                <select name="employment_status">
                    <option value="I papune" <?= selected('I papune', $profile['employment_status'] ?? '') ?>>I papune</option>
                    <option value="I punesuar" <?= selected('I punesuar', $profile['employment_status'] ?? '') ?>>I punesuar</option>
                </select>
            </label>
            <label class="wide">Shkollimi i meparshem<textarea name="previous_education"><?= e($profile['previous_education'] ?? '') ?></textarea></label>

            <h2 class="wide">Student</h2>
            <label>Sistemi akademik
                <select name="academic_system">
                    <option value="SEMS" <?= selected('SEMS', $profile['academic_system'] ?? '') ?>>SEMS</option>
                    <option value="SMU" <?= selected('SMU', $profile['academic_system'] ?? '') ?>>SMU</option>
                </select>
            </label>
            <label>Universiteti
                <select name="university">
                    <option <?= selected('Universiteti Kadri Zeka', $profile['university'] ?? '') ?>>Universiteti Kadri Zeka</option>
                    <option <?= selected('Universiteti Hasan Prishtina', $profile['university'] ?? '') ?>>Universiteti Hasan Prishtina</option>
                    <option <?= selected('Universiteti Haxhi Zeka', $profile['university'] ?? '') ?>>Universiteti Haxhi Zeka</option>
                </select>
            </label>
            <label>Qyteti
                <select name="city">
                    <option <?= selected('Kamenice', $profile['city'] ?? '') ?>>Kamenice</option>
                    <option <?= selected('Gjilan', $profile['city'] ?? '') ?>>Gjilan</option>
                    <option <?= selected('Viti', $profile['city'] ?? '') ?>>Viti</option>
                    <option <?= selected('Ferizaj', $profile['city'] ?? '') ?>>Ferizaj</option>
                </select>
            </label>
            <label>Nota mesatare<input name="average_grade" type="number" min="6" max="10" step="0.01" value="<?= e((string) ($profile['average_grade'] ?? '8.00')) ?>" required></label>
            <label>Statusi social
                <select name="social_status">
                    <option value="Standard" <?= selected('Standard', $profile['social_status'] ?? '') ?>>Standard</option>
                    <option value="Ndihme sociale" <?= selected('Ndihme sociale', $profile['social_status'] ?? '') ?>>Ndihme sociale</option>
                    <option value="Jetim" <?= selected('Jetim', $profile['social_status'] ?? '') ?>>Jetim</option>
                    <option value="Femije veterani" <?= selected('Femije veterani', $profile['social_status'] ?? '') ?>>Femije veterani</option>
                </select>
            </label>
            <label class="check"><input type="checkbox" name="student_active" <?= checked_bool(!empty($profile['student_active'])) ?>> Student aktiv</label>
            <label class="check"><input type="checkbox" name="is_veteran_child" <?= checked_bool(!empty($profile['is_veteran_child'])) ?>> Femije veterani</label>
            <label class="check"><input type="checkbox" name="is_orphan" <?= checked_bool(!empty($profile['is_orphan'])) ?>> Jetim</label>
            <label class="check"><input type="checkbox" name="receives_social_assistance" <?= checked_bool(!empty($profile['receives_social_assistance'])) ?>> Pranon ndihme sociale</label>
            <label class="wide">Permbledhja e transkriptes<textarea name="transcript_summary"><?= e($profile['transcript_summary'] ?? '') ?></textarea></label>

            <h2 class="wide">Banka</h2>
            <label>Banka<input name="bank_name" value="<?= e($profile['bank_name'] ?? '') ?>"></label>
            <label>Mbajtesi i xhirollogarise<input name="bank_account_holder" value="<?= e($profile['bank_account_holder'] ?? '') ?>"></label>
            <label>Numri i xhirollogarise<input name="bank_account_number" value="<?= e($profile['bank_account_number'] ?? '') ?>"></label>
            <label>IBAN<input name="bank_iban" value="<?= e($profile['bank_iban'] ?? '') ?>"></label>
            <label>Dega<input name="bank_branch" value="<?= e($profile['bank_branch'] ?? '') ?>"></label>

            <div class="form-actions wide">
                <button class="btn">Ruaj ndryshimet</button>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=profile">Anulo</a>
            </div>
        </form>
    </section>
    <?php
}

function criteria_text(array $s): string
{
    $items = [];
    if ($s['min_grade'] !== null) $items[] = 'Nota min: ' . e((string) $s['min_grade']);
    if ($s['required_university']) $items[] = 'Universiteti: ' . e($s['required_university']);
    if ($s['required_city']) $items[] = 'Qyteti: ' . e($s['required_city']);
    if ($s['required_social_status']) $items[] = 'Statusi: ' . e($s['required_social_status']);
    if ($s['requires_veteran_child']) $items[] = 'Femije veterani';
    if ($s['requires_orphan']) $items[] = 'Jetim';
    if ($s['requires_social_assistance']) $items[] = 'Ndihme sociale';
    return $items ? implode(' · ', $items) : 'Pa kritere shtese';
}

function profile_field(string $label, ?string $value, bool $wide = false): void
{
    $class = $wide ? 'profile-field wide' : 'profile-field';
    ?>
    <div class="<?= $class ?>">
        <span><?= e($label) ?></span>
        <strong><?= e($value !== null && $value !== '' ? $value : '-') ?></strong>
    </div>
    <?php
}

function yes_no(int $value): string
{
    return $value === 1 ? 'Po' : 'Jo';
}

function money_label(mixed $value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    return number_format((float) $value, 2) . ' EUR';
}

function document_status_label(string $status): string
{
    return [
        'verified' => 'E verifikuar',
        'missing' => 'Mungon',
        'not_required' => 'Nuk kerkohet',
    ][$status] ?? $status;
}

function allowed_value(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}
