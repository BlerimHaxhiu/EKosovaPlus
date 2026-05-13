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

set_app_language();
$lang = load_app_language();

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
        'update_scholarship_status' => action_update_scholarship_status(),
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

function set_app_language(): void
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['sq', 'en', 'sr'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }

    $_SESSION['lang'] ??= 'sq';
}

function current_lang(): string
{
    return $_SESSION['lang'] ?? 'sq';
}

function load_app_language(): array
{
    $file = dirname(__DIR__) . '/lang/' . current_lang() . '.php';
    if (!is_file($file)) {
        $file = dirname(__DIR__) . '/lang/sq.php';
    }

    $loaded = require $file;
    return is_array($loaded) ? $loaded : [];
}

function language_url(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    return BASE_URL . '/index.php?' . http_build_query($params);
}

function lang_label(string $sq, string $en, string $sr): string
{
    return match (current_lang()) {
        'en' => $en,
        'sr' => $sr,
        default => $sq,
    };
}

function translate_output(string $html): string
{
    $current = current_lang();
    if ($current === 'sq') {
        return $html;
    }

    $translations = array_replace(translations_for($current), $GLOBALS['lang']['__legacy'] ?? []);
    uksort($translations, fn($a, $b) => strlen($b) <=> strlen($a));
    return strtr($html, $translations);
}

function translations_for(string $lang): array
{
    $en = [
        'Ndihme' => 'Help',
        'Ndihmë' => 'Help',
        'Vegzat' => 'Links',
        'Gjuha:' => 'Language:',
        'Shqi' => 'Alb',
        'Kryesore' => 'Home',
        'Shërbime' => 'Services',
        'ShÃ«rbime' => 'Services',
        'Arsimi' => 'Education',
        'Bursat' => 'Scholarships',
        'Informata' => 'Information',
        'Njoftimet' => 'Notifications',
        'Te dhenat e mia' => 'My details',
        'Paneli' => 'Dashboard',
        'Dil' => 'Log out',
        'Platforma e shërbimeve online' => 'Online services platform',
        'Platforma e shÃ«rbimeve online' => 'Online services platform',
        'eKosova është platformë shtetërore ku shërbimet publike që gjenden në zyrat dhe sportelet fizike të institucioneve ofrohen në mënyrë elektronike.' => 'eKosova is a state platform where public services available at physical offices and counters of institutions are offered electronically.',
        'eKosova Ã«shtÃ« platformÃ« shtetÃ«rore ku shÃ«rbimet publike qÃ« gjenden nÃ« zyrat dhe sportelet fizike tÃ« institucioneve ofrohen nÃ« mÃ«nyrÃ« elektronike.' => 'eKosova is a state platform where public services available at physical offices and counters of institutions are offered electronically.',
        'Regjistrohu' => 'Register',
        'Hyr' => 'Log in',
        'Kërko shërbimin' => 'Search service',
        'KÃ«rko shÃ«rbimin' => 'Search service',
        'Shiko video udhëzuesit' => 'View video guides',
        'Shiko video udhÃ«zuesit' => 'View video guides',
        'Video udhëzuesit janë placeholder në këtë prototip.' => 'Video guides are placeholders in this prototype.',
        'Video udhÃ«zuesit janÃ« placeholder nÃ« kÃ«tÃ« prototip.' => 'Video guides are placeholders in this prototype.',
        'Vëmendje' => 'Attention',
        'VÃ«mendje' => 'Attention',
        'Ju lutem të keni parasysh që platforma eKosova mund të hapet vetëm përmes adresës zyrtare https://ekosova.rks-gov.net dhe https://rks-gov.net.' => 'Please note that the eKosova platform can only be accessed through the official address https://ekosova.rks-gov.net and https://rks-gov.net.',
        'Ju lutem tÃ« keni parasysh qÃ« platforma eKosova mund tÃ« hapet vetÃ«m pÃ«rmes adresÃ«s zyrtare https://ekosova.rks-gov.net dhe https://rks-gov.net.' => 'Please note that the eKosova platform can only be accessed through the official address https://ekosova.rks-gov.net and https://rks-gov.net.',
        'Çdo adresë, vegëz apo URL tjetër që nuk përfundon me rks-gov.net nuk i përket platformës eKosova dhe si e tillë nuk janë shërbime që ofrohen nga platforma shtetërore.' => 'Any other address, link, or URL that does not end with rks-gov.net does not belong to the eKosova platform and is not a service offered by the state platform.',
        'Ã‡do adresÃ«, vegÃ«z apo URL tjetÃ«r qÃ« nuk pÃ«rfundon me rks-gov.net nuk i pÃ«rket platformÃ«s eKosova dhe si e tillÃ« nuk janÃ« shÃ«rbime qÃ« ofrohen nga platforma shtetÃ«rore.' => 'Any other address, link, or URL that does not end with rks-gov.net does not belong to the eKosova platform and is not a service offered by the state platform.',
        'Familja' => 'Family',
        'Kontributet' => 'Contributions',
        'Grantet' => 'Grants',
        'Komunalitet' => 'Municipal services',
        'Vizita në platformë' => 'Platform visits',
        'Vizita nÃ« platformÃ«' => 'Platform visits',
        'Shfrytëzime të shërbimit' => 'Service uses',
        'ShfrytÃ«zime tÃ« shÃ«rbimit' => 'Service uses',
        'Ky shërbim është placeholder në këtë prototip.' => 'This service is a placeholder in this prototype.',
        'Ky shÃ«rbim Ã«shtÃ« placeholder nÃ« kÃ«tÃ« prototip.' => 'This service is a placeholder in this prototype.',
        'Krijo llogarinë tënde' => 'Create your account',
        'Krijo llogarinÃ« tÃ«nde' => 'Create your account',
        'Zgjedh shërbimin' => 'Choose the service',
        'Zgjedh shÃ«rbimin' => 'Choose the service',
        'Prano shërbimin' => 'Receive the service',
        'Prano shÃ«rbimin' => 'Receive the service',
        'Rreth portalit' => 'About the portal',
        'Privatësia' => 'Privacy',
        'PrivatÃ«sia' => 'Privacy',
        'Tani edhe në:' => 'Now also on:',
        'Tani edhe nÃ«:' => 'Now also on:',
        'Na ndiqni në:' => 'Follow us:',
        'Na ndiqni nÃ«:' => 'Follow us:',
        'Qendra e thirrjeve' => 'Call center',
        'Projekti u mundësua nga' => 'Project made possible by',
        'Projekti u mundÃ«sua nga' => 'Project made possible by',
        'Agjencia e Shoqërisë së Informacionit' => 'Information Society Agency',
        'Agjencia e ShoqÃ«risÃ« sÃ« Informacionit' => 'Information Society Agency',
        'MPB, Qeveria e Kosovës' => 'MIA, Government of Kosovo',
        'MPB, Qeveria e KosovÃ«s' => 'MIA, Government of Kosovo',
        'Shërbimet në nivel qendror' => 'Central level services',
        'ShÃ«rbimet nÃ« nivel qendror' => 'Central level services',
        'Shërbimet në nivel lokal' => 'Local level services',
        'ShÃ«rbimet nÃ« nivel lokal' => 'Local level services',
        'Të gjitha' => 'All',
        'TÃ« gjitha' => 'All',
        'Ndrysho te dhenat personale' => 'Edit personal data',
        'Te dhenat personale' => 'Personal data',
        'Student' => 'Student',
        'Banka' => 'Bank',
        'Kurset e perfunduara' => 'Completed courses',
        'Zanatet e kryera' => 'Completed trades',
        'Shkollimi i meparshem' => 'Previous education',
        'Ruaj ndryshimet' => 'Save changes',
        'Anulo' => 'Cancel',
        'Shto studime te reja' => 'Add current studies',
        'Shto studime te kaluara' => 'Add past studies',
        'Fshij' => 'Delete',
        'Nuk Ka' => 'None',
        'Nuk eshte plotesuar' => 'Not completed',
        'Kycu ne llogarine tuaj' => 'Log in to your account',
        'Fjalekalimi' => 'Password',
        'Mire se erdhet!' => 'Welcome!',
        'Per te filluar procesin e regjistrimit plotesoni te dhenat ne vazhdim.' => 'To start registration, complete the following information.',
        'Lloji i regjistrimit' => 'Registration type',
        'Regjistrohu si Perfitues - Student' => 'Register as beneficiary - Student',
        'Regjistrohu si Ofrues' => 'Register as provider',
        'Tipi i ofruesit' => 'Provider type',
        'Institucion Arsimor' => 'Educational institution',
        'Drejtori Komunale e Arsimit' => 'Municipal Education Directorate',
        'Ofrues i Pavarur' => 'Independent provider',
        'Universiteti' => 'University',
        'Qyteti' => 'City',
        'Numri i karteles' => 'Card number',
        'Data e skadences' => 'Expiry date',
        'Vazhdo' => 'Continue',
        'Paneli i studentit' => 'Student dashboard',
        'Shiko bursat aktive' => 'View active scholarships',
        'Te dhenat e verifikuara' => 'Verified data',
        'Statusi studentor' => 'Student status',
        'I verifikuar' => 'Verified',
        'Nota mesatare' => 'Average grade',
        'Statusi social' => 'Social status',
        'Statuset sociale' => 'Social statuses',
        'Femije veterani' => 'Veteran child',
        'Jetim' => 'Orphan',
        'Ndihme sociale' => 'Social assistance',
        'Aplikimet e mia' => 'My applications',
        'Ende nuk keni aplikuar per burse.' => 'You have not applied for a scholarship yet.',
        'Ankohu per Gabim' => 'Report an error',
        'Te dhenat e perdoruesit' => 'User data',
        'Profili i studentit ruan te dhenat qe perdoren gjate aplikimit automatik.' => 'The student profile stores the data used during automatic application.',
        'Ndrysho te dhenat' => 'Edit data',
        'Apliko per burse' => 'Apply for scholarship',
        'Familja e ngushte' => 'Close family',
        'Nuk ka te dhena familjare te regjistruara.' => 'No family data registered.',
        'Dokumentet dhe te dhenat e ruajtura' => 'Documents and stored data',
        'Nuk ka dokumente te regjistruara.' => 'No documents registered.',
        'Bursat aktive' => 'Active scholarships',
        'Apliko' => 'Apply',
        'Raporto problem' => 'Report problem',
        'Aplikimi automatik per burse studentore' => 'Automatic student scholarship application',
        'Aplikimi automatik për bursë studentore' => 'Automatic student scholarship application',
        'Aplikimi për licencë të karrierës në mësimdhënie' => 'Teaching career license application',
        'Aplikimi per licence te karrieres ne mesimdhenie' => 'Teaching career license application',
        'Per qytetare' => 'For citizens',
        'Për qytetarë' => 'For citizens',
        'Per biznese' => 'For businesses',
        'Për biznese' => 'For businesses',
        'Te gjitha sherbimet' => 'All services',
        'Të gjitha shërbimet' => 'All services',
        'Parashtroni kërkesë për ndihmë ose ankesë' => 'Submit a request for help or complaint',
        'Emri dhe mbiemri' => 'Full name',
        'Email adresa' => 'Email address',
        "Si mund t'ju ndihmojmë?" => 'How can we help you?',
        'Përshkruani kërkesën ose ankesën tuaj' => 'Describe your request or complaint',
        'Zgjedh shërbimin' => 'Choose service',
        'Shëndetësia' => 'Healthcare',
        'Tjetër' => 'Other',
        'Ndërpreje' => 'Cancel',
        'Dërgo' => 'Send',
        'Kërkesa për ndihmë është placeholder në këtë prototip.' => 'The help request is a placeholder in this prototype.',
        'FAQ eshte placeholder ne kete prototip.' => 'FAQ is a placeholder in this prototype.',
        'Vegzat jane placeholder ne kete prototip.' => 'Links are placeholders in this prototype.',
        'Webmail eshte placeholder ne kete prototip.' => 'Webmail is a placeholder in this prototype.',
    ];

    $sr = [
        'Ndihme' => 'Pomoć',
        'Ndihmë' => 'Pomoć',
        'Vegzat' => 'Linkovi',
        'Gjuha:' => 'Jezik:',
        'Shqi' => 'Alb',
        'Eng' => 'Eng',
        'Srb' => 'Srp',
        'Kryesore' => 'Početna',
        'Shërbime' => 'Usluge',
        'ShÃ«rbime' => 'Usluge',
        'Arsimi' => 'Obrazovanje',
        'Bursat' => 'Stipendije',
        'Informata' => 'Informacije',
        'Njoftimet' => 'Obaveštenja',
        'Te dhenat e mia' => 'Moji podaci',
        'Paneli' => 'Panel',
        'Dil' => 'Odjavi se',
        'Platforma e shërbimeve online' => 'Platforma online usluga',
        'Platforma e shÃ«rbimeve online' => 'Platforma online usluga',
        'eKosova është platformë shtetërore ku shërbimet publike që gjenden në zyrat dhe sportelet fizike të institucioneve ofrohen në mënyrë elektronike.' => 'eKosova je državna platforma na kojoj se javne usluge iz kancelarija i šaltera institucija nude elektronski.',
        'eKosova Ã«shtÃ« platformÃ« shtetÃ«rore ku shÃ«rbimet publike qÃ« gjenden nÃ« zyrat dhe sportelet fizike tÃ« institucioneve ofrohen nÃ« mÃ«nyrÃ« elektronike.' => 'eKosova je državna platforma na kojoj se javne usluge iz kancelarija i šaltera institucija nude elektronski.',
        'Regjistrohu' => 'Registruj se',
        'Hyr' => 'Prijavi se',
        'Kërko shërbimin' => 'Pretraži uslugu',
        'KÃ«rko shÃ«rbimin' => 'Pretraži uslugu',
        'Shiko video udhëzuesit' => 'Pogledaj video uputstva',
        'Shiko video udhÃ«zuesit' => 'Pogledaj video uputstva',
        'Video udhëzuesit janë placeholder në këtë prototip.' => 'Video uputstva su placeholder u ovom prototipu.',
        'Video udhÃ«zuesit janÃ« placeholder nÃ« kÃ«tÃ« prototip.' => 'Video uputstva su placeholder u ovom prototipu.',
        'Vëmendje' => 'Pažnja',
        'VÃ«mendje' => 'Pažnja',
        'Ju lutem të keni parasysh që platforma eKosova mund të hapet vetëm përmes adresës zyrtare https://ekosova.rks-gov.net dhe https://rks-gov.net.' => 'Imajte na umu da se platformi eKosova može pristupiti samo preko zvanične adrese https://ekosova.rks-gov.net i https://rks-gov.net.',
        'Ju lutem tÃ« keni parasysh qÃ« platforma eKosova mund tÃ« hapet vetÃ«m pÃ«rmes adresÃ«s zyrtare https://ekosova.rks-gov.net dhe https://rks-gov.net.' => 'Imajte na umu da se platformi eKosova može pristupiti samo preko zvanične adrese https://ekosova.rks-gov.net i https://rks-gov.net.',
        'Çdo adresë, vegëz apo URL tjetër që nuk përfundon me rks-gov.net nuk i përket platformës eKosova dhe si e tillë nuk janë shërbime që ofrohen nga platforma shtetërore.' => 'Svaka druga adresa, link ili URL koji se ne završava sa rks-gov.net ne pripada platformi eKosova i nije usluga državne platforme.',
        'Ã‡do adresÃ«, vegÃ«z apo URL tjetÃ«r qÃ« nuk pÃ«rfundon me rks-gov.net nuk i pÃ«rket platformÃ«s eKosova dhe si e tillÃ« nuk janÃ« shÃ«rbime qÃ« ofrohen nga platforma shtetÃ«rore.' => 'Svaka druga adresa, link ili URL koji se ne završava sa rks-gov.net ne pripada platformi eKosova i nije usluga državne platforme.',
        'Familja' => 'Porodica',
        'Kontributet' => 'Doprinosi',
        'Grantet' => 'Grantovi',
        'Komunalitet' => 'Komunalne usluge',
        'Vizita në platformë' => 'Posete platformi',
        'Vizita nÃ« platformÃ«' => 'Posete platformi',
        'Shfrytëzime të shërbimit' => 'Korišćenja usluge',
        'ShfrytÃ«zime tÃ« shÃ«rbimit' => 'Korišćenja usluge',
        'Ky shërbim është placeholder në këtë prototip.' => 'Ova usluga je placeholder u ovom prototipu.',
        'Ky shÃ«rbim Ã«shtÃ« placeholder nÃ« kÃ«tÃ« prototip.' => 'Ova usluga je placeholder u ovom prototipu.',
        'Krijo llogarinë tënde' => 'Kreiraj svoj nalog',
        'Krijo llogarinÃ« tÃ«nde' => 'Kreiraj svoj nalog',
        'Zgjedh shërbimin' => 'Izaberi uslugu',
        'Zgjedh shÃ«rbimin' => 'Izaberi uslugu',
        'Prano shërbimin' => 'Primi uslugu',
        'Prano shÃ«rbimin' => 'Primi uslugu',
        'Rreth portalit' => 'O portalu',
        'Privatësia' => 'Privatnost',
        'PrivatÃ«sia' => 'Privatnost',
        'Tani edhe në:' => 'Sada i na:',
        'Tani edhe nÃ«:' => 'Sada i na:',
        'Na ndiqni në:' => 'Pratite nas:',
        'Na ndiqni nÃ«:' => 'Pratite nas:',
        'Qendra e thirrjeve' => 'Pozivni centar',
        'Projekti u mundësua nga' => 'Projekat omogućio',
        'Projekti u mundÃ«sua nga' => 'Projekat omogućio',
        'Agjencia e Shoqërisë së Informacionit' => 'Agencija za informaciono društvo',
        'Agjencia e ShoqÃ«risÃ« sÃ« Informacionit' => 'Agencija za informaciono društvo',
        'MPB, Qeveria e Kosovës' => 'MUP, Vlada Kosova',
        'MPB, Qeveria e KosovÃ«s' => 'MUP, Vlada Kosova',
        'Shërbimet në nivel qendror' => 'Usluge na centralnom nivou',
        'ShÃ«rbimet nÃ« nivel qendror' => 'Usluge na centralnom nivou',
        'Shërbimet në nivel lokal' => 'Usluge na lokalnom nivou',
        'ShÃ«rbimet nÃ« nivel lokal' => 'Usluge na lokalnom nivou',
        'Të gjitha' => 'Sve',
        'TÃ« gjitha' => 'Sve',
        'Ndrysho te dhenat personale' => 'Izmeni lične podatke',
        'Te dhenat personale' => 'Lični podaci',
        'Student' => 'Student',
        'Banka' => 'Banka',
        'Kurset e perfunduara' => 'Završeni kursevi',
        'Zanatet e kryera' => 'Završeni zanati',
        'Shkollimi i meparshem' => 'Prethodno obrazovanje',
        'Ruaj ndryshimet' => 'Sačuvaj izmene',
        'Anulo' => 'Otkaži',
        'Shto studime te reja' => 'Dodaj trenutne studije',
        'Shto studime te kaluara' => 'Dodaj prethodne studije',
        'Fshij' => 'Obriši',
        'Nuk Ka' => 'Nema',
        'Nuk eshte plotesuar' => 'Nije popunjeno',
        'Kycu ne llogarine tuaj' => 'Prijavite se na svoj nalog',
        'Fjalekalimi' => 'Lozinka',
        'Mire se erdhet!' => 'Dobrodošli!',
        'Per te filluar procesin e regjistrimit plotesoni te dhenat ne vazhdim.' => 'Za početak registracije popunite sledeće podatke.',
        'Lloji i regjistrimit' => 'Tip registracije',
        'Regjistrohu si Perfitues - Student' => 'Registruj se kao korisnik - Student',
        'Regjistrohu si Ofrues' => 'Registruj se kao pružalac',
        'Tipi i ofruesit' => 'Tip pružaoca',
        'Institucion Arsimor' => 'Obrazovna institucija',
        'Drejtori Komunale e Arsimit' => 'Opštinska direkcija za obrazovanje',
        'Ofrues i Pavarur' => 'Nezavisni pružalac',
        'Universiteti' => 'Univerzitet',
        'Qyteti' => 'Grad',
        'Numri i karteles' => 'Broj kartice',
        'Data e skadences' => 'Datum isteka',
        'Vazhdo' => 'Nastavi',
        'Paneli i studentit' => 'Studentski panel',
        'Shiko bursat aktive' => 'Pogledaj aktivne stipendije',
        'Te dhenat e verifikuara' => 'Verifikovani podaci',
        'Statusi studentor' => 'Studentski status',
        'I verifikuar' => 'Verifikovan',
        'Nota mesatare' => 'Prosečna ocena',
        'Statusi social' => 'Socijalni status',
        'Statuset sociale' => 'Socijalni statusi',
        'Femije veterani' => 'Dete veterana',
        'Jetim' => 'Siroče',
        'Ndihme sociale' => 'Socijalna pomoć',
        'Aplikimet e mia' => 'Moje prijave',
        'Ende nuk keni aplikuar per burse.' => 'Još niste aplicirali za stipendiju.',
        'Ankohu per Gabim' => 'Prijavi grešku',
        'Te dhenat e perdoruesit' => 'Podaci korisnika',
        'Profili i studentit ruan te dhenat qe perdoren gjate aplikimit automatik.' => 'Studentski profil čuva podatke koji se koriste tokom automatske prijave.',
        'Ndrysho te dhenat' => 'Izmeni podatke',
        'Apliko per burse' => 'Apliciraj za stipendiju',
        'Familja e ngushte' => 'Uža porodica',
        'Nuk ka te dhena familjare te regjistruara.' => 'Nema registrovanih porodičnih podataka.',
        'Dokumentet dhe te dhenat e ruajtura' => 'Dokumenti i sačuvani podaci',
        'Nuk ka dokumente te regjistruara.' => 'Nema registrovanih dokumenata.',
        'Bursat aktive' => 'Aktivne stipendije',
        'Apliko' => 'Apliciraj',
        'Raporto problem' => 'Prijavi problem',
        'Aplikimi automatik per burse studentore' => 'Automatska prijava za studentsku stipendiju',
        'Aplikimi automatik për bursë studentore' => 'Automatska prijava za studentsku stipendiju',
        'Aplikimi për licencë të karrierës në mësimdhënie' => 'Prijava za licencu nastavničke karijere',
        'Aplikimi per licence te karrieres ne mesimdhenie' => 'Prijava za licencu nastavničke karijere',
        'Per qytetare' => 'Za građane',
        'Për qytetarë' => 'Za građane',
        'Per biznese' => 'Za biznise',
        'Për biznese' => 'Za biznise',
        'Te gjitha sherbimet' => 'Sve usluge',
        'Të gjitha shërbimet' => 'Sve usluge',
        'Parashtroni kërkesë për ndihmë ose ankesë' => 'Podnesite zahtev za pomoć ili žalbu',
        'Emri dhe mbiemri' => 'Ime i prezime',
        'Email adresa' => 'Email adresa',
        "Si mund t'ju ndihmojmë?" => 'Kako vam možemo pomoći?',
        'Përshkruani kërkesën ose ankesën tuaj' => 'Opišite svoj zahtev ili žalbu',
        'Zgjedh shërbimin' => 'Izaberite uslugu',
        'Shëndetësia' => 'Zdravstvo',
        'Tjetër' => 'Drugo',
        'Ndërpreje' => 'Prekini',
        'Dërgo' => 'Pošalji',
        'Kërkesa për ndihmë është placeholder në këtë prototip.' => 'Zahtev za pomoć je placeholder u ovom prototipu.',
        'FAQ eshte placeholder ne kete prototip.' => 'FAQ je placeholder u ovom prototipu.',
        'Vegzat jane placeholder ne kete prototip.' => 'Linkovi su placeholder u ovom prototipu.',
        'Webmail eshte placeholder ne kete prototip.' => 'Webmail je placeholder u ovom prototipu.',
    ];

    return $lang === 'sr' ? $sr : $en;
}

function action_login(): void
{
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, name, username, email, role, provider_type, password_hash FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash(t('invalid_login'), 'error');
        redirect('login');
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    flash(t('login_welcome'));
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
        flash(t('registration_required_fields'), 'error');
        redirect('register');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, username, email, password_hash, role, provider_type) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $providerType]);
        $userId = (int) $pdo->lastInsertId();

        if ($role === 'student') {
            $stmt = $pdo->prepare('INSERT INTO student_profiles (user_id, personal_number, university, city, average_grade, social_status, bank_name, bank_account_holder, bank_account_number, bank_iban, bank_branch, is_veteran_child, is_orphan, receives_social_assistance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $userId,
                trim($_POST['personal_number'] ?? ''),
                trim($_POST['university'] ?? 'Universiteti Kadri Zeka'),
                trim($_POST['city'] ?? 'Kamenice'),
                (float) ($_POST['average_grade'] ?? 8.5),
                social_status_from_flags(),
                trim($_POST['bank_name'] ?? 'Banka Ekonomike'),
                trim($_POST['bank_account_holder'] ?? ''),
                trim($_POST['bank_account_number'] ?? ''),
                trim($_POST['bank_iban'] ?? ''),
                encode_bank_card_metadata($_POST),
                isset($_POST['is_veteran_child']) ? 1 : 0,
                isset($_POST['is_orphan']) ? 1 : 0,
                isset($_POST['receives_social_assistance']) ? 1 : 0,
            ]);
        }

        $pdo->commit();
        flash(t('registration_success'));
        redirect('login');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash(t('user_exists'), 'error');
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
    require_role(['provider', 'admin']);
    ensure_scholarship_template_schema();
    $id = (int) ($_POST['id'] ?? 0);
    $isAdmin = current_user()['role'] === 'admin';
    $redirectPage = $isAdmin ? 'admin' : 'provider';
    $providerId = (int) current_user()['id'];
    $templateId = $isAdmin ? (int) ($_POST['template_id'] ?? 0) : 0;
    $category = scholarship_category_value($_POST['category'] ?? '');
    $providerName = trim($_POST['provider_name'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? ($_POST['deadline'] ?? ''));

    if ($isAdmin) {
        $providerId = (int) ($_POST['provider_id'] ?? 0);
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE id = ? AND role = "provider" AND is_active = 1');
        $stmt->execute([$providerId]);
        if ((int) $stmt->fetchColumn() === 0) {
            flash('Zgjidhni nje ofrues valid.', 'error');
            redirect('admin');
        }
    }

    if ($providerName === '') {
        $stmt = db()->prepare('SELECT name FROM users WHERE id = ?');
        $stmt->execute([$providerId]);
        $providerName = (string) $stmt->fetchColumn();
    }

    $data = [
        $templateId > 0 ? $templateId : null,
        $category !== '' ? $category : null,
        $providerName !== '' ? $providerName : null,
        trim($_POST['title'] ?? ''),
        trim($_POST['description'] ?? ''),
        (float) ($_POST['amount'] ?? 0),
        $startDate !== '' ? $startDate : null,
        $endDate,
        $endDate,
        ($_POST['min_grade'] ?? '') !== '' ? (float) $_POST['min_grade'] : null,
        ($_POST['required_university'] ?? '') !== '' ? trim($_POST['required_university']) : null,
        ($_POST['required_city'] ?? '') !== '' ? trim($_POST['required_city']) : null,
        ($_POST['required_social_status'] ?? '') !== '' ? trim($_POST['required_social_status']) : null,
        isset($_POST['requires_veteran_child']) ? 1 : 0,
        isset($_POST['requires_orphan']) ? 1 : 0,
        isset($_POST['requires_social_assistance']) ? 1 : 0,
        ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
        ($_POST['status'] ?? 'active') === 'inactive' ? 0 : 1,
    ];

    if ($data[3] === '' || $data[5] <= 0 || $data[7] === '') {
        flash(t('scholarship_required_fields'), 'error');
        redirect($redirectPage);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        if ($id > 0) {
            $data[] = $id;
            $data[] = $providerId;
            $stmt = $pdo->prepare('UPDATE scholarships SET template_id=?, category=?, provider_name=?, title=?, description=?, amount=?, start_date=?, end_date=?, deadline=?, min_grade=?, required_university=?, required_city=?, required_social_status=?, requires_veteran_child=?, requires_orphan=?, requires_social_assistance=?, status=?, is_active=? WHERE id=? AND provider_id=?');
            $stmt->execute($data);
            $scholarshipId = $id;
            $pdo->prepare('DELETE FROM scholarship_rules WHERE scholarship_id=?')->execute([$scholarshipId]);
            $pdo->prepare('DELETE FROM scholarship_documents WHERE scholarship_id=?')->execute([$scholarshipId]);
            popup_flash(t('scholarship_updated'));
        } else {
            array_unshift($data, $providerId);
            $stmt = $pdo->prepare('INSERT INTO scholarships (provider_id, template_id, category, provider_name, title, description, amount, start_date, end_date, deadline, min_grade, required_university, required_city, required_social_status, requires_veteran_child, requires_orphan, requires_social_assistance, status, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute($data);
            $scholarshipId = (int) $pdo->lastInsertId();
            popup_flash(t('scholarship_created'));
        }

        save_scholarship_rules($pdo, $scholarshipId, $_POST['rules'] ?? []);
        save_scholarship_documents($pdo, $scholarshipId, $_POST['documents'] ?? []);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash(t('error_saving'), 'error');
        redirect($redirectPage);
    }

    redirect($redirectPage);
}

function action_delete_scholarship(): void
{
    require_role(['provider', 'admin']);
    ensure_scholarship_template_schema();
    $id = (int) ($_POST['id'] ?? 0);

    if (current_user()['role'] === 'provider') {
        $stmt = db()->prepare('DELETE FROM scholarships WHERE id = ? AND provider_id = ?');
        $stmt->execute([$id, current_user()['id']]);
        popup_flash('Fshirja u krye me sukses.');
        redirect('provider');
    }

    $stmt = db()->prepare('DELETE FROM scholarships WHERE id = ?');
    $stmt->execute([$id]);
    popup_flash('Fshirja u krye me sukses.');
    redirect('admin');
}

function action_update_scholarship_status(): void
{
    require_role(['admin']);
    ensure_scholarship_template_schema();
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
    $stmt = db()->prepare('UPDATE scholarships SET status=?, is_active=? WHERE id=?');
    $stmt->execute([$status, $status === 'active' ? 1 : 0, (int) ($_POST['id'] ?? 0)]);
    popup_flash('Statusi i burses u perditesua.');
    redirect('admin');
}

function ensure_scholarship_template_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }

    $pdo = db();
    $pdo->exec('CREATE TABLE IF NOT EXISTS scholarship_templates (
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
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS scholarship_template_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_id INT NOT NULL,
        rule_key VARCHAR(120) NOT NULL,
        operator VARCHAR(30) NOT NULL DEFAULT "=",
        rule_value VARCHAR(180),
        is_required TINYINT(1) NOT NULL DEFAULT 1,
        points INT NOT NULL DEFAULT 0,
        description TEXT,
        FOREIGN KEY (template_id) REFERENCES scholarship_templates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS scholarship_template_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_id INT NOT NULL,
        document_section_name VARCHAR(160) NOT NULL,
        is_required TINYINT(1) NOT NULL DEFAULT 1,
        is_optional_bonus TINYINT(1) NOT NULL DEFAULT 0,
        description TEXT,
        FOREIGN KEY (template_id) REFERENCES scholarship_templates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS scholarship_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        scholarship_id INT NOT NULL,
        rule_key VARCHAR(120) NOT NULL,
        operator VARCHAR(30) NOT NULL DEFAULT "=",
        rule_value VARCHAR(180),
        is_required TINYINT(1) NOT NULL DEFAULT 1,
        points INT NOT NULL DEFAULT 0,
        description TEXT,
        FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
    ) ENGINE=InnoDB');

    $pdo->exec('CREATE TABLE IF NOT EXISTS scholarship_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        scholarship_id INT NOT NULL,
        document_section_name VARCHAR(160) NOT NULL,
        is_required TINYINT(1) NOT NULL DEFAULT 1,
        is_optional_bonus TINYINT(1) NOT NULL DEFAULT 0,
        description TEXT,
        FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE
    ) ENGINE=InnoDB');

    ensure_scholarship_column('template_id', 'INT NULL');
    ensure_scholarship_column('category', 'VARCHAR(80) NULL');
    ensure_scholarship_column('provider_name', 'VARCHAR(160) NULL');
    ensure_scholarship_column('start_date', 'DATE NULL');
    ensure_scholarship_column('end_date', 'DATE NULL');
    ensure_scholarship_column('is_active', 'TINYINT(1) NOT NULL DEFAULT 1');
    ensure_application_flow_schema();

    seed_scholarship_templates();
    $done = true;
}

function ensure_application_flow_schema(): void
{
    ensure_table_column('applications', 'applied_at', 'DATETIME NULL');
    ensure_table_column('applications', 'points_total', 'INT NULL');
    ensure_table_column('applications', 'result_message', 'TEXT NULL');
    ensure_table_column('complaints', 'scholarship_category', 'VARCHAR(80) NULL');
    ensure_table_column('complaints', 'provider_name', 'VARCHAR(160) NULL');
    ensure_table_column('complaints', 'reason', 'TEXT NULL');

    try {
        db()->exec("ALTER TABLE applications MODIFY status VARCHAR(30) NOT NULL DEFAULT 'fituar'");
    } catch (Throwable $e) {
        // Older installs may already have a compatible type.
    }

    try {
        db()->exec("ALTER TABLE complaints MODIFY application_id INT NULL");
    } catch (Throwable $e) {
        // Older installs may already allow NULL or may handle complaints through the new columns.
    }
}

function ensure_scholarship_column(string $column, string $definition): void
{
    ensure_table_column('scholarships', $column, $definition);
}

function ensure_table_column(string $table, string $column, string $definition): void
{
    $allowedTables = ['scholarships', 'applications', 'complaints'];
    if (!in_array($table, $allowedTables, true) || !preg_match('/^[a-z_]+$/', $column)) {
        return;
    }

    $stmt = db()->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    if ((int) $stmt->fetchColumn() === 0) {
        db()->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
    }
}

function seed_scholarship_templates(): void
{
    foreach (default_scholarship_templates() as $template) {
        insert_scholarship_template($template);
    }
}

function insert_scholarship_template(array $template): void
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id FROM scholarship_templates WHERE category=? AND provider_name=?');
    $stmt->execute([$template['category'], $template['provider_name']]);
    $templateId = (int) ($stmt->fetchColumn() ?: 0);

    if ($templateId > 0) {
        $stmt = $pdo->prepare('UPDATE scholarship_templates SET title=?, description=?, start_date=?, end_date=?, is_active=1 WHERE id=?');
        $stmt->execute([$template['title'], $template['description'], $template['start_date'], $template['end_date'], $templateId]);
        $pdo->prepare('DELETE FROM scholarship_template_rules WHERE template_id=?')->execute([$templateId]);
        $pdo->prepare('DELETE FROM scholarship_template_documents WHERE template_id=?')->execute([$templateId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO scholarship_templates (category, provider_name, title, description, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
        $stmt->execute([
        $template['category'],
        $template['provider_name'],
        $template['title'],
        $template['description'],
        $template['start_date'],
        $template['end_date'],
        ]);
        $templateId = (int) $pdo->lastInsertId();
    }

    $ruleStmt = $pdo->prepare('INSERT INTO scholarship_template_rules (template_id, rule_key, operator, rule_value, is_required, points, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($template['rules'] as $rule) {
        $ruleStmt->execute([$templateId, $rule['rule_key'], $rule['operator'], $rule['rule_value'], $rule['is_required'], $rule['points'], $rule['description']]);
    }

    $documentStmt = $pdo->prepare('INSERT INTO scholarship_template_documents (template_id, document_section_name, is_required, is_optional_bonus, description) VALUES (?, ?, ?, ?, ?)');
    foreach ($template['documents'] as $document) {
        $documentStmt->execute([$templateId, $document['document_section_name'], $document['is_required'], $document['is_optional_bonus'], $document['description']]);
    }
}

function default_scholarship_templates(): array
{
    return [
        [
            'category' => 'Burse komunale',
            'provider_name' => 'Komuna e Kamenices',
            'title' => 'Bursa Komunale per Studente - Komuna e Kamenices 2026/2027',
            'description' => 'Afati: 15 dite, nga 20.10.2026 deri me 04.11.2026. Bursa perdor vetem seksione te profilit te studentit dhe nuk kerkon ngarkim dokumentesh.',
            'start_date' => '2026-10-20',
            'end_date' => '2026-11-04',
            'rules' => array_merge([
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('residence_municipality', '=', 'Kamenice', true, 0, 'Certifikata e Vendbanimit tregon se studenti eshte banor i Komunes se Kamenices.'),
                rule_template('full_time', '=', 'po', true, 0, 'Studenti eshte i rregullt.'),
                rule_template('student_active', '=', 'po', true, 0, 'Studenti eshte aktiv.'),
                rule_template('repeating_year', '=', 'jo', true, 0, 'Studenti nuk eshte perserites.'),
                rule_template('study_level', '!=', 'Master', true, 0, 'Niveli i studimeve nuk eshte Master.'),
                rule_template('study_level', '!=', 'PhD', true, 0, 'Niveli i studimeve nuk eshte PhD.'),
                rule_template('first_year_university', '=', 'Universiteti Kadri Zeka', true, 0, 'Per vitin e pare: universiteti duhet te jete Universiteti Kadri Zeka.'),
                rule_template('public_university_after_first_year', '=', 'po', true, 0, 'Per vitin e dyte e tutje: universitet publik i Republikes se Kosoves.'),
                rule_template('average_grade', 'between', '9-10', false, 30, 'Nota 9 deri 10.'),
                rule_template('average_grade', 'between', '8-9', false, 20, 'Nota 8 deri 9.'),
                rule_template('average_grade', 'between', '7-8', false, 15, 'Nota 7 deri 8.'),
                rule_template('average_grade', 'between', '6-7', false, 10, 'Nota 6 deri 7.'),
                rule_template('family_students_count', '>=', '2', false, 10, '2 ose me shume studente ne familje.'),
                rule_template('war_category', '=', 'po', false, 10, 'Kategori e dale nga lufta.'),
                rule_template('receives_social_assistance', '=', 'po', false, 10, 'Ndihme sociale.'),
                rule_template('is_deficit_program', '=', 'po', false, 10, 'Drejtim deficitar.'),
                rule_template('special_needs', '=', 'po', false, 10, 'Nevoja te vecanta.'),
                rule_template('two_parents_missing', '=', 'po', false, 20, 'Pa dy prinder.'),
                rule_template('one_parent_missing', '=', 'po', false, 10, 'Pa njerin prind.'),
            ], deficit_rules(['Mjekesi e pergjithshme', 'TIK', 'Matematike', 'Fizike', 'Kimi', 'Biologji', 'Ndertimtari', 'Arkitekture', 'Mekanike', 'FIEK'])),
            'documents' => [
                document_template('ID / Leternjoftimi', true, false, 'Obligativ.'),
                document_template('Vertetimi i Studentit Aktiv', true, false, 'Obligativ.'),
                document_template('Certifikata e Notave', true, false, 'Obligative.'),
                document_template('Certifikata e Vendbanimit', true, false, 'Obligative.'),
                document_template('Vertetimi per Kategori te Luftes', false, true, 'Opsional/pikezues.'),
                document_template('Vertetimi per Ndihme Sociale', false, true, 'Opsional/pikezues.'),
                document_template('Certifikata e Vdekjes se Prinderve', false, true, 'Opsional/pikezues.'),
                document_template('Vertetimi per Nevoja te Vecanta', false, true, 'Opsional/pikezues.'),
                document_template('Konfirmimi Bankar', true, false, 'Obligativ.'),
            ],
        ],
        [
            'category' => 'Burse komunale',
            'provider_name' => 'Komuna e Gjilanit',
            'title' => 'Bursa Komunale per Studente - Komuna e Gjilanit 2026/2027',
            'description' => 'Thirrje komunale 2026/2027 me hapje me 20.10.2026. Rregull buxheti: nese numri i perfituesve kalon buxhetin, aplikuesit renditen sipas mesatares dhe drejtimit deri ne perfundim te buxhetit.',
            'start_date' => '2026-10-20',
            'end_date' => '2026-11-04',
            'rules' => array_merge([
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('residence_municipality', '=', 'Gjilan', true, 0, 'Vendbanim i perhershem ne Komunen e Gjilanit, pervec nese kriteret lejojne kombinime te tjera.'),
                rule_template('full_time', '=', 'po', true, 0, 'Studenti eshte i rregullt.'),
                rule_template('study_country', '=', 'Kosove', true, 0, 'Studenti studion ne universitet ne Republiken e Kosoves.'),
                rule_template('repeating_year', '=', 'jo', true, 0, 'Studenti nuk eshte perserites.'),
                rule_template('student_employed', '=', 'jo', true, 0, 'Studenti nuk eshte i punesuar.'),
                rule_template('active_other_scholarship', '=', 'jo', true, 0, 'Studenti nuk ka burse tjeter aktive.'),
                rule_template('average_grade', '>=', '7.50', true, 0, 'Mesatarja minimale eshte 7.5.'),
                rule_template('deficit_program_average_grade', '>=', '6.00', true, 0, 'Per drejtime deficitare lejohet mesatare 6.0 e me lart.'),
                rule_template('average_grade', 'score_base', 'nota mesatare', false, 0, 'Nota mesatare perdoret si baze.'),
                rule_template('war_family', '=', 'po', false, 1, 'Familje deshmori/veterani UCK.'),
                rule_template('missing_civilian_family', '=', 'po', false, 1, 'Familjar i civileve te pagjetur.'),
                rule_template('receives_social_assistance', '=', 'po', false, 1, 'Asistence sociale.'),
                rule_template('parent_missing', '=', 'po', false, 1, 'Pa njerin ose dy prinder.'),
                rule_template('final_year_average_grade', '>=', '7.50', false, 5, 'Viti i fundit me mesatare 7.5+.'),
                rule_template('bonus_points_limit', '<=', '1', false, 0, 'Studenti nuk mund te marre me shume se 1 pike shtese, pervec pikeve nga nota mesatare.'),
            ], deficit_rules(['Mjekesi e pergjithshme', 'Fakulteti Teknik', 'Matematike', 'Fizike', 'Kimi', 'Biologji'])),
            'documents' => [
                document_template('ID / Leternjoftimi', true, false, 'Obligativ.'),
                document_template('Vertetimi i Studentit Aktiv', true, false, 'Obligativ.'),
                document_template('Certifikata e Notave', true, false, 'Obligative.'),
                document_template('Certifikata e Vendbanimit', true, false, 'Obligative.'),
                document_template('Vertetimi nga Administrata Tatimore e Kosoves', true, false, 'Obligativ.'),
                document_template('Vertetimi per Kategori te Luftes', false, true, 'Opsional/pikezues.'),
                document_template('Vertetimi per Ndihme Sociale', false, true, 'Opsional/pikezues.'),
                document_template('Certifikata e Vdekjes se Prinderve', false, true, 'Opsional/pikezues.'),
                document_template('Vertetimi per Nevoja te Vecanta', false, true, 'Opsional/pikezues.'),
                document_template('Konfirmimi Bankar', true, false, 'Obligativ.'),
            ],
        ],
        [
            'category' => 'Burse komunale',
            'provider_name' => 'Komuna e Vitise',
            'title' => 'Bursa Komunale per Studente - Komuna e Vitise 2026/2027',
            'description' => 'Burse komunale per studente te rregullt nga Komuna e Vitise. Perparesia bazohet ne drejtim deficitar, note, kategori sociale/familjare dhe numrin e studenteve ne familje.',
            'start_date' => '2026-10-20',
            'end_date' => '2026-11-04',
            'rules' => array_merge([
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('residence_municipality', '=', 'Viti', true, 0, 'Studenti eshte banor i Komunes se Vitise.'),
                rule_template('full_time', '=', 'po', true, 0, 'Studenti eshte i rregullt.'),
                rule_template('public_university', '=', 'po', true, 0, 'Studenti studion ne universitet publik te Republikes se Kosoves.'),
                rule_template('study_year', '>=', '2', true, 0, 'Studenti eshte ne vitin e dyte ose me lart.'),
                rule_template('study_level', '!=', 'Master', true, 0, 'Studenti nuk eshte Master.'),
                rule_template('study_level', '!=', 'PhD', true, 0, 'Studenti nuk eshte PhD.'),
                rule_template('private_university', '=', 'jo', true, 0, 'Studenti nuk eshte ne universitet privat.'),
                rule_template('commercial_student', '=', 'jo', true, 0, 'Studenti nuk eshte student komercial.'),
                rule_template('student_employed', '=', 'jo', true, 0, 'Studenti nuk eshte i punesuar.'),
                rule_template('is_deficit_program', '=', 'po', false, 0, 'Perparesi per drejtim deficitar.'),
                rule_template('average_grade', 'desc', 'nota me e larte', false, 0, 'Perparesi per note mesatare me te larte.'),
                rule_template('war_category', '=', 'po', false, 0, 'Perparesi per kategori te dala nga lufta.'),
                rule_template('receives_social_assistance', '=', 'po', false, 0, 'Perparesi per ndihme sociale.'),
                rule_template('parents_alive', '=', 'jo', false, 0, 'Perparesi per familje pa prinder ne jete.'),
                rule_template('family_students_count', '>', '1', false, 0, 'Me shume studente ne familje, por perfitues mund te jete vetem njeri.'),
            ], deficit_rules(['Matematike', 'Fizike', 'Gjuhe Gjermane'])),
            'documents' => [
                document_template('ID / Leternjoftimi', true, false, 'Obligativ.'),
                document_template('Certifikata e Vendbanimit', true, false, 'Obligative.'),
                document_template('Certifikata e Notave', true, false, 'Obligative.'),
                document_template('Vertetimi i Studentit Aktiv', true, false, 'Obligativ.'),
                document_template('Vertetimi nga Administrata Tatimore e Kosoves', true, false, 'Obligativ: studenti nuk eshte i punesuar.'),
                document_template('Vertetimi per Kategori te Luftes', false, true, 'Opsional/perparesi.'),
                document_template('Vertetimi per Ndihme Sociale', false, true, 'Opsional/perparesi.'),
                document_template('Certifikata e Vdekjes se Prinderve', false, true, 'Opsional/perparesi.'),
                document_template('Deklarata e Bashkesise Familjare', false, true, 'Opsional/perparesi.'),
                document_template('Konfirmimi Bankar', true, false, 'Obligativ nese duhet per pagese.'),
            ],
        ],
        [
            'category' => 'Burse komunale',
            'provider_name' => 'Komuna e Ferizajit',
            'title' => 'Bursa Komunale per Studente - Komuna e Ferizajt 2026/2027',
            'description' => 'Vlera: 50 euro ne muaj per 10 muaj, total 500 euro. Kontrollohet qe studenti nuk ka burse tjeter aktive; nese merr tjeter burse, kthimi i buxhetit parandalohet ne EKosova+ me kete kontroll.',
            'start_date' => '2026-10-20',
            'end_date' => '2026-11-04',
            'rules' => [
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('residence_municipality', '=', 'Ferizaj', true, 0, 'Studenti eshte banor rezident i Komunes se Ferizajt.'),
                rule_template('study_location', 'in', 'Kosove,jashte shtetit', true, 0, 'Studenti studion ne universitet ne Kosove ose jashte shtetit.'),
                rule_template('study_level', '!=', 'Master', true, 0, 'Studenti nuk eshte Master.'),
                rule_template('study_level', '!=', 'PhD', true, 0, 'Studenti nuk eshte PhD.'),
                rule_template('full_time', '=', 'po', true, 0, 'Studenti eshte i rregullt.'),
                rule_template('study_year', '>=', '2', true, 0, 'Studenti eshte se paku ne vitin e dyte.'),
                rule_template('previous_year_exams_completed', '=', 'po', true, 0, 'I ka perfunduar te gjitha provimet e vitit paraprak.'),
                rule_template('academic_year_registered', '=', 'po', true, 0, 'Ka regjistruar vitin akademik per te cilin ndahet bursa.'),
                rule_template('average_grade', '>=', '7.50', true, 0, 'Nota mesatare eshte 7.50 ose me lart.'),
                rule_template('lost_year', '=', 'jo', true, 0, 'Studenti nuk ka humbur vitin.'),
                rule_template('repeating_year', '=', 'jo', true, 0, 'Studenti nuk eshte perserites.'),
                rule_template('self_financing_or_active_worker', '=', 'jo', true, 0, 'Studenti nuk eshte me vetefinancim/punetor aktiv.'),
                rule_template('correspondence', '=', 'jo', true, 0, 'Studenti nuk eshte me korrespondence.'),
                rule_template('active_other_scholarship', '=', 'jo', true, 0, 'Studenti nuk ka burse tjeter aktive.'),
                rule_template('martyr_child', '=', 'po', false, 0, 'Perparesi: femije deshmori.'),
                rule_template('no_income_family', '=', 'po', false, 0, 'Perparesi: familje pa te ardhura dhe pa perkujdesje.'),
                rule_template('martyr_family', '=', 'po', false, 0, 'Perparesi: familje deshmoresh.'),
                rule_template('disabled_war', '=', 'po', false, 0, 'Perparesi: invalid.'),
                rule_template('veteran_war', '=', 'po', false, 0, 'Perparesi: pjesetar i UCK-se.'),
                rule_template('social_category', '=', 'po', false, 0, 'Perparesi: kategori sociale.'),
                rule_template('competition_success', '=', 'po', false, 0, 'Perparesi: suksese ne gara kombetare/nderkombetare ne arsim.'),
                rule_template('monthly_value', '=', '50 EUR', false, 0, '50 euro ne muaj.'),
                rule_template('months', '=', '10', false, 0, '10 muaj.'),
                rule_template('total_value', '=', '500 EUR', false, 0, 'Total 500 euro.'),
            ],
            'documents' => [
                document_template('ID / Leternjoftimi', true, false, 'Obligativ.'),
                document_template('Certifikata e Vendbanimit', true, false, 'Obligative.'),
                document_template('Certifikata e Notave', true, false, 'Obligative.'),
                document_template('Vertetimi i Studentit Aktiv', true, false, 'Obligativ.'),
                document_template('Vertetimi nga Administrata Tatimore e Kosoves', true, false, 'Obligativ.'),
                document_template('Deklarata e Bashkesise Familjare', false, true, 'Opsionale/perparesi.'),
                document_template('Vertetimi per Kategori te Luftes', false, true, 'Opsional/perparesi.'),
                document_template('Vertetimi per Ndihme Sociale', false, true, 'Opsional/perparesi.'),
                document_template('Konfirmimi Bankar', true, false, 'Obligativ.'),
                document_template('Diploma/mirenjohje/certifikata per gara', false, true, 'Opsionale/perparesi.'),
            ],
        ],
        [
            'category' => 'Burse universitare',
            'provider_name' => 'Universiteti Kadri Zeka',
            'title' => 'Bursa Universitare - Universiteti Kadri Zeka 2026',
            'description' => 'Vlera baze: 1000 euro per perfitues. Fondi 3000 euro ndahet proporcionalisht per mesatare mbi 9.5 ne fakultetet tjera ose mbi 9.0 ne Fakultetin e Shkencave Kompjuterike dhe Fakultetin e Shkencave Aplikative.',
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-30',
            'rules' => [
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('university', '=', 'Universiteti Kadri Zeka', true, 0, 'Studenti eshte student i UKZ-se.'),
                rule_template('full_time', '=', 'po', true, 0, 'Studenti eshte i rregullt.'),
                rule_template('study_level', 'in', 'Bachelor,Master', true, 0, 'Niveli Bachelor ose Master.'),
                rule_template('study_year', '>=', '2', true, 0, 'Studenti eshte ne vitin e dyte ose me lart.'),
                rule_template('repeating_year', '=', 'jo', true, 0, 'Studenti nuk eshte perserites.'),
                rule_template('previous_year_exams_completed', '=', 'po', true, 0, 'I ka perfunduar te gjitha provimet e vitit paraprak.'),
                rule_template('bachelor_average_grade', '>=', '8.50', true, 0, 'Bachelor: nota minimale 8.50.'),
                rule_template('master_average_grade', '>=', '9.00', true, 0, 'Master: nota minimale 9.00.'),
                rule_template('faculty_min_grade_social', '>=', '9.00', true, 0, 'Fakulteti i Edukimit, Ekonomik, Juridik dhe Shkencave Sociale: nota minimale 9.00.'),
                rule_template('faculty_min_grade_applied', '>=', '8.50', true, 0, 'Fakulteti i Shkencave Kompjuterike dhe Fakulteti i Shkencave Aplikative: nota minimale 8.50.'),
                rule_template('base_value', '=', '1000 EUR', false, 0, '1000 euro per secilin perfitues.'),
                rule_template('proportional_fund', '=', '3000 EUR', false, 0, 'Fondi shtese 3000 euro ndahet proporcionalisht sipas pragjeve te larta.'),
            ],
            'documents' => basic_university_documents(),
        ],
        [
            'category' => 'Burse universitare',
            'provider_name' => 'Universiteti i Prishtines',
            'title' => 'Bursa Universitare - Universiteti i Prishtines 2026',
            'description' => 'Aplikimi online ne EKosova+ nga 03.03.2026 deri me 17.03.2026, ora 15:30; diten e pare hapet nga ora 13:00. Jo SEMS, jo printim, gjithcka merret nga profili i digjitalizuar.',
            'start_date' => '2026-03-03',
            'end_date' => '2026-03-17',
            'rules' => [
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('university', '=', 'Universiteti Hasan Prishtina', true, 0, 'Studenti eshte student i rregullt i UP-se.'),
                rule_template('full_time', '=', 'po', true, 0, 'Student i rregullt.'),
                rule_template('study_level', '=', 'Bachelor', true, 0, 'Niveli Bachelor.'),
                rule_template('study_year', '>=', '2', true, 0, 'Studenti eshte ne vitin e dyte ose me lart.'),
                rule_template('repeating_year', '=', 'jo', true, 0, 'Studenti nuk ka vit te perseritur.'),
                rule_template('september_exams_completed', '=', 'po', true, 0, 'Te gjitha provimet deri ne afatin e shtatorit 2025/2026.'),
                rule_template('average_grade', '>=', '9.00', true, 0, 'Nota mesatare se paku 9.00.'),
                rule_template('correspondence', '=', 'jo', true, 0, 'Nuk ka status me korrespondence ne vitin akademik 2025/2026.'),
                rule_template('exception_average_grade', '>=', '8.00', false, 0, 'Per fakultetet deficitare mund te aplikohet me note jo me pak se 8.00.'),
                rule_template('exception_units', 'in', 'Fakulteti i Ndertimtarise,Fakulteti i Arkitektures,Fakulteti i Inxhinierise Mekanike,Fakulteti i Inxhinierise Elektrike dhe Kompjuterike,FSHMN Departamenti i Matematikes,FSHMN Departamenti i Fizikes', false, 0, 'Njesite ku vlen perjashtimi i notes.'),
                rule_template('digital_application', '=', 'EKosova+', false, 0, 'Aplikimi behet ne EKosova+, jo ne SEMS; studenti nuk printon asnje flete.'),
            ],
            'documents' => basic_university_documents('Obligativ nese duhet per pagese.'),
        ],
        [
            'category' => 'Burse universitare',
            'provider_name' => 'Universiteti Haxhi Zeka',
            'title' => 'Bursa Universitare - Universiteti Haxhi Zeka 2026',
            'description' => 'EKosova+ hapet prej 17.11.2026 deri me 21.11.2026, ora 16:00. Aplikimi behet ne EKosova+, jo ne SMU; studenti nuk printon flete.',
            'start_date' => '2026-11-17',
            'end_date' => '2026-11-21',
            'rules' => [
                rule_template('id_card_completed', '=', 'po', true, 0, 'ID / Leternjoftimi eshte i plotesuar.'),
                rule_template('university', '=', 'Universiteti Haxhi Zeka', true, 0, 'Studenti eshte student i rregullt i UHZ-se.'),
                rule_template('full_time', '=', 'po', true, 0, 'Student i rregullt.'),
                rule_template('study_level', 'in', 'Bachelor,Master', true, 0, 'Niveli Bachelor ose Master.'),
                rule_template('study_year', '>=', '2', true, 0, 'Studenti eshte ne vitin e dyte ose me lart.'),
                rule_template('repeating_year', '=', 'jo', true, 0, 'Studenti nuk ka vit te perseritur.'),
                rule_template('september_exams_completed', '=', 'po', true, 0, 'Provimet deri ne afatin e shtatorit 2024/2025.'),
                rule_template('average_grade', '>=', '9.00', true, 0, 'Nota mesatare jo me pak se 9.00.'),
                rule_template('results_academic_year', '=', '2025/2026', true, 0, 'Bursat ndahen per rezultatet akademike te vitit akademik 2025/2026.'),
                rule_template('digital_application', '=', 'EKosova+', false, 0, 'Aplikimi behet ne EKosova+, jo ne SMU; gjithcka merret nga profili i digjitalizuar.'),
            ],
            'documents' => basic_university_documents('Obligativ nese duhet per pagese.'),
        ],
    ];
}

function rule_template(string $key, string $operator, string $value, bool $required, int $points, string $description): array
{
    return [
        'rule_key' => $key,
        'operator' => $operator,
        'rule_value' => $value,
        'is_required' => $required ? 1 : 0,
        'points' => $points,
        'description' => $description,
    ];
}

function municipal_template_documents(): array
{
    return [
        document_template('ID / Leternjoftimi', true, false, 'Identiteti dhe numri personal nga profili.'),
        document_template('Certifikata e Vendbanimit', true, false, 'Komuna dhe adresa e vendbanimit.'),
        document_template('Vertetimi i Studentit Aktiv', true, false, 'Statusi aktiv i studentit.'),
        document_template('Certifikata e Notave', true, false, 'Nota mesatare dhe rezultatet akademike.'),
        document_template('Deklarata e Bashkesise Familjare', false, true, 'Pike shtese per gjendje familjare.'),
        document_template('Vertetimi per Ndihme Sociale', false, true, 'Pike/perparesi sociale.'),
        document_template('Vertetimi per Kategori te Luftes', false, true, 'Pike/perparesi per kategori te luftes.'),
        document_template('Certifikata e Vdekjes se Prinderve', false, true, 'Opsionale per pike shtese.'),
    ];
}

function university_template_documents(): array
{
    return [
        document_template('ID / Leternjoftimi', true, false, 'Identiteti i studentit.'),
        document_template('Vertetimi i Studentit Aktiv', true, false, 'Universiteti, fakulteti, programi dhe statusi aktiv.'),
        document_template('Certifikata e Notave', true, false, 'Nota mesatare dhe provimet e perfunduara.'),
        document_template('Deshmi per Drejtime Deficitare', false, true, 'Pike shtese nese programi eshte deficitar.'),
        document_template('Vertetimi per Nevoja te Vecanta', false, true, 'Opsionale per perparesi sipas thirrjes.'),
    ];
}

function deficit_rules(array $programs): array
{
    return [
        rule_template('deficit_programs', 'in', implode(', ', $programs), false, 0, 'Lista e drejtimeve deficitare per kete thirrje.'),
    ];
}

function basic_university_documents(string $bankDescription = 'Obligativ.'): array
{
    return [
        document_template('ID / Leternjoftimi', true, false, 'Obligativ.'),
        document_template('Vertetimi i Studentit Aktiv', true, false, 'Obligativ.'),
        document_template('Certifikata e Notave', true, false, 'Obligative.'),
        document_template('Konfirmimi Bankar', true, false, $bankDescription),
    ];
}

function document_template(string $section, bool $required, bool $bonus, string $description): array
{
    return [
        'document_section_name' => $section,
        'is_required' => $required ? 1 : 0,
        'is_optional_bonus' => $bonus ? 1 : 0,
        'description' => $description,
    ];
}

function save_scholarship_rules(PDO $pdo, int $scholarshipId, mixed $rules): void
{
    if (!is_array($rules)) {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO scholarship_rules (scholarship_id, rule_key, operator, rule_value, is_required, points, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($rules as $rule) {
        if (!is_array($rule) || trim((string) ($rule['rule_key'] ?? '')) === '') {
            continue;
        }
        $stmt->execute([
            $scholarshipId,
            trim((string) $rule['rule_key']),
            trim((string) ($rule['operator'] ?? '=')),
            trim((string) ($rule['rule_value'] ?? '')),
            !empty($rule['is_required']) ? 1 : 0,
            (int) ($rule['points'] ?? 0),
            trim((string) ($rule['description'] ?? '')),
        ]);
    }
}

function save_scholarship_documents(PDO $pdo, int $scholarshipId, mixed $documents): void
{
    if (!is_array($documents)) {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO scholarship_documents (scholarship_id, document_section_name, is_required, is_optional_bonus, description) VALUES (?, ?, ?, ?, ?)');
    foreach ($documents as $document) {
        if (!is_array($document) || trim((string) ($document['document_section_name'] ?? '')) === '') {
            continue;
        }
        $stmt->execute([
            $scholarshipId,
            trim((string) $document['document_section_name']),
            !empty($document['is_required']) ? 1 : 0,
            !empty($document['is_optional_bonus']) ? 1 : 0,
            trim((string) ($document['description'] ?? '')),
        ]);
    }
}

function scholarship_category_value(string $value): string
{
    return in_array($value, ['Burse komunale', 'Burse universitare', 'Burse humanitare nga OJQ'], true) ? $value : '';
}

function admin_scholarship_template_payload(): array
{
    $templates = db()->query('SELECT * FROM scholarship_templates WHERE is_active=1 ORDER BY category, provider_name')->fetchAll();
    $payload = [];

    foreach ($templates as $template) {
        $templateId = (int) $template['id'];
        $ruleStmt = db()->prepare('SELECT rule_key, operator, rule_value, is_required, points, description FROM scholarship_template_rules WHERE template_id=? ORDER BY is_required DESC, id');
        $ruleStmt->execute([$templateId]);
        $documentStmt = db()->prepare('SELECT document_section_name, is_required, is_optional_bonus, description FROM scholarship_template_documents WHERE template_id=? ORDER BY is_required DESC, is_optional_bonus, id');
        $documentStmt->execute([$templateId]);

        $rules = $ruleStmt->fetchAll();
        foreach ($rules as &$rule) {
            $rule['display_info'] = getRuleDisplayInfo((string) ($rule['rule_key'] ?? ''));
        }
        unset($rule);

        $payload[] = [
            'id' => $templateId,
            'category' => $template['category'],
            'provider_name' => $template['provider_name'],
            'title' => $template['title'],
            'description' => $template['description'],
            'start_date' => $template['start_date'],
            'end_date' => $template['end_date'],
            'rules' => $rules,
            'documents' => $documentStmt->fetchAll(),
        ];
    }

    return $payload;
}

function eligible_scholarships_for_student(array $profile): array
{
    ensure_scholarship_template_schema();
    $stmt = db()->query('SELECT s.*, COALESCE(s.provider_name, u.name) provider_name FROM scholarships s LEFT JOIN users u ON u.id=s.provider_id WHERE s.status="active" ORDER BY s.deadline ASC');
    $eligible = [];

    foreach ($stmt->fetchAll() as $scholarship) {
        $match = scholarship_match_for_student($profile, $scholarship);
        if ($match['eligible']) {
            $scholarship['match'] = $match;
            $eligible[] = $scholarship;
        }
    }

    return $eligible;
}

function scholarship_match_for_student(array $profile, array $scholarship): array
{
    $student = student_matching_profile($profile);
    $studentId = (int) ($profile['user_id'] ?? current_user()['id']);
    $rules = scholarship_rules_for_matching((int) $scholarship['id'], $scholarship);
    $fulfilled = [];
    $bonuses = [];
    $debug = [];

    foreach ($rules as $rule) {
        $isRequired = (int) ($rule['is_required'] ?? 0) === 1;
        $override = $isRequired ? evaluate_required_rule_override($student, $rule, $rules) : null;
        $studentValue = getStudentRuleValue($studentId, (string) ($rule['rule_key'] ?? ''), $student);
        $passed = $override ?? evaluateMappedScholarshipRule($studentId, $student, $rule);
        $item = rule_match_report_item($studentValue, $rule, $scholarship, $passed, $isRequired);
        $debug[] = [
            'rule_key' => (string) ($rule['rule_key'] ?? ''),
            'required_value' => (string) ($rule['rule_value'] ?? ''),
            'student_value' => $studentValue,
            'passed' => $passed,
        ];

        if ($isRequired && !$passed) {
            return ['eligible' => false, 'fulfilled' => $fulfilled, 'bonuses' => $bonuses, 'debug' => $debug];
        }

        if ($isRequired) {
            $fulfilled[] = $item;
            continue;
        }

        $bonuses[] = $item;
    }

    return ['eligible' => true, 'fulfilled' => $fulfilled, 'bonuses' => $bonuses, 'debug' => $debug];
}

function evaluate_required_rule_override(array $student, array $rule, array $rules): ?bool
{
    $key = (string) ($rule['rule_key'] ?? '');
    $expected = (string) ($rule['rule_value'] ?? '');
    $average = (float) ($student['average_grade'] ?? 0);

    if ($key === 'average_grade' && normalize_rule_operator((string) ($rule['operator'] ?? '=')) === '>=') {
        $deficitAverage = rule_value_for_key($rules, 'deficit_program_average_grade');
        if ($deficitAverage !== null && student_is_deficit_for_rules($student, $rules) && $average >= (float) $deficitAverage) {
            return true;
        }

        $exceptionAverage = rule_value_for_key($rules, 'exception_average_grade');
        $exceptionUnits = rule_value_for_key($rules, 'exception_units');
        if ($exceptionAverage !== null && $exceptionUnits !== null && student_matches_list_value((string) ($student['faculty'] ?? ''), $exceptionUnits) && $average >= (float) $exceptionAverage) {
            return true;
        }

        return null;
    }

    if ($key === 'deficit_program_average_grade') {
        return !student_is_deficit_for_rules($student, $rules) || $average >= (float) $expected;
    }

    if ($key === 'bachelor_average_grade') {
        return normalize_match_value($student['study_level'] ?? '') !== 'bachelor' || $average >= (float) $expected;
    }

    if ($key === 'master_average_grade') {
        return normalize_match_value($student['study_level'] ?? '') !== 'master' || $average >= (float) $expected;
    }

    if ($key === 'faculty_min_grade_social') {
        $faculties = 'Fakulteti i Edukimit,Fakulteti Ekonomik,Fakulteti Juridik,Fakulteti i Shkencave Sociale';
        return !student_matches_list_value((string) ($student['faculty'] ?? ''), $faculties) || $average >= (float) $expected;
    }

    if ($key === 'faculty_min_grade_applied') {
        $faculties = 'Fakulteti i Shkencave Kompjuterike,Fakulteti i Shkencave Aplikative';
        return !student_matches_list_value((string) ($student['faculty'] ?? ''), $faculties) || $average >= (float) $expected;
    }

    return null;
}

function scholarship_rules_for_matching(int $scholarshipId, array $scholarship): array
{
    try {
        $stmt = db()->prepare('SELECT rule_key, operator, rule_value, is_required, points, description FROM scholarship_rules WHERE scholarship_id=? ORDER BY is_required DESC, id');
        $stmt->execute([$scholarshipId]);
        $rules = $stmt->fetchAll();
        if ($rules) {
            return $rules;
        }
    } catch (Throwable $e) {
        // Fall back to legacy columns below.
    }

    $rules = [rule_template('student_active', '=', 'po', true, 0, 'Studenti eshte aktiv.')];
    if (($scholarship['min_grade'] ?? null) !== null) {
        $rules[] = rule_template('average_grade', '>=', (string) $scholarship['min_grade'], true, 0, 'Nota minimale e kerkuar.');
    }
    if (!empty($scholarship['required_university'])) {
        $rules[] = rule_template('university', '=', (string) $scholarship['required_university'], true, 0, 'Universiteti i kerkuar.');
    }
    if (!empty($scholarship['required_city'])) {
        $rules[] = rule_template('city', '=', (string) $scholarship['required_city'], true, 0, 'Komuna/qyteti i kerkuar.');
    }
    if (!empty($scholarship['required_social_status'])) {
        $rules[] = rule_template('social_status', 'contains', (string) $scholarship['required_social_status'], true, 0, 'Statusi social i kerkuar.');
    }
    foreach ([
        'requires_veteran_child' => ['is_veteran_child', 'Femije veterani'],
        'requires_orphan' => ['is_orphan', 'Jetim'],
        'requires_social_assistance' => ['receives_social_assistance', 'Ndihme sociale'],
    ] as $field => [$key, $label]) {
        if (!empty($scholarship[$field])) {
            $rules[] = rule_template($key, '=', 'po', true, 0, $label);
        }
    }

    return $rules;
}

function student_matching_profile(array $profile): array
{
    $education = decode_previous_education($profile['previous_education'] ?? '');
    $documents = is_array($education['documents'] ?? null) ? $education['documents'] : [];
    $studentDoc = is_array($documents['student_confirmation'] ?? null) ? $documents['student_confirmation'] : [];
    $gradeDoc = is_array($documents['grade_certificate'] ?? null) ? $documents['grade_certificate'] : [];
    $taxDoc = is_array($documents['tax_confirmation'] ?? null) ? $documents['tax_confirmation'] : [];
    $familyDoc = is_array($documents['family_declaration'] ?? null) ? $documents['family_declaration'] : [];
    $warDoc = is_array($documents['war_category_confirmation'] ?? null) ? $documents['war_category_confirmation'] : [];
    $deathDoc = is_array($documents['parent_death_certificate'] ?? null) ? $documents['parent_death_certificate'] : [];
    $specialDoc = is_array($documents['special_needs_confirmation'] ?? null) ? $documents['special_needs_confirmation'] : [];
    $deficitDoc = is_array($documents['deficit_program_evidence'] ?? null) ? $documents['deficit_program_evidence'] : [];
    $meta = is_array($education['studies']['student_meta'] ?? null) ? $education['studies']['student_meta'] : [];
    $program = (string) ($studentDoc['program'] ?? $meta['program'] ?? $deficitDoc['study_field'] ?? '');
    $studyLevel = (string) ($studentDoc['study_level'] ?? $meta['study_level'] ?? '');
    $studyYear = (int) ($studentDoc['study_year'] ?? $meta['study_year'] ?? 1);
    $nameParts = explode(' ', trim((string) ($profile['name'] ?? '')), 2);
    $firstName = (string) (($profile['first_name'] ?? '') ?: ($nameParts[0] ?? ''));
    $lastName = (string) (($profile['last_name'] ?? '') ?: ($nameParts[1] ?? ''));
    $isWarCategory = array_has_yes($warDoc) || !empty($profile['is_veteran_child']);
    $oneParentMissing = yes_value((string) ($deathDoc['one_parent_missing'] ?? '')) || !empty($profile['is_orphan']);
    $twoParentsMissing = yes_value((string) ($deathDoc['two_parents_missing'] ?? ''));

    return [
        'id_card_completed' => filled($firstName) && filled($lastName ?: $firstName) && filled($profile['personal_number'] ?? '') ? 'po' : 'jo',
        'city' => (string) ($profile['city'] ?? ''),
        'residence_municipality' => (string) (($documents['residence_certificate']['municipality'] ?? '') ?: ($profile['city'] ?? '')),
        'university' => (string) ($profile['university'] ?? ''),
        'faculty' => (string) ($studentDoc['faculty'] ?? $meta['faculty'] ?? ''),
        'program' => $program,
        'field_of_study' => $program,
        'study_field' => $program,
        'study_level' => $studyLevel,
        'study_year' => $studyYear,
        'average_grade' => (float) ($gradeDoc['average_grade'] ?? $profile['average_grade'] ?? 0),
        'student_active' => !empty($profile['student_active']) ? 'po' : 'jo',
        'active_status' => !empty($profile['student_active']) ? 'po' : 'jo',
        'full_time' => (string) ($studentDoc['full_time'] ?? 'po'),
        'correspondence' => (string) ($studentDoc['correspondence'] ?? 'jo'),
        'self_financing' => (string) ($studentDoc['self_financing'] ?? 'jo'),
        'repeating_year' => (string) ($studentDoc['repeating_year'] ?? 'jo'),
        'public_university' => (string) ($studentDoc['public_university'] ?? (education_has_option($education, 'studies.student_meta.institution_type', 'Universitet Publik') ? 'po' : 'po')),
        'private_university' => (string) ($studentDoc['private_university'] ?? 'jo'),
        'commercial_student' => (string) ($studentDoc['commercial_student'] ?? 'jo'),
        'previous_year_exams_completed' => (string) ($gradeDoc['previous_year_exams_completed'] ?? 'po'),
        'september_exams_completed' => (string) ($gradeDoc['september_exams_completed'] ?? 'po'),
        'results_academic_year' => (string) ($gradeDoc['results_academic_year'] ?? ''),
        'academic_year_registered' => (string) ($gradeDoc['academic_year_registered'] ?? 'po'),
        'lost_year' => (string) ($gradeDoc['lost_year'] ?? 'jo'),
        'student_employed' => (string) ($taxDoc['student_employed'] ?? (($profile['employment_status'] ?? '') === 'I punesuar' ? 'po' : 'jo')),
        'active_worker' => (string) ($taxDoc['active_worker'] ?? (($profile['employment_status'] ?? '') === 'I punesuar' ? 'po' : 'jo')),
        'self_financing_or_active_worker' => yes_value((string) ($studentDoc['self_financing'] ?? 'jo')) || (($profile['employment_status'] ?? '') === 'I punesuar') ? 'po' : 'jo',
        'receives_social_assistance' => !empty($profile['receives_social_assistance']) ? 'po' : 'jo',
        'social_status' => (string) ($profile['social_status'] ?? ''),
        'is_veteran_child' => !empty($profile['is_veteran_child']) ? 'po' : 'jo',
        'veteran_child' => !empty($profile['is_veteran_child']) ? 'po' : 'jo',
        'is_orphan' => !empty($profile['is_orphan']) ? 'po' : 'jo',
        'war_category' => $isWarCategory ? 'po' : 'jo',
        'war_family' => $isWarCategory ? 'po' : 'jo',
        'martyr_child' => (string) ($warDoc['martyr_child'] ?? 'jo'),
        'martyr_family' => (string) ($warDoc['martyr_family'] ?? 'jo'),
        'disabled_war' => (string) ($warDoc['disabled_war'] ?? 'jo'),
        'veteran_war' => (string) ($warDoc['veteran_war'] ?? (!empty($profile['is_veteran_child']) ? 'po' : 'jo')),
        'one_parent_missing' => $oneParentMissing ? 'po' : 'jo',
        'two_parents_missing' => $twoParentsMissing ? 'po' : 'jo',
        'missing_one_parent' => $oneParentMissing ? 'po' : 'jo',
        'missing_both_parents' => $twoParentsMissing ? 'po' : 'jo',
        'parent_missing' => $oneParentMissing || $twoParentsMissing ? 'po' : 'jo',
        'parents_alive' => $oneParentMissing || $twoParentsMissing ? 'jo' : 'po',
        'special_needs' => (string) ($specialDoc['special_needs'] ?? 'jo'),
        'is_deficit_program' => (string) ($deficitDoc['is_deficit'] ?? 'jo'),
        'deficit_field' => (string) ($deficitDoc['is_deficit'] ?? 'jo'),
        'family_students_count' => (int) ($familyDoc['family_students_count'] ?? 1),
        'active_other_scholarship' => student_has_active_other_scholarship((int) ($profile['user_id'] ?? current_user()['id'])) ? 'po' : 'jo',
        'active_scholarship_exists' => student_has_active_other_scholarship((int) ($profile['user_id'] ?? current_user()['id'])) ? 'po' : 'jo',
        'study_country' => 'Kosove',
        'study_location' => 'Kosove',
        'bachelor_average_grade' => strtolower($studyLevel) === 'bachelor' ? (float) ($profile['average_grade'] ?? 0) : 0,
        'master_average_grade' => strtolower($studyLevel) === 'master' ? (float) ($profile['average_grade'] ?? 0) : 0,
        'bank_completed' => filled($profile['bank_name'] ?? '') && filled($profile['bank_iban'] ?? '') ? 'po' : 'jo',
        'bank_confirmed' => filled($profile['bank_name'] ?? '') && filled($profile['bank_iban'] ?? '') ? 'po' : 'jo',
        'is_final_year' => $studyYear >= 3 ? 'po' : 'jo',
        'competition_awards' => (string) ($documents['competition_awards']['has_awards'] ?? 'jo'),
    ];
}

function evaluate_scholarship_rule(array $student, array $rule): bool
{
    $key = (string) ($rule['rule_key'] ?? '');
    $operator = normalize_rule_operator((string) ($rule['operator'] ?? '='));
    $expected = (string) ($rule['rule_value'] ?? '');

    if ($key === 'deficit_programs') {
        return in_list_value((string) ($student['program'] ?? ''), $expected);
    }
    if (in_array($key, ['first_year_university', 'public_university_after_first_year'], true)) {
        return evaluate_conditional_student_rule($student, $key, $expected);
    }
    if (in_array($operator, ['score_base', 'desc'], true)) {
        return filled($student[$key] ?? '');
    }
    if (in_array($key, ['bonus_points_limit', 'monthly_value', 'months', 'total_value', 'digital_application', 'exception_units'], true)) {
        return true;
    }

    $actual = $student[$key] ?? '';
    return evaluateRule($actual, $operator, $expected);
}

function evaluateMappedScholarshipRule(int $studentId, array $student, array $rule): bool
{
    $key = (string) ($rule['rule_key'] ?? '');
    if ($key === 'deficit_programs' || in_array($key, ['first_year_university', 'public_university_after_first_year'], true)) {
        return evaluate_scholarship_rule($student, $rule);
    }

    $studentValue = getStudentRuleValue($studentId, $key, $student);
    return evaluateRule($studentValue, (string) ($rule['operator'] ?? '='), (string) ($rule['rule_value'] ?? ''));
}

function getStudentRuleValue(int $studentId, string $ruleKey, ?array $student = null): mixed
{
    if ($student === null) {
        $stmt = db()->prepare('SELECT sp.*, u.name FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE sp.user_id=?');
        $stmt->execute([$studentId]);
        $profile = $stmt->fetch() ?: [];
        $student = student_matching_profile($profile);
    }

    $canonical = rule_key_alias($ruleKey);
    if ($canonical === 'id_card_completed' && (($student[$canonical] ?? '') === 'jo' || ($student[$canonical] ?? '') === '')) {
        $stmt = db()->prepare('SELECT sp.personal_number, sp.first_name, sp.last_name, u.name FROM student_profiles sp JOIN users u ON u.id=sp.user_id WHERE sp.user_id=?');
        $stmt->execute([$studentId]);
        $profile = $stmt->fetch() ?: [];
        $name = trim((string) ($profile['name'] ?? ''));
        return filled($profile['personal_number'] ?? '') && (filled($profile['first_name'] ?? '') || filled($profile['last_name'] ?? '') || $name !== '') ? 'po' : 'jo';
    }
    if ($canonical === 'active_other_scholarship') {
        return student_has_active_other_scholarship($studentId) ? 'po' : 'jo';
    }

    return $student[$canonical] ?? '';
}

function evaluateRule(mixed $studentValue, string $operator, mixed $ruleValue): bool
{
    return compare_rule_values($studentValue, normalize_rule_operator($operator), (string) $ruleValue);
}

function getRuleDisplayInfo(string $ruleKey): array
{
    $map = rule_display_map();
    $canonical = rule_key_alias($ruleKey);
    $info = $map[$canonical] ?? [
        'label' => humanize_rule_key($ruleKey),
        'document_section' => 'Profili i studentit',
        'human_description' => humanize_rule_key($ruleKey),
    ];

    $info['rule_key'] = $ruleKey;
    return $info;
}

function calculateOptionalPoints(int $studentId, int $scholarshipId): array
{
    $stmt = db()->prepare('SELECT rule_key, operator, rule_value, is_required, points, description FROM scholarship_rules WHERE scholarship_id=? AND is_required=0 ORDER BY id');
    $stmt->execute([$scholarshipId]);
    $items = [];
    $total = 0;

    foreach ($stmt->fetchAll() as $rule) {
        $studentValue = getStudentRuleValue($studentId, (string) $rule['rule_key']);
        $passed = evaluateRule($studentValue, (string) $rule['operator'], (string) $rule['rule_value']);
        $points = $passed ? (int) ($rule['points'] ?? 0) : 0;
        $total += $points;
        $items[] = rule_match_report_item($studentValue, $rule, [], $passed, false);
    }

    return ['total' => $total, 'items' => $items];
}

function rule_match_report_item(mixed $studentValue, array $rule, array $scholarship, bool $passed, bool $isRequired): array
{
    $info = getRuleDisplayInfo((string) ($rule['rule_key'] ?? ''));
    $points = (int) ($rule['points'] ?? 0);
    $pointsAwarded = !$isRequired && $passed ? $points : 0;

    return [
        'name' => (string) ($rule['rule_key'] ?? ''),
        'rule_key' => (string) ($rule['rule_key'] ?? ''),
        'label' => $info['label'],
        'document_section' => $info['document_section'],
        'criterion' => $info['human_description'],
        'passed' => $passed,
        'details' => (string) ($rule['description'] ?? $info['human_description']),
        'institution' => (string) ($scholarship['provider_name'] ?? 'EKosova+'),
        'operator' => (string) ($rule['operator'] ?? '='),
        'value' => (string) ($rule['rule_value'] ?? ''),
        'required_value' => readable_rule_value((string) ($rule['rule_value'] ?? '')),
        'student_value' => readable_rule_value($studentValue),
        'status_text' => $isRequired
            ? ($passed ? 'Plotësohet' : 'Nuk plotësohet')
            : ($passed ? 'Përfitohet bonus' : 'Nuk përfitohet bonus'),
        'points' => $points,
        'points_awarded' => $pointsAwarded,
    ];
}

function rule_key_alias(string $ruleKey): string
{
    return [
        'bank_confirmed' => 'bank_completed',
        'has_other_scholarship' => 'active_other_scholarship',
        'active_scholarship_exists' => 'active_other_scholarship',
        'social_assistance' => 'receives_social_assistance',
        'deficit_field' => 'is_deficit_program',
        'field_of_study' => 'program',
        'veteran_child' => 'is_veteran_child',
        'war_invalid' => 'disabled_war',
        'war_veteran' => 'veteran_war',
        'missing_one_parent' => 'one_parent_missing',
        'missing_both_parents' => 'two_parents_missing',
        'last_year_student' => 'is_final_year',
        'competition_awards' => 'competition_awards',
    ][$ruleKey] ?? $ruleKey;
}

function rule_display_map(): array
{
    return [
        'id_card_completed' => ['label' => 'ID / Letërnjoftimi', 'document_section' => 'ID / Letërnjoftimi', 'human_description' => 'ID / Letërnjoftimi është i plotësuar'],
        'residence_municipality' => ['label' => 'Certifikata e Vendbanimit', 'document_section' => 'Certifikata e Vendbanimit', 'human_description' => 'Komuna e vendbanimit'],
        'city' => ['label' => 'Certifikata e Vendbanimit', 'document_section' => 'Certifikata e Vendbanimit', 'human_description' => 'Komuna/qyteti i vendbanimit'],
        'full_time' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Student i rregullt'],
        'student_active' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Student aktiv'],
        'repeating_year' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Përsëritës i vitit'],
        'study_level' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Niveli i studimeve'],
        'study_year' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Viti i studimit'],
        'university' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Universiteti'],
        'faculty' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Fakulteti'],
        'public_university' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Universitet publik'],
        'correspondence' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Student me korrespondencë'],
        'self_financing' => ['label' => 'Vërtetimi i Studentit Aktiv', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Vetëfinancim'],
        'average_grade' => ['label' => 'Certifikata e Notave', 'document_section' => 'Certifikata e Notave', 'human_description' => 'Nota mesatare'],
        'previous_year_exams_completed' => ['label' => 'Certifikata e Notave', 'document_section' => 'Certifikata e Notave', 'human_description' => 'Provimet e vitit paraprak të përfunduara'],
        'september_exams_completed' => ['label' => 'Certifikata e Notave', 'document_section' => 'Certifikata e Notave', 'human_description' => 'Provimet e përfunduara deri në afatin e shtatorit'],
        'bank_completed' => ['label' => 'Konfirmimi Bankar', 'document_section' => 'Konfirmimi Bankar', 'human_description' => 'Llogaria bankare e konfirmuar'],
        'student_employed' => ['label' => 'Vërtetimi nga ATK', 'document_section' => 'Vërtetimi nga Administrata Tatimore e Kosovës', 'human_description' => 'Studenti i punësuar'],
        'active_worker' => ['label' => 'Vërtetimi nga ATK', 'document_section' => 'Vërtetimi nga Administrata Tatimore e Kosovës', 'human_description' => 'Punëtor aktiv'],
        'active_other_scholarship' => ['label' => 'Regjistri i Bursave', 'document_section' => 'Regjistri i Bursave', 'human_description' => 'Ka bursë tjetër aktive'],
        'family_students_count' => ['label' => 'Numri i studentëve në familje', 'document_section' => 'Deklarata e Bashkësisë Familjare', 'human_description' => 'Numri i studentëve në familje'],
        'war_category' => ['label' => 'Kategori e dalë nga lufta', 'document_section' => 'Vërtetimi për Kategori të Luftës', 'human_description' => 'Kategori e dalë nga lufta'],
        'is_veteran_child' => ['label' => 'Fëmijë veterani', 'document_section' => 'Vërtetimi për Kategori të Luftës', 'human_description' => 'Fëmijë veterani'],
        'martyr_child' => ['label' => 'Fëmijë dëshmori', 'document_section' => 'Vërtetimi për Kategori të Luftës', 'human_description' => 'Fëmijë dëshmori'],
        'receives_social_assistance' => ['label' => 'Përfitues i ndihmës sociale', 'document_section' => 'Vërtetimi për Ndihmë Sociale', 'human_description' => 'Përfitues i ndihmës sociale'],
        'is_deficit_program' => ['label' => 'Drejtim deficitar', 'document_section' => 'Dëshmi për Drejtime Deficitare', 'human_description' => 'Drejtim deficitar'],
        'special_needs' => ['label' => 'Student me nevoja të veçanta', 'document_section' => 'Vërtetimi për Nevoja të Veçanta', 'human_description' => 'Student me nevoja të veçanta'],
        'one_parent_missing' => ['label' => 'Pa njërin prind', 'document_section' => 'Certifikata e Vdekjes së Prindërve', 'human_description' => 'Pa njërin prind'],
        'two_parents_missing' => ['label' => 'Pa dy prindër', 'document_section' => 'Certifikata e Vdekjes së Prindërve', 'human_description' => 'Pa dy prindër'],
        'is_final_year' => ['label' => 'Student i vitit të fundit', 'document_section' => 'Vërtetimi i Studentit Aktiv', 'human_description' => 'Student i vitit të fundit'],
        'competition_awards' => ['label' => 'Suksese në gara', 'document_section' => 'Diploma/Mirënjohje/Certifikata për gara', 'human_description' => 'Suksese në gara'],
    ];
}

function readable_rule_value(mixed $value): string
{
    if (is_bool($value)) {
        return $value ? 'Po' : 'Jo';
    }

    $text = trim((string) $value);
    return match (normalize_match_value($text)) {
        'po' => 'Po',
        'jo' => 'Jo',
        '' => 'Nuk është plotësuar',
        default => $text,
    };
}

function humanize_rule_key(string $ruleKey): string
{
    return ucfirst(str_replace('_', ' ', $ruleKey));
}

function rule_value_for_key(array $rules, string $key): ?string
{
    foreach ($rules as $rule) {
        if (($rule['rule_key'] ?? '') === $key) {
            return (string) ($rule['rule_value'] ?? '');
        }
    }

    return null;
}

function student_is_deficit_for_rules(array $student, array $rules): bool
{
    if (normalize_match_value($student['is_deficit_program'] ?? '') === 'po') {
        return true;
    }

    foreach ($rules as $rule) {
        if (($rule['rule_key'] ?? '') === 'deficit_programs' && student_matches_list_value((string) ($student['program'] ?? ''), (string) ($rule['rule_value'] ?? ''))) {
            return true;
        }
    }

    return false;
}

function student_matches_list_value(string $actual, string $expectedList): bool
{
    if (in_list_value($actual, $expectedList)) {
        return true;
    }

    $actual = normalize_match_value($actual);
    if ($actual === '') {
        return false;
    }

    foreach (preg_split('/[,;]+/', $expectedList) ?: [] as $item) {
        $item = normalize_match_value($item);
        if ($item !== '' && ($actual === $item || str_contains($actual, $item) || str_contains($item, $actual))) {
            return true;
        }
    }

    return false;
}

function evaluate_conditional_student_rule(array $student, string $key, string $expected): bool
{
    $year = (int) ($student['study_year'] ?? 1);
    if ($key === 'first_year_university') {
        return $year !== 1 || compare_rule_values($student['university'] ?? '', '=', $expected);
    }
    if ($key === 'public_university_after_first_year') {
        return $year < 2 || compare_rule_values($student['public_university'] ?? '', '=', 'po');
    }
    return true;
}

function compare_rule_values(mixed $actual, string $operator, string $expected): bool
{
    return match ($operator) {
        '=', 'equals', 'boolean_true' => normalize_match_value($actual) === normalize_match_value($expected === '' && $operator === 'boolean_true' ? 'po' : $expected),
        '!=', 'not_equals', 'boolean_false' => normalize_match_value($actual) !== normalize_match_value($expected === '' && $operator === 'boolean_false' ? 'po' : $expected),
        '>=', 'greater_or_equal' => (float) $actual >= (float) $expected,
        '<=', 'less_or_equal' => (float) $actual <= (float) $expected,
        '>', 'greater' => (float) $actual > (float) $expected,
        '<', 'less' => (float) $actual < (float) $expected,
        'in', 'in_list' => in_list_value((string) $actual, $expected),
        'not_in' => !in_list_value((string) $actual, $expected),
        'contains' => stripos((string) $actual, $expected) !== false,
        'between' => between_rule_value((float) $actual, $expected),
        default => normalize_match_value($actual) === normalize_match_value($expected),
    };
}

function normalize_rule_operator(string $operator): string
{
    return match ($operator) {
        'equals' => '=',
        'not_equals' => '!=',
        'greater_or_equal' => '>=',
        'less_or_equal' => '<=',
        'in_list' => 'in',
        'not_in_list' => 'not_in',
        default => $operator,
    };
}

function normalize_match_value(mixed $value): string
{
    $text = strtolower(trim((string) $value));
    return match ($text) {
        'yes', 'true', '1' => 'po',
        'no', 'false', '0' => 'jo',
        default => $text,
    };
}

function in_list_value(string $actual, string $expectedList): bool
{
    $actual = normalize_match_value($actual);
    $items = array_map('normalize_match_value', preg_split('/[,;]+/', $expectedList) ?: []);
    return in_array($actual, $items, true);
}

function between_rule_value(float $actual, string $range): bool
{
    if (!preg_match('/^\s*([0-9.]+)\s*-\s*([0-9.]+)\s*$/', $range, $matches)) {
        return false;
    }
    return $actual >= (float) $matches[1] && $actual <= (float) $matches[2];
}

function student_has_active_other_scholarship(int $studentId): bool
{
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM applications WHERE student_id=? AND status IN ("fituar", "approved")');
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function array_has_yes(array $items): bool
{
    foreach ($items as $value) {
        if (is_array($value) && array_has_yes($value)) {
            return true;
        }
        if (!is_array($value) && yes_value((string) $value)) {
            return true;
        }
    }
    return false;
}

function filled(mixed $value): bool
{
    return trim((string) $value) !== '';
}

function action_apply(): void
{
    require_role(['student']);
    ensure_scholarship_template_schema();
    $scholarshipId = (int) ($_POST['scholarship_id'] ?? 0);

    $stmt = db()->prepare('SELECT * FROM student_profiles WHERE user_id = ?');
    $stmt->execute([current_user()['id']]);
    $student = $stmt->fetch();

    $stmt = db()->prepare('SELECT * FROM scholarships WHERE id = ? AND status = "active"');
    $stmt->execute([$scholarshipId]);
    $scholarship = $stmt->fetch();

    if (!$student || !$scholarship) {
        flash(t('scholarship_or_profile_missing'), 'error');
        redirect('scholarships');
    }

    $match = scholarship_match_for_student($student, $scholarship);
    if (!$match['eligible']) {
        flash('Kjo bursë nuk përputhet me profilin tuaj aktual.', 'error');
        redirect('dashboard');
    }

    $stmt = db()->prepare('SELECT id FROM applications WHERE student_id = ? AND scholarship_id = ?');
    $stmt->execute([current_user()['id'], $scholarshipId]);
    $existing = $stmt->fetch();
    if ($existing) {
        flash('Ju tashmë keni aplikuar për këtë bursë.', 'error');
        redirect('dashboard');
    }

    $status = 'fituar';
    $verificationJson = json_encode([
        'required' => $match['fulfilled'],
        'optional' => $match['bonuses'],
        'debug' => $match['debug'] ?? [],
    ], JSON_UNESCAPED_UNICODE);
    $pointsTotal = array_sum(array_map(fn($bonus) => (int) ($bonus['points_awarded'] ?? 0), $match['bonuses']));
    $resultMessage = 'Urime! Ju keni fituar bursën.';

    $stmt = db()->prepare('INSERT INTO applications (student_id, scholarship_id, status, verification_json, applied_at, points_total, result_message) VALUES (?, ?, ?, ?, NOW(), ?, ?)');
    $stmt->execute([current_user()['id'], $scholarshipId, $status, $verificationJson, $pointsTotal, $resultMessage]);
    $applicationId = (int) db()->lastInsertId();

    $_SESSION['delayed_application_result_id'] = $applicationId;
    redirect('dashboard');
}

function action_complaint(): void
{
    require_role(['student']);
    ensure_scholarship_template_schema();
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $category = trim($_POST['scholarship_category'] ?? '');
    $provider = trim($_POST['provider_name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($category === '' || $provider === '' || $message === '' || $reason === '') {
        flash(t('complaint_reason_required'), 'error');
        redirect('complaint');
    }

    $stmt = db()->prepare('INSERT INTO complaints (application_id, student_id, scholarship_category, provider_name, message, reason, status) VALUES (?, ?, ?, ?, ?, ?, "pending")');
    $stmt->execute([$applicationId > 0 ? $applicationId : null, current_user()['id'], $category, $provider, $message, $reason]);
    flash(t('complaint_sent'));
    redirect('dashboard');
}

function application_report(int $applicationId, int $studentId): ?array
{
    $stmt = db()->prepare('SELECT a.*, s.title, s.category, s.id scholarship_id, COALESCE(s.provider_name, u.name) provider_name FROM applications a JOIN scholarships s ON s.id=a.scholarship_id LEFT JOIN users u ON u.id=s.provider_id WHERE a.id=? AND a.student_id=?');
    $stmt->execute([$applicationId, $studentId]);
    $application = $stmt->fetch();

    return $application ?: null;
}

function scholarship_document_sections(int $scholarshipId): array
{
    try {
        $stmt = db()->prepare('SELECT document_section_name FROM scholarship_documents WHERE scholarship_id=? ORDER BY is_required DESC, id');
        $stmt->execute([$scholarshipId]);
        $sections = array_map(fn($row) => (string) $row['document_section_name'], $stmt->fetchAll());
        return $sections ?: ['ID / Letërnjoftimi', 'Vërtetimi i Studentit Aktiv', 'Certifikata e Notave'];
    } catch (Throwable $e) {
        return ['ID / Letërnjoftimi', 'Vërtetimi i Studentit Aktiv', 'Certifikata e Notave'];
    }
}

function action_update_profile(): void
{
    require_role(['student']);

    $documentSections = array_keys(student_document_section_definitions(current_student_profile()));
    $section = allowed_value($_POST['section_name'] ?? '', array_merge(['personal', 'education', 'courses', 'crafts', 'student', 'bank'], $documentSections), '');
    if ($section === '') {
        flash(t('invalid_section'), 'error');
        redirect('profile&edit=1');
    }

    try {
        if (in_array($section, $documentSections, true)) {
            update_profile_document_section($section);
        } else {
            match ($section) {
                'personal' => update_profile_personal_section(),
                'education' => update_profile_previous_education_section('schools'),
                'courses' => update_profile_previous_education_section('courses'),
                'crafts' => update_profile_previous_education_section('crafts'),
                'student' => update_profile_student_section(),
                'bank' => update_profile_bank_section(),
            };
        }

        refresh_session_user((int) current_user()['id']);
        popup_flash(section_success_message($section));
        redirect('profile&edit=1&saved_section=' . rawurlencode($section));
    } catch (Throwable $e) {
        flash(t('error_saving'), 'error');
        redirect('profile&edit=1&open_section=' . rawurlencode($section));
    }
}

function update_profile_personal_section(): void
{
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Invalid personal fields');
    }

    $birthDate = trim($_POST['birth_date'] ?? '');
    $birthDate = $birthDate !== '' ? $birthDate : null;
    $gender = allowed_value($_POST['gender'] ?? '', ['Mashkull', 'Femer', 'Tjeter'], 'Tjeter');
    $employmentStatus = allowed_value($_POST['employment_status'] ?? '', ['I punesuar', 'I papune'], 'I papune');
    $annualCirculation = max(0, (float) ($_POST['annual_circulation'] ?? 0));

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$firstName . ' ' . $lastName, $email, current_user()['id']]);

        $stmt = $pdo->prepare('UPDATE student_profiles SET first_name=?, last_name=?, birth_date=?, birth_place=?, residence=?, gender=?, annual_circulation=?, employment_status=? WHERE user_id=?');
        $stmt->execute([
            $firstName,
            $lastName,
            $birthDate,
            trim($_POST['birth_place'] ?? ''),
            trim($_POST['residence'] ?? ''),
            $gender,
            $annualCirculation,
            $employmentStatus,
            current_user()['id'],
        ]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function update_profile_previous_education_section(string $key): void
{
    $profile = current_student_profile();
    $education = decode_previous_education($profile['previous_education'] ?? '');
    $sectionData = $_POST['previous_education'][$key] ?? [];
    if (is_array($sectionData)) {
        validate_year_ranges($sectionData);
    }
    $education[$key] = is_array($sectionData) ? $sectionData : [];

    $stmt = db()->prepare('UPDATE student_profiles SET previous_education=? WHERE user_id=?');
    $stmt->execute([encode_previous_education($education), current_user()['id']]);
}

function update_profile_student_section(): void
{
    $averageGrade = (float) ($_POST['average_grade'] ?? 0);
    if ($averageGrade < 6 || $averageGrade > 10) {
        throw new RuntimeException('Invalid average grade');
    }

    $profile = current_student_profile();
    $education = decode_previous_education($profile['previous_education'] ?? '');
    $studiesData = $_POST['previous_education']['studies'] ?? [];
    if (is_array($studiesData)) {
        validate_year_ranges($studiesData);
    }
    $education['studies'] = is_array($studiesData) ? $studiesData : [];

    $stmt = db()->prepare('UPDATE student_profiles SET university=?, city=?, average_grade=?, social_status=?, student_active=?, is_veteran_child=?, is_orphan=?, receives_social_assistance=?, previous_education=? WHERE user_id=?');
    $stmt->execute([
        trim($_POST['university'] ?? ''),
        trim($_POST['city'] ?? ''),
        $averageGrade,
        social_status_from_flags(),
        isset($_POST['student_active']) ? 1 : 0,
        isset($_POST['is_veteran_child']) ? 1 : 0,
        isset($_POST['is_orphan']) ? 1 : 0,
        isset($_POST['receives_social_assistance']) ? 1 : 0,
        encode_previous_education($education),
        current_user()['id'],
    ]);
}

function update_profile_bank_section(): void
{
    $stmt = db()->prepare('UPDATE student_profiles SET bank_name=?, bank_account_holder=?, bank_account_number=?, bank_iban=?, bank_branch=? WHERE user_id=?');
    $stmt->execute([
        trim($_POST['bank_name'] ?? ''),
        trim($_POST['bank_account_holder'] ?? ''),
        trim($_POST['bank_account_number'] ?? ''),
        trim($_POST['bank_iban'] ?? ''),
        encode_bank_card_metadata($_POST),
        current_user()['id'],
    ]);
}

function update_profile_document_section(string $section): void
{
    $profile = current_student_profile();
    $education = decode_previous_education($profile['previous_education'] ?? '');
    $education['documents'] ??= [];
    $posted = $_POST['document_data'] ?? [];
    $education['documents'][$section] = is_array($posted) ? normalize_document_data($posted) : [];

    $data = $education['documents'][$section];
    $pdo = db();
    $pdo->beginTransaction();
    try {
        match ($section) {
            'id_card' => update_document_identity($pdo, $data),
            'residence_certificate' => update_document_residence($pdo, $data),
            'student_confirmation' => update_document_student($pdo, $data, $education),
            'grade_certificate' => update_document_grades($pdo, $data, $education),
            'family_declaration' => update_document_family($pdo, $data, $education),
            'bank_confirmation' => update_document_bank($pdo, $data),
            'tax_confirmation' => update_document_tax($pdo, $data, $education),
            'social_assistance_confirmation' => update_document_social($pdo, $data, $education),
            'war_category_confirmation' => update_document_war($pdo, $data, $education),
            'parent_death_certificate' => update_document_parent_death($pdo, $data, $education),
            'special_needs_confirmation', 'deficit_program_evidence' => update_document_json_only($pdo, $education),
            default => throw new RuntimeException('Invalid document section'),
        };
        update_document_json_only($pdo, $education);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function normalize_document_data(array $data): array
{
    $normalized = [];
    foreach ($data as $key => $value) {
        $normalized[$key] = is_array($value) ? normalize_document_data($value) : trim((string) $value);
    }
    return $normalized;
}

function update_document_identity(PDO $pdo, array $data): void
{
    $firstName = $data['first_name'] ?? '';
    $lastName = $data['last_name'] ?? '';
    if ($firstName === '' || $lastName === '') {
        throw new RuntimeException('Invalid identity');
    }

    $stmt = $pdo->prepare('UPDATE users SET name=? WHERE id=?');
    $stmt->execute([$firstName . ' ' . $lastName, current_user()['id']]);

    $stmt = $pdo->prepare('UPDATE student_profiles SET first_name=?, last_name=?, personal_number=?, birth_date=?, gender=?, city=? WHERE user_id=?');
    $stmt->execute([
        $firstName,
        $lastName,
        $data['personal_number'] ?? '',
        ($data['birth_date'] ?? '') ?: null,
        allowed_value($data['gender'] ?? '', ['Mashkull', 'Femer', 'Tjeter'], 'Tjeter'),
        $data['municipality'] ?? '',
        current_user()['id'],
    ]);
}

function update_document_residence(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET city=?, residence=? WHERE user_id=?');
    $stmt->execute([$data['municipality'] ?? '', $data['address'] ?? '', current_user()['id']]);
}

function update_document_student(PDO $pdo, array $data, array &$education): void
{
    $education['studies']['student_meta']['study_level'] = $data['study_level'] ?? '';
    $education['studies']['student_meta']['study_year'] = $data['study_year'] ?? '';
    $education['studies']['student_meta']['program'] = $data['program'] ?? '';
    $education['studies']['student_meta']['faculty'] = $data['faculty'] ?? '';
    $education['studies']['student_meta']['institution_type'] = yes_value($data['public_university'] ?? '') ? ['Universitet Publik'] : [];
    $stmt = $pdo->prepare('UPDATE student_profiles SET university=?, student_active=?, previous_education=? WHERE user_id=?');
    $stmt->execute([
        $data['university'] ?? '',
        yes_value($data['active_status'] ?? '') ? 1 : 0,
        encode_previous_education($education),
        current_user()['id'],
    ]);
}

function update_document_grades(PDO $pdo, array $data, array $education): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET average_grade=?, previous_education=? WHERE user_id=?');
    $stmt->execute([
        max(0, (float) ($data['average_grade'] ?? 0)),
        encode_previous_education($education),
        current_user()['id'],
    ]);
}

function update_document_family(PDO $pdo, array $data, array $education): void
{
    update_document_json_only($pdo, $education);
}

function update_document_bank(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET bank_name=?, bank_account_holder=?, bank_account_number=?, bank_iban=?, bank_branch=? WHERE user_id=?');
    $stmt->execute([
        $data['bank'] ?? '',
        $data['account_holder'] ?? '',
        $data['account_number'] ?? '',
        $data['iban'] ?? '',
        $data['branch'] ?? '',
        current_user()['id'],
    ]);
}

function update_document_tax(PDO $pdo, array $data, array $education): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET employment_status=?, previous_education=? WHERE user_id=?');
    $stmt->execute([
        yes_value($data['student_employed'] ?? '') || yes_value($data['active_worker'] ?? '') ? 'I punesuar' : 'I papune',
        encode_previous_education($education),
        current_user()['id'],
    ]);
}

function update_document_social(PDO $pdo, array $data, array $education): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET receives_social_assistance=?, social_status=?, previous_education=? WHERE user_id=?');
    $receives = yes_value($data['receives_social_assistance'] ?? '');
    $stmt->execute([$receives ? 1 : 0, $receives ? 'Ndihme sociale' : social_status_from_profile_flags(false), encode_previous_education($education), current_user()['id']]);
}

function update_document_war(PDO $pdo, array $data, array $education): void
{
    $isVeteranChild = yes_value($data['veteran_child'] ?? '');
    $stmt = $pdo->prepare('UPDATE student_profiles SET is_veteran_child=?, social_status=?, previous_education=? WHERE user_id=?');
    $stmt->execute([$isVeteranChild ? 1 : 0, $isVeteranChild ? 'Femije veterani' : social_status_from_profile_flags(null, false), encode_previous_education($education), current_user()['id']]);
}

function update_document_parent_death(PDO $pdo, array $data, array $education): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET is_orphan=?, social_status=?, previous_education=? WHERE user_id=?');
    $isOrphan = yes_value($data['one_parent_missing'] ?? '') || yes_value($data['two_parents_missing'] ?? '');
    $stmt->execute([$isOrphan ? 1 : 0, $isOrphan ? 'Jetim' : social_status_from_profile_flags(null, null, false), encode_previous_education($education), current_user()['id']]);
}

function update_document_json_only(PDO $pdo, array $education): void
{
    $stmt = $pdo->prepare('UPDATE student_profiles SET previous_education=? WHERE user_id=?');
    $stmt->execute([encode_previous_education($education), current_user()['id']]);
}

function current_student_profile(): array
{
    $stmt = db()->prepare('SELECT * FROM student_profiles WHERE user_id=?');
    $stmt->execute([current_user()['id']]);
    return $stmt->fetch() ?: [];
}

function section_success_message(string $section): string
{
    return [
        'personal' => t('personal_saved'),
        'education' => t('education_saved'),
        'courses' => t('courses_saved'),
        'crafts' => t('crafts_saved'),
        'student' => t('student_saved'),
        'bank' => t('bank_saved'),
    ][$section] ?? t('successfully_saved');
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

    popup_flash(t('user_saved'));
    redirect('admin');
}

function action_admin_delete_user(): void
{
    require_role(['admin']);
    $id = (int) ($_POST['id'] ?? 0);
    if ($id !== (int) current_user()['id']) {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        popup_flash('Fshirja u krye me sukses.');
    } else {
        flash('Nuk mund ta fshini llogarinë tuaj aktive.', 'error');
    }
    redirect('admin');
}

function action_admin_update_complaint(): void
{
    require_role(['admin']);
    $status = in_array($_POST['status'] ?? '', ['pending', 'reviewing', 'accepted', 'rejected'], true) ? $_POST['status'] : 'pending';
    $stmt = db()->prepare('UPDATE complaints SET status = ? WHERE id = ?');
    $stmt->execute([$status, (int) $_POST['id']]);
    popup_flash('Modifikimi u krye me sukses.');
    redirect('admin');
}

function render_layout(string $page): void
{
    $publicPages = ['home', 'login', 'register', 'info', 'help'];
    if (!in_array($page, $publicPages, true)) {
        require_login();
    }

    $flash = flash();
    ob_start();
    require __DIR__ . '/../src/pages/layout_top.php';

    match ($page) {
        'login' => page_login(),
        'register' => page_register(),
        'dashboard' => page_dashboard(),
        'services' => page_services(),
        'education' => page_education(),
        'help' => page_help(),
        'info' => page_info(),
        'scholarships' => page_scholarships(),
        'profile' => page_profile(),
        'provider' => page_provider(),
        'admin' => page_admin(),
        'analytics' => page_analytics(),
        'complaint' => page_complaint(),
        default => page_home(),
    };

    require __DIR__ . '/../src/pages/layout_bottom.php';
    echo translate_output(ob_get_clean());
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
        <h1><?= e(t('login_title')) ?></h1>
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="login">
            <label><?= e(t('username')) ?></label>
            <input name="username" required>
            <label><?= e(t('password')) ?></label>
            <input name="password" type="password" required>
            <label class="check"><input type="checkbox"> Me mbaj ne mend</label>
            <button class="btn full"><?= e(t('login')) ?></button>
            <a class="btn btn-outline full" href="<?= BASE_URL ?>/index.php?page=register"><?= e(t('register')) ?></a>
        </div>
    </section>
    <?php
}

function page_register(): void
{
    ?>
    <section class="auth-panel">
        <h1><?= e(t('welcome')) ?></h1>
        <p class="center-muted"><?= e(t('registration_intro')) ?></p>
        <form method="post" class="form grid-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="register">
            <label><?= e(t('registration_type')) ?>
                <select name="role" id="roleSelect">
                    <option value="student"><?= e(t('register_as_student')) ?></option>
                    <option value="provider"><?= e(t('register_as_provider')) ?></option>
                </select>
            </label>
            <label><?= e(t('name_or_organization')) ?><input name="name" required></label>
            <label><?= e(t('username')) ?><input name="username" required></label>
            <label><?= e(t('email')) ?><input name="email" type="email" required></label>
            <label><?= e(t('password')) ?><input name="password" type="password" required></label>
            <label class="provider-field"><?= e(t('provider_type')) ?>
                <select name="provider_type">
                    <option>OJQ</option>
                    <option>Biznes</option>
                    <option>Institucion Arsimor</option>
                    <option>Drejtori Komunale e Arsimit</option>
                    <option>Ofrues i Pavarur</option>
                </select>
            </label>
            <div class="student-fields form-subgrid">
                <label><?= e(t('personal_number')) ?><input name="personal_number"></label>
                <label><?= e(t('university')) ?>
                    <select name="university">
                        <option>Universiteti Kadri Zeka</option>
                        <option>Universiteti Hasan Prishtina</option>
                        <option>Universiteti Haxhi Zeka</option>
                    </select>
                </label>
                <label><?= e(t('city')) ?>
                    <select name="city">
                        <option>Kamenice</option><option>Gjilan</option><option>Viti</option><option>Ferizaj</option>
                    </select>
                </label>
                <label><?= e(t('average_grade')) ?><input name="average_grade" type="number" min="6" max="10" step="0.01" value="8.70"></label>
                <label><?= e(t('bank_name')) ?><input name="bank_name" value="Banka Ekonomike"></label>
                <label><?= e(t('account_holder')) ?><input name="bank_account_holder"></label>
                <label><?= e(t('account_number')) ?><input class="card-number-input" name="bank_account_number" inputmode="numeric" placeholder="0000 0000 0000 0000" maxlength="19" pattern="[0-9 ]{19}"></label>
                <label>IBAN<input name="bank_iban" placeholder="XK05 1212 0123 4567 8906"></label>
                <label><?= e(t('expiry_date')) ?><input class="card-expiry-input" name="bank_card_expiry" inputmode="numeric" placeholder="MM/YY" maxlength="5" pattern="(0[1-9]|1[0-2])/[0-9]{2}"></label>
                <label>CVV<input class="cvv-input" name="bank_card_cvv" inputmode="numeric" maxlength="3" pattern="[0-9]{3}"></label>
                <label class="check"><input type="checkbox" name="is_veteran_child"> Femije veterani</label>
                <label class="check"><input type="checkbox" name="is_orphan"> Jetim</label>
                <label class="check"><input type="checkbox" name="receives_social_assistance"> Pranon ndihme sociale</label>
            </div>
            <button class="btn"><?= e(t('continue')) ?></button>
        </div>
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
    $eligibleScholarships = $profile ? eligible_scholarships_for_student($profile) : [];

    $stmt = db()->prepare('SELECT a.*, s.title, s.amount, COALESCE(s.provider_name, u.name) provider_name FROM applications a JOIN scholarships s ON s.id=a.scholarship_id LEFT JOIN users u ON u.id=s.provider_id WHERE a.student_id=? ORDER BY a.created_at DESC');
    $stmt->execute([current_user()['id']]);
    $applications = $stmt->fetchAll();
    $appliedScholarshipIds = array_fill_keys(array_map(fn($application) => (int) $application['scholarship_id'], $applications), true);
    $delayedApplicationId = (int) ($_SESSION['delayed_application_result_id'] ?? 0);
    $winningApplication = $delayedApplicationId > 0 ? application_report($delayedApplicationId, (int) current_user()['id']) : null;
    unset($_SESSION['delayed_application_result_id']);
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
        <h2>Bursat</h2>
        <?php if (!$eligibleScholarships): ?>
            <p class="muted-text">Nuk u gjet asnjë bursë e përshtatshme për profilin tuaj aktual.</p>
            <p class="muted-text">Mendon se të takon ndonjë bursë që nuk po shfaqet?</p>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=complaint">Ankohu për bursë që nuk po shfaqet</a>
        <?php endif; ?>
        <?php if ($winningApplication): ?>
            <?php
            $applicationReport = json_decode($winningApplication['verification_json'] ?? '[]', true);
            $applicationReport = is_array($applicationReport) ? $applicationReport : [];
            $fulfilled = isset($applicationReport['required']) ? (array) $applicationReport['required'] : $applicationReport;
            $optionalReport = isset($applicationReport['optional']) ? (array) $applicationReport['optional'] : [];
            $usedSections = scholarship_document_sections((int) $winningApplication['scholarship_id']);
            ?>
            <article class="application-result-card">
                <h3><?= e($winningApplication['result_message'] ?: 'Urime! Ju keni fituar bursën.') ?></h3>
                <dl class="info-list">
                    <dt>Bursa</dt><dd><?= e($winningApplication['title']) ?></dd>
                    <dt>Ofruesi</dt><dd><?= e($winningApplication['provider_name']) ?></dd>
                    <dt>Statusi</dt><dd>Fituar</dd>
                    <dt>Data e aplikimit</dt><dd><?= e($winningApplication['applied_at'] ?? $winningApplication['created_at']) ?></dd>
                </dl>
                <div class="match-summary">
                    <h4>Kriteret obligative të përmbushura</h4>
                    <div class="chips">
                        <?php foreach (array_slice($fulfilled, 0, 8) as $criterion): ?>
                            <span class="ok"><?= e(($criterion['document_section'] ?? 'Profili i studentit') . ': ' . (($criterion['criterion'] ?? '') ?: ($criterion['details'] ?? 'Kriter i përmbushur')) . ' - ' . ($criterion['status_text'] ?? 'Plotësohet')) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($optionalReport): ?>
                    <div class="match-summary">
                        <h4>Kriteret opsionale / pikëzuese</h4>
                        <div class="chips">
                            <?php foreach (array_slice($optionalReport, 0, 8) as $bonus): ?>
                                <span class="<?= !empty($bonus['passed']) ? 'ok' : 'muted' ?>"><?= e(($bonus['document_section'] ?? 'Profili i studentit') . ': ' . (($bonus['criterion'] ?? '') ?: ($bonus['details'] ?? 'Kriter opsional')) . ' - ' . ($bonus['status_text'] ?? 'Nuk përfitohet bonus') . ' (' . (int) ($bonus['points_awarded'] ?? 0) . ' pikë)') ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="match-summary">
                    <h4>Seksionet e profilit të përdorura</h4>
                    <div class="chips">
                        <?php foreach ($usedSections as $section): ?>
                            <span class="muted"><?= e($section) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>
        <?php endif; ?>
        <div class="scholarship-list">
            <?php foreach ($eligibleScholarships as $scholarship): ?>
                <?php $match = $scholarship['match']; ?>
                <?php $alreadyApplied = isset($appliedScholarshipIds[(int) $scholarship['id']]); ?>
                <article class="scholarship-card">
                    <div>
                        <h3><?= e($scholarship['title']) ?></h3>
                        <p><?= e($scholarship['provider_name'] ?? '') ?> · <?= e($scholarship['category'] ?? 'Burse') ?></p>
                    </div>
                    <div class="meta">
                        <span>Afati: <?= e($scholarship['deadline'] ?? $scholarship['end_date'] ?? '-') ?></span>
                        <span>Shuma: <?= e(number_format((float) ($scholarship['amount'] ?? 0), 2)) ?> EUR</span>
                    </div>
                    <div class="match-summary">
                        <h4>Kushtet kryesore të përmbushura</h4>
                        <div class="chips">
                            <?php foreach (array_slice($match['fulfilled'], 0, 5) as $criterion): ?>
                                <span class="ok"><?= e(($criterion['document_section'] ?? 'Profili') . ': ' . (($criterion['criterion'] ?? '') ?: ($criterion['details'] ?? 'Kriter')) . ' - ' . ($criterion['student_value'] ?? '')) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (!empty($match['bonuses'])): ?>
                        <div class="match-summary">
                            <h4>Pikë ose përparësi</h4>
                            <div class="chips">
                                <?php foreach (array_slice($match['bonuses'], 0, 5) as $bonus): ?>
                                    <span class="<?= !empty($bonus['passed']) ? 'ok' : 'muted' ?>"><?= e(($bonus['document_section'] ?? 'Profili') . ': ' . (($bonus['criterion'] ?? '') ?: ($bonus['details'] ?? 'Kriter opsional')) . ' - ' . ($bonus['student_value'] ?? '') . ' (' . (int) ($bonus['points_awarded'] ?? 0) . ' pikë)') ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($alreadyApplied): ?>
                        <p class="muted-text">Ju tashmë keni aplikuar për këtë bursë.</p>
                    <?php else: ?>
                        <form method="post" class="scholarship-apply-form" data-scholarship-title="<?= e($scholarship['title']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="apply">
                            <input type="hidden" name="scholarship_id" value="<?= (int) $scholarship['id'] ?>">
                            <button class="btn">Apliko</button>
                        </form>
                    <?php endif; ?>
                    <?php if (($_GET['debug_rules'] ?? '') === '1' && !empty($match['debug'])): ?>
                        <details class="rule-debug">
                            <summary>Debug matching</summary>
                            <table>
                                <tr><th>rule_key</th><th>Kërkohet</th><th>Vlera reale</th><th>Rezultati</th></tr>
                                <?php foreach ($match['debug'] as $debug): ?>
                                    <tr>
                                        <td><?= e($debug['rule_key']) ?></td>
                                        <td><?= e(readable_rule_value($debug['required_value'])) ?></td>
                                        <td><?= e(readable_rule_value($debug['student_value'])) ?></td>
                                        <td><?= !empty($debug['passed']) ? 'true' : 'false' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($eligibleScholarships): ?>
            <div class="profile-actions">
                <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=complaint">Ankohu për bursë që nuk po shfaqet</a>
            </div>
        <?php endif; ?>
    </section>
    <div class="application-modal" id="applicationModal" aria-live="polite" aria-hidden="true">
        <div class="application-modal-card">
            <strong id="applicationModalTitle">Duke verifikuar të dhënat e profilit tuaj...</strong>
            <p id="applicationModalText">Ju lutemi prisni pak.</p>
        </div>
    </div>
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

    $documents = [];

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

    render_structured_student_documents($profile, $firstName, $lastName, false);
    return;
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
            </div>
        </div>

        <div class="profile-section">
            <h2>Student</h2>
            <div class="profile-grid">
                <?php profile_field('Statusi studentor', !empty($profile['student_active']) ? 'Student aktiv' : 'Jo aktiv'); ?>
                <?php profile_field('Universiteti', $profile['university'] ?? '-'); ?>
                <?php profile_field('Nota mesatare', isset($profile['average_grade']) ? (string) $profile['average_grade'] : '-'); ?>
            </div>
        </div>

        <div class="profile-section">
            <h2>Banka</h2>
            <?php $bankCard = decode_bank_card_metadata($profile['bank_branch'] ?? ''); ?>
            <div class="profile-grid">
                <?php profile_field('Banka', $profile['bank_name'] ?? '-'); ?>
                <?php profile_field('Mbajtesi i karteles', $profile['bank_account_holder'] ?? ($profile['name'] ?? '-')); ?>
                <?php profile_field('Numri i karteles', $profile['bank_account_number'] ?? '-'); ?>
                <?php profile_field('IBAN', $profile['bank_iban'] ?? '-'); ?>
                <?php profile_field('Data e skadences', $bankCard['expiry'] ?: '-'); ?>
                <?php profile_field('CVV', $bankCard['cvv'] ?: '-'); ?>
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

function page_help(): void
{
    ?>
    <section class="help-page">
        <h1>Parashtroni kërkesë për ndihmë ose ankesë</h1>
        <form class="help-form">
            <div class="help-form-row">
                <label>Emri dhe mbiemri
                    <input name="full_name" autocomplete="name">
                </label>
                <label>Email adresa
                    <input name="email" type="email" autocomplete="email">
                </label>
            </div>
            <label class="wide">Si mund t'ju ndihmojmë?
                <textarea name="description" placeholder="Përshkruani kërkesën ose ankesën tuaj"></textarea>
            </label>
            <div class="help-form-row help-form-row-secondary">
                <label>Numri/reference i kerkeses
                    <input name="case_reference" placeholder="Opsionale">
                </label>
                <label>Zgjedh shërbimin
                    <select name="service">
                        <option value="">Zgjedh shërbimin</option>
                        <option>Familja</option>
                        <option>Arsimi</option>
                        <option>Shëndetësia</option>
                        <option>Kontributet</option>
                        <option>Grantet</option>
                        <option>Tjetër</option>
                    </select>
                </label>
            </div>
            <div class="captcha-placeholder" aria-label="Captcha placeholder">
                <span>✓</span>
                <strong>Success!</strong>
                <b>Cloudflare</b>
            </div>
            <div class="help-actions">
                <a class="btn btn-outline danger-outline" href="<?= BASE_URL ?>/index.php?page=home">Ndërpreje</a>
                <button class="btn placeholder" type="button" data-placeholder="Kërkesa për ndihmë është placeholder në këtë prototip.">Dërgo</button>
            </div>
        </form>
    </section>
    <?php
}

function page_info(): void
{
    $items = [
        ['Shërbimet në nivel qendror', '▥'],
        ['Shërbimet në nivel lokal', '▦'],
        ['Benefitet dhe asistenca', '♧'],
        ['Lëvizja dhe komunikimi', '↻'],
        ['Familja', '♟'],
        ['Siguria', '◆'],
        ['Shëndetësia', '♡'],
        ['Ambienti dhe natyra', '♠'],
        ['Dokumentet', '✎'],
        ['Diaspora', '◉'],
        ['Puna dhe biznesi', '▰'],
        ['Udhëzuesit', 'ⓘ'],
    ];
    ?>
    <section class="info-page">
        <div class="info-head">
            <h1>Informata</h1>
            <button class="info-filter placeholder" type="button" data-placeholder="Filtrat janë placeholder në këtë prototip."><span>☷</span> Të gjitha <b>⌄</b></button>
        </div>

        <div class="info-grid">
            <?php foreach ($items as [$title, $icon]): ?>
                <a class="info-tile placeholder" href="<?= BASE_URL ?>/index.php?page=info" data-placeholder="Kjo informatë është placeholder në këtë prototip.">
                    <span><?= e($icon) ?></span>
                    <strong><?= e($title) ?></strong>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function page_scholarships(): void
{
    require_role(['student', 'admin']);
    $isStudent = current_user()['role'] === 'student';
    if ($isStudent) {
        $profile = current_student_profile();
        $scholarships = $profile ? eligible_scholarships_for_student($profile) : [];
        $stmt = db()->prepare('SELECT scholarship_id FROM applications WHERE student_id=?');
        $stmt->execute([current_user()['id']]);
        $appliedScholarshipIds = array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
    } else {
        $scholarships = db()->query('SELECT s.*, COALESCE(s.provider_name, u.name) provider_name FROM scholarships s LEFT JOIN users u ON u.id=s.provider_id WHERE s.status="active" ORDER BY s.deadline ASC')->fetchAll();
        $appliedScholarshipIds = [];
    }
    ?>
    <section class="page-head"><h1>Bursat aktive</h1></section>
    <div class="scholarship-list">
        <?php if ($isStudent && !$scholarships): ?>
            <article class="scholarship-card">
                <h2>Nuk u gjet asnjë bursë e përshtatshme për profilin tuaj aktual.</h2>
                <p>Mendon se të takon ndonjë bursë që nuk po shfaqet?</p>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=complaint">Ankohu për bursë që nuk po shfaqet</a>
            </article>
        <?php endif; ?>
        <?php foreach ($scholarships as $s): ?>
            <article class="scholarship-card">
                <h2><?= e($s['title']) ?></h2>
                <p><?= e($s['description']) ?></p>
                <div class="meta">
                    <span>Ofruesi: <?= e($s['provider_name']) ?></span>
                    <span>Kategoria: <?= e($s['category'] ?? 'Burse') ?></span>
                    <span>Shuma: <?= e(number_format((float) $s['amount'], 2)) ?> EUR</span>
                    <span>Afati: <?= e($s['deadline']) ?></span>
                </div>
                <div class="criteria">
                    <?= criteria_text($s) ?>
                </div>
                <?php if ($isStudent && isset($s['match'])): ?>
                    <div class="match-summary">
                        <h4>Kushtet kryesore të përmbushura</h4>
                        <div class="chips">
                            <?php foreach (array_slice($s['match']['fulfilled'], 0, 5) as $fulfilled): ?>
                                <span class="ok"><?= e(($fulfilled['document_section'] ?? 'Profili') . ': ' . (($fulfilled['criterion'] ?? '') ?: ($fulfilled['details'] ?? 'Kriter')) . ' - ' . ($fulfilled['student_value'] ?? '')) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($s['match']['bonuses'])): ?>
                            <h4>Pikët ose përparësitë</h4>
                            <div class="chips">
                                <?php foreach (array_slice($s['match']['bonuses'], 0, 5) as $bonus): ?>
                                    <span class="<?= !empty($bonus['passed']) ? 'ok' : 'muted' ?>"><?= e(($bonus['document_section'] ?? 'Profili') . ': ' . (($bonus['criterion'] ?? '') ?: ($bonus['details'] ?? 'Kriter opsional')) . ' - ' . ($bonus['student_value'] ?? '') . ' (' . (int) ($bonus['points_awarded'] ?? 0) . ' pikë)') ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($isStudent): ?>
                    <?php if (isset($appliedScholarshipIds[(int) $s['id']])): ?>
                        <p class="muted-text">Ju tashmë keni aplikuar për këtë bursë.</p>
                    <?php else: ?>
                        <form method="post" class="scholarship-apply-form" data-scholarship-title="<?= e($s['title']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="action" value="apply">
                            <input type="hidden" name="scholarship_id" value="<?= (int) $s['id'] ?>">
                            <button class="btn">Apliko</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($isStudent): ?>
        <div class="application-modal" id="applicationModal" aria-live="polite" aria-hidden="true">
            <div class="application-modal-card">
                <strong id="applicationModalTitle">Duke verifikuar të dhënat e profilit tuaj...</strong>
                <p id="applicationModalText">Ju lutemi prisni pak.</p>
            </div>
        </div>
    <?php endif; ?>
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
    ensure_scholarship_template_schema();
    $users = db()->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
    $providers = db()->query('SELECT id, name, provider_type FROM users WHERE role = "provider" AND is_active = 1 ORDER BY name')->fetchAll();
    $scholarships = db()->query('SELECT s.*, COALESCE(s.provider_name, u.name) provider_name FROM scholarships s LEFT JOIN users u ON u.id=s.provider_id ORDER BY s.created_at DESC')->fetchAll();
    $applications = db()->query('SELECT a.*, st.name student_name, s.title FROM applications a JOIN users st ON st.id=a.student_id JOIN scholarships s ON s.id=a.scholarship_id ORDER BY a.created_at DESC')->fetchAll();
    $complaints = db()->query('SELECT c.*, u.name student_name, COALESCE(s.title, c.provider_name) scholarship_title FROM complaints c JOIN users u ON u.id=c.student_id LEFT JOIN applications a ON a.id=c.application_id LEFT JOIN scholarships s ON s.id=a.scholarship_id ORDER BY c.created_at DESC')->fetchAll();
    $analytics = analytics_data();
    $templatePayload = admin_scholarship_template_payload();
    ?>
    <section class="page-head">
        <h1>Paneli i administratorit</h1>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=analytics">Analitika</a>
    </section>
    <?php render_analytics_summary($analytics); ?>
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
    <section class="panel">
        <h2>Shto bursë</h2>
        <?php if (!$providers): ?>
            <p class="muted-text">Nuk ka ofrues aktivë. Shtoni fillimisht një përdorues me rolin Ofrues.</p>
        <?php else: ?>
            <form method="post" class="form scholarship-template-form" id="adminScholarshipForm" data-templates="<?= e((string) json_encode($templatePayload, JSON_UNESCAPED_UNICODE)) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="save_scholarship">
                <input type="hidden" name="id" value="0">
                <input type="hidden" name="template_id" id="scholarshipTemplateId">
                <input type="hidden" name="provider_name" id="scholarshipProviderName">

                <div class="form-subsection scholarship-template-picker">
                    <h3>Template-i</h3>
                    <div class="form-section-grid">
                        <label>Kategoria
                            <select name="category" id="scholarshipCategory" required>
                                <option value="">Zgjedh kategorine</option>
                                <option value="Burse komunale">Burse komunale</option>
                                <option value="Burse universitare">Burse universitare</option>
                                <option value="Burse humanitare nga OJQ">Burse humanitare nga OJQ</option>
                            </select>
                        </label>
                        <label id="templateProviderWrap">Ofruesi
                            <select id="templateProviderSelect">
                                <option value="">Zgjedh ofruesin</option>
                            </select>
                        </label>
                        <label id="ojqProviderWrap" class="is-hidden">Ofruesi OJQ
                            <select name="provider_id" id="providerSelect" required disabled>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?= (int) $provider['id'] ?>" data-provider-name="<?= e($provider['name']) ?>" data-provider-type="<?= e($provider['provider_type'] ?? '') ?>"><?= e($provider['name']) ?><?= $provider['provider_type'] ? ' - ' . e($provider['provider_type']) : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label id="templateProviderIdWrap">Llogaria e ofruesit
                            <select name="provider_id" id="templateProviderIdSelect" required>
                                <?php foreach ($providers as $provider): ?>
                                    <option value="<?= (int) $provider['id'] ?>" data-provider-name="<?= e($provider['name']) ?>" data-provider-type="<?= e($provider['provider_type'] ?? '') ?>"><?= e($provider['name']) ?><?= $provider['provider_type'] ? ' - ' . e($provider['provider_type']) : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <button class="btn btn-outline" type="button" id="loadScholarshipTemplate">Ngarko kriteret</button>
                    <p class="muted-text template-hint">Template-i sherben vetem si model. Para ruajtjes mund te ndryshoni titullin, kriteret dhe seksionet e profilit.</p>
                </div>

                <div class="form-subsection">
                    <h3>1. Te dhenat baze te burses</h3>
                    <div class="form-section-grid">
                        <label>Titulli<input name="title" id="scholarshipTitle" required></label>
                        <label>Shuma EUR<input type="number" name="amount" step="0.01" min="1" value="500" required></label>
                        <label>Statusi<select name="status"><option value="active">Aktive</option><option value="inactive">Jo aktive</option></select></label>
                        <label>Nota minimale<input type="number" min="6" max="10" step="0.01" name="min_grade" id="legacyMinGrade"></label>
                        <label>Universiteti<select name="required_university" id="legacyUniversity"><option value="">Cilido</option><option>Universiteti Kadri Zeka</option><option>Universiteti Hasan Prishtina</option><option>Universiteti Haxhi Zeka</option></select></label>
                        <label>Qyteti<select name="required_city" id="legacyCity"><option value="">Cilido</option><option>Kamenice</option><option>Gjilan</option><option>Viti</option><option>Ferizaj</option></select></label>
                        <label>Statusi social<select name="required_social_status" id="legacySocialStatus"><option value="">Cilido</option><option>Ndihme sociale</option><option>Jetim</option><option>Femije veterani</option></select></label>
                        <label class="wide">Pershkrimi<textarea name="description" id="scholarshipDescription"></textarea></label>
                    </div>
                </div>

                <div class="form-subsection">
                    <h3>2. Kriteret obligative</h3>
                    <div class="template-card-list" id="requiredRulesBody"></div>
                </div>

                <div class="form-subsection">
                    <h3>3. Kriteret opsionale / pikezuese</h3>
                    <div class="template-card-list" id="optionalRulesBody"></div>
                </div>

                <div class="form-subsection">
                    <h3>4. Seksionet e profilit qe perdoren ne vend te dokumenteve</h3>
                    <div class="template-card-list" id="templateDocumentsBody"></div>
                    <p class="muted-text">Keto jane seksione te profilit te studentit, jo file upload.</p>
                </div>

                <div class="form-subsection">
                    <h3>5. Afati i aplikimit</h3>
                    <div class="form-section-grid">
                        <label>Data e hapjes<input type="date" name="start_date" id="scholarshipStartDate"></label>
                        <label>Data e mbylljes<input type="date" name="end_date" id="scholarshipEndDate" required></label>
                    </div>
                </div>

                <div class="checkbox-group inline-checks">
                    <label class="check"><input type="checkbox" name="requires_veteran_child" id="legacyVeteranChild"> Kerko femije veterani</label>
                    <label class="check"><input type="checkbox" name="requires_orphan" id="legacyOrphan"> Kerko jetim</label>
                    <label class="check"><input type="checkbox" name="requires_social_assistance" id="legacySocialAssistance"> Kerko ndihme sociale</label>
                </div>
                <button class="btn">Ruaj si burse aktive</button>
            </form>
        <?php endif; ?>
    </section>
    <section class="panel"><h2>Bursat</h2><table><tr><th>Titulli</th><th>Ofruesi</th><th>Kategoria</th><th>Statusi</th><th></th></tr><?php foreach ($scholarships as $s): ?><tr><td><?= e($s['title']) ?></td><td><?= e($s['provider_name']) ?></td><td><?= e($s['category'] ?? '-') ?></td><td><form method="post" class="mini-form"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="update_scholarship_status"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><select name="status"><option value="active" <?= selected('active', $s['status']) ?>>Aktive</option><option value="inactive" <?= selected('inactive', $s['status']) ?>>Jo aktive</option></select><button class="btn small">Ruaj</button></form></td><td><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete_scholarship"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="link danger">Fshi</button></form></td></tr><?php endforeach; ?></table></section>
    <section class="panel"><h2>Aplikimet</h2><table><tr><th>Studenti</th><th>Bursa</th><th>Statusi</th><th>Data</th></tr><?php foreach ($applications as $a): ?><tr><td><?= e($a['student_name']) ?></td><td><?= e($a['title']) ?></td><td><?= status_label($a['status']) ?></td><td><?= e($a['applied_at'] ?? $a['created_at']) ?></td></tr><?php endforeach; ?></table></section>
    <section class="panel"><h2>Ankesat</h2><table><tr><th>Studenti</th><th>Kategoria</th><th>Ofruesi/Bursa</th><th>Pershkrimi</th><th>Arsyeja</th><th>Statusi</th></tr><?php foreach ($complaints as $c): ?><tr><td><?= e($c['student_name']) ?></td><td><?= e($c['scholarship_category'] ?? '-') ?></td><td><?= e($c['scholarship_title'] ?: ($c['provider_name'] ?? '-')) ?></td><td><?= e($c['message']) ?></td><td><?= e($c['reason'] ?? '') ?></td><td><form method="post" class="mini-form"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="admin_update_complaint"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><select name="status"><option value="pending" <?= selected('pending', $c['status']) ?>>Në pritje</option><option value="reviewing" <?= selected('reviewing', $c['status']) ?>>Në shqyrtim</option><option value="accepted" <?= selected('accepted', $c['status']) ?>>E pranuar</option><option value="rejected" <?= selected('rejected', $c['status']) ?>>E refuzuar</option></select><button class="btn small">Ruaj</button></form></td></tr><?php endforeach; ?></table></section>
    <?php
}

function page_analytics(): void
{
    require_role(['admin']);
    ensure_scholarship_template_schema();
    render_analytics_page(analytics_data());
}

function analytics_data(): array
{
    $traditionalSteps = 10;
    $ekosovaSteps = 2.5;
    $traditionalSeconds = 345600;
    $ekosovaSeconds = 3;
    $traditionalVisits = 5;
    $ekosovaVisits = 0;
    $traditionalDocuments = 6;
    $ekosovaDocuments = 0;

    return [
        'metrics' => [
            [
                'label' => 'Reduktimi i dokumenteve fizike',
                'before' => $traditionalDocuments . ' dokumente',
                'after' => $ekosovaDocuments . ' dokumente fizike',
                'percent' => 100.0,
                'tone' => 'gold',
            ],
            [
                'label' => 'Reduktimi i vizitave fizike',
                'before' => $traditionalVisits . ' institucione fizike',
                'after' => $ekosovaVisits . ' vizita fizike',
                'percent' => 100.0,
                'tone' => 'teal',
            ],
            [
                'label' => 'Reduktimi i hapave proceduralë',
                'before' => $traditionalSteps . ' hapa',
                'after' => '2-3 hapa',
                'percent' => percentage_reduction($traditionalSteps, $ekosovaSteps),
                'tone' => 'blue',
            ],
            [
                'label' => 'Reduktimi i kohës së aplikimit',
                'before' => '4 ditë',
                'after' => '3 sekonda pas klikimit',
                'percent' => percentage_reduction($traditionalSeconds, $ekosovaSeconds),
                'tone' => 'green',
            ],
            [
                'label' => 'Eliminimi i refuzimit manual',
                'before' => 'Listim i plotë dhe refuzim manual',
                'after' => 'Shfaqen vetëm bursat e përshtatshme',
                'percent' => 100.0,
                'tone' => 'purple',
            ],
        ],
        'counts' => analytics_counts(),
        'optimization_sources' => [
            'Eliminimi i dokumenteve fizike/PDF.',
            'Zëvendësimi i dokumenteve me seksione të profilit.',
            'Matching automatik student-bursë.',
            'Shfaqja vetëm e bursave të përshtatshme.',
            'Eliminimi i refuzimit manual.',
            'Aplikimi me një klikim.',
            'Fitimi/simulimi pas 3 sekondash.',
        ],
        'comparison' => [
            ['Procesi', 'Procesi tradicional', 'Procesi me EKosova+'],
            ['Pikat e kontaktit', '5 institucione fizike', '1 platformë'],
            ['Hapat', '10 hapa', '2-3 hapa'],
            ['Dokumentet', '6 dokumente', '0 dokumente fizike'],
            ['Koha', '4 ditë', '3 sekonda pas klikimit'],
        ],
    ];
}

function analytics_counts(): array
{
    $counts = [
        'total_applications' => 0,
        'won_applications' => 0,
        'active_scholarships' => 0,
        'total_complaints' => 0,
    ];

    try {
        $queries = [
            'total_applications' => ['SELECT COUNT(*) FROM applications', []],
            'won_applications' => ['SELECT COUNT(*) FROM applications WHERE status IN ("fituar", "approved")', []],
            'active_scholarships' => ['SELECT COUNT(*) FROM scholarships WHERE status = ?', ['active']],
            'total_complaints' => ['SELECT COUNT(*) FROM complaints', []],
        ];

        foreach ($queries as $key => [$sql, $params]) {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            $counts[$key] = (int) $stmt->fetchColumn();
        }
    } catch (Throwable $e) {
        return $counts;
    }

    return $counts;
}

function percentage_reduction(float $before, float $after): float
{
    if ($before <= 0) {
        return 0.0;
    }

    return max(0.0, min(100.0, (($before - $after) / $before) * 100));
}

function percent_label(float $value): string
{
    return rtrim(rtrim(number_format($value, 1), '0'), '.') . '%';
}

function admin_file_url(string $file): string
{
    $root = str_replace('\\', '/', dirname(BASE_URL));
    if (in_array($root, ['.', '/', '\\'], true)) {
        $root = '';
    }

    return $root . '/admin/' . ltrim($file, '/');
}

function render_analytics_summary(array $analytics): void
{
    $counts = $analytics['counts'];
    ?>
    <section class="panel analytics-summary-panel">
        <div class="analytics-summary-head">
            <div>
                <h2>Analitika e Optimizimit</h2>
                <p class="muted-text">Krahasim i shpejtë i procesit tradicional me rrjedhën EKosova+.</p>
            </div>
            <a class="btn small" href="<?= BASE_URL ?>/index.php?page=analytics">Hap analitikën</a>
        </div>
        <div class="analytics-mini-grid">
            <article>
                <span><?= percent_label($analytics['metrics'][0]['percent']) ?></span>
                <small>më pak dokumente fizike</small>
            </article>
            <article>
                <span><?= percent_label($analytics['metrics'][3]['percent']) ?></span>
                <small>më pak kohë aplikimi</small>
            </article>
            <article>
                <span><?= (int) $counts['won_applications'] ?></span>
                <small>aplikime të fituara</small>
            </article>
            <article>
                <span><?= (int) $counts['total_complaints'] ?></span>
                <small>ankesa</small>
            </article>
        </div>
    </section>
    <?php
}

function render_analytics_page(array $analytics): void
{
    $counts = $analytics['counts'];
    ?>
    <section class="page-head">
        <div>
            <h1>Analitika e Optimizimit</h1>
            <p class="muted-text">Optimizimi vjen nga eliminimi i dokumenteve fizike/PDF, zëvendësimi i tyre me seksione të profilit, matching automatik student-bursë, shfaqja vetëm e bursave të përshtatshme, eliminimi i refuzimit manual, aplikimi me një klikim dhe fitimi/simulimi pas 3 sekondash.</p>
            <p class="muted-text">Formula: Optimizimi = ((Vlera tradicionale - Vlera EKosova+) / Vlera tradicionale) * 100.</p>
        </div>
        <div class="profile-actions">
            <a class="btn" href="<?= e(admin_file_url('export-analytics.php')) ?>">Eksporto në Excel</a>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=admin">Kthehu te admini</a>
        </div>
    </section>

    <section class="analytics-stat-grid" aria-label="Metrikat e optimizimit">
        <?php foreach ($analytics['metrics'] as $metric): ?>
            <article class="analytics-card <?= e($metric['tone']) ?>">
                <small><?= e($metric['label']) ?></small>
                <strong><?= e(percent_label((float) $metric['percent'])) ?></strong>
                <div class="progress-bar" aria-label="<?= e(percent_label((float) $metric['percent'])) ?>">
                    <span style="width: <?= e((string) round((float) $metric['percent'], 1)) ?>%"></span>
                </div>
                <p><b>Para:</b> <?= e($metric['before']) ?></p>
                <p><b>Pas:</b> <?= e($metric['after']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Burimet e optimizimit</h2>
        <div class="chips">
            <?php foreach ($analytics['optimization_sources'] as $source): ?>
                <span class="ok"><?= e($source) ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="analytics-layout">
        <article class="panel">
            <h2>Aplikimet dhe ankesat</h2>
            <div class="analytics-mini-grid count-grid">
                <article><span><?= (int) $counts['active_scholarships'] ?></span><small>Bursa aktive</small></article>
                <article><span><?= (int) $counts['total_applications'] ?></span><small>Total aplikime</small></article>
                <article><span><?= (int) $counts['won_applications'] ?></span><small>Të fituara</small></article>
                <article><span><?= (int) $counts['total_complaints'] ?></span><small>Ankesa</small></article>
            </div>
        </article>

        <article class="panel">
            <h2>Grafik i thjeshtë</h2>
            <div class="simple-chart" role="img" aria-label="Krahasim i reduktimeve kryesore">
                <?php foreach (array_slice($analytics['metrics'], 0, 4) as $metric): ?>
                    <div class="chart-row">
                        <span><?= e($metric['label']) ?></span>
                        <div><i style="width: <?= e((string) round((float) $metric['percent'], 1)) ?>%"></i></div>
                        <b><?= e(percent_label((float) $metric['percent'])) ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="panel">
        <h2>Para / Pas</h2>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th><?= e($analytics['comparison'][0][0]) ?></th>
                    <th><?= e($analytics['comparison'][0][1]) ?></th>
                    <th><?= e($analytics['comparison'][0][2]) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($analytics['comparison'], 1) as $row): ?>
                    <tr>
                        <td><?= e($row[0]) ?></td>
                        <td><?= e($row[1]) ?></td>
                        <td><?= e($row[2]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php
}

function page_complaint(): void
{
    require_role(['student']);
    $applicationId = (int) ($_GET['application_id'] ?? 0);
    $providers = [
        'Bursë komunale' => ['Komuna e Kamenicës', 'Komuna e Gjilanit', 'Komuna e Vitisë', 'Komuna e Ferizajit'],
        'Bursë universitare' => ['Universiteti “Kadri Zeka”', 'Universiteti i Prishtinës', 'Universiteti “Haxhi Zeka”'],
        'Bursë humanitare nga OJQ' => ['OJQ TOKA', 'AMIK', 'VILDANA Foundation'],
    ];
    ?>
    <section class="auth-panel narrow">
        <h1>Ankohu për bursë që nuk po shfaqet</h1>
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="complaint">
            <input type="hidden" name="application_id" value="<?= $applicationId ?>">
            <label>Kategoria e bursës
                <select name="scholarship_category" required>
                    <option value="">Zgjedh kategorinë</option>
                    <?php foreach (array_keys($providers) as $category): ?>
                        <option value="<?= e($category) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Ofruesi
                <select name="provider_name" required>
                    <option value="">Zgjedh ofruesin</option>
                    <?php foreach ($providers as $items): ?>
                        <?php foreach ($items as $provider): ?>
                            <option value="<?= e($provider) ?>"><?= e($provider) ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Përshkrimi i ankesës<textarea name="message" required></textarea></label>
            <label>Arsyeja pse mendon se të takon bursa<textarea name="reason" required></textarea></label>
            <p class="warning-note">Vërejtje: Paraqitja e ankesave të pavërteta ose keqpërdorimi i sistemit mund të sjellë përgjegjësi ligjore, duke përfshirë dënim deri në 1 vit burgim, sipas dispozitave përkatëse ligjore.</p>
            <button class="btn">Dërgo ankesën</button>
        </form>
    </section>
    <?php
}

function status_label(string $status): string
{
    return [
        'pending' => t('pending'),
        'approved' => 'Fituar',
        'fituar' => 'Fituar',
        'rejected' => t('rejected'),
    ][$status] ?? t($status);
}

function render_structured_student_documents(array $profile, string $firstName, string $lastName, bool $editPage): void
{
    $sections = student_document_section_definitions($profile, $firstName, $lastName);
    ?>
    <section class="page-head">
        <div>
            <h1><?= $editPage ? 'Ndrysho te dhenat e profilit' : 'Te dhenat e perdoruesit' ?></h1>
            <p class="muted-text">Dokumentet ne EKosova+ jane te dhena te strukturuara, te licencuara nga institucionet burimore. Nuk kerkohet ngarkim i PDF, fotografi apo file zyrtar.</p>
        </div>
        <div class="profile-actions">
            <?php if ($editPage): ?>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=profile">Kthehu te profili</a>
            <?php else: ?>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=profile&edit=1">Ndrysho te dhenat</a>
                <a class="btn" href="<?= BASE_URL ?>/index.php?page=scholarships">Apliko per burse</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="profile-document-grid">
            <?php foreach ($sections as $key => $section): ?>
                <article class="form-section-card profile-collapsible-section structured-document-card <?= section_open_class($key) ?>" data-section="<?= e($key) ?>">
                    <div class="section-card-head">
                        <div>
                            <h2><?= e($section['title']) ?></h2>
                            <?php if (!empty($section['required_note'])): ?>
                                <span class="document-required-note"><?= e($section['required_note']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="section-edit-button" type="button" aria-label="Ndrysho <?= e($section['title']) ?>" title="Ndrysho">✎</button>
                    </div>
                    <div class="document-verification-line">
                        <span class="badge ok">Verifikuar</span>
                    </div>
                    <div class="section-view-body readonly-grid">
                        <?php foreach ($section['fields'] as $field): ?>
                            <?php render_readonly_field($field['label'], document_field_value($section['data'], $field)); ?>
                        <?php endforeach; ?>
                    </div>
                    <p class="document-source-message"><?= e($section['message']) ?></p>
                    <form method="post" class="section-edit-body">
                        <?php render_section_form_fields($key); ?>
                        <div class="form-section-grid">
                            <?php foreach ($section['fields'] as $field): ?>
                                <?php render_document_input($field, document_field_value($section['data'], $field)); ?>
                            <?php endforeach; ?>
                        </div>
                        <?php render_profile_form_actions($key); ?>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function student_document_section_definitions(array $profile, string $firstName = '', string $lastName = ''): array
{
    $education = decode_previous_education($profile['previous_education'] ?? '');
    $documents = is_array($education['documents'] ?? null) ? $education['documents'] : [];
    $studentMeta = is_array($education['studies']['student_meta'] ?? null) ? $education['studies']['student_meta'] : [];
    $university = (string) ($profile['university'] ?? '');
    $municipality = (string) ($profile['city'] ?? '');
    $bank = (string) ($profile['bank_name'] ?? '');
    $program = (string) ($studentMeta['program'] ?? document_data_value($documents, 'student_confirmation', 'program', ''));
    $deficitPrograms = ['Inxhinieri Kompjuterike', 'Matematike', 'Fizike', 'TIK', 'Infermieria'];

    return [
        'id_card' => document_section('ID / Leternjoftimi', [
            field_def('first_name', 'Emri', 'text', $firstName ?: ($profile['first_name'] ?? '')),
            field_def('last_name', 'Mbiemri', 'text', $lastName ?: ($profile['last_name'] ?? '')),
            field_def('personal_number', 'Numri personal', 'text', $profile['personal_number'] ?? ''),
            field_def('birth_date', 'Data e lindjes', 'date', $profile['birth_date'] ?? ''),
            field_def('gender', 'Gjinia', 'select', $profile['gender'] ?? '', ['Mashkull', 'Femer', 'Tjeter']),
            field_def('citizenship', 'Shtetesia', 'text', document_data_value($documents, 'id_card', 'citizenship', 'Kosove')),
            field_def('municipality', 'Komuna', 'text', $municipality),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga Agjencia e Regjistrimit Civil e Republikes se Kosoves.', $documents['id_card'] ?? [], 'Gjithmone obligativ per cdo burse.'),
        'residence_certificate' => document_section('Certifikata e Vendbanimit', [
            field_def('municipality', 'Komuna e vendbanimit', 'text', $municipality),
            field_def('city', 'Qyteti', 'text', document_data_value($documents, 'residence_certificate', 'city', $profile['residence'] ?? $municipality)),
            field_def('address', 'Adresa', 'text', $profile['residence'] ?? ''),
            field_def('resident_status', 'Statusi rezident', 'yesno', document_data_value($documents, 'residence_certificate', 'resident_status', 'po')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga Komuna e ' . ($municipality ?: '[komuna e studentit]') . '.', $documents['residence_certificate'] ?? []),
        'student_confirmation' => document_section('Vertetimi i Studentit Aktiv', [
            field_def('university', 'Universiteti', 'text', $university),
            field_def('faculty', 'Fakulteti', 'text', document_data_value($documents, 'student_confirmation', 'faculty', $studentMeta['faculty'] ?? '')),
            field_def('program', 'Drejtimi/programi', 'text', $program),
            field_def('study_level', 'Niveli i studimeve', 'select', $studentMeta['study_level'] ?? '', ['Bachelor', 'Master', 'PhD']),
            field_def('study_year', 'Viti i studimit', 'number', $studentMeta['study_year'] ?? ''),
            field_def('active_status', 'Statusi aktiv', 'yesno', !empty($profile['student_active']) ? 'po' : 'jo'),
            field_def('full_time', 'Student i rregullt', 'yesno', document_data_value($documents, 'student_confirmation', 'full_time', 'po')),
            field_def('correspondence', 'Student me korrespondence', 'yesno', document_data_value($documents, 'student_confirmation', 'correspondence', 'jo')),
            field_def('self_financing', 'Vetefinancim', 'yesno', document_data_value($documents, 'student_confirmation', 'self_financing', 'jo')),
            field_def('repeating_year', 'Perserites i vitit', 'yesno', document_data_value($documents, 'student_confirmation', 'repeating_year', 'jo')),
            field_def('public_university', 'Universitet publik', 'yesno', education_has_option($education, 'studies.student_meta.institution_type', 'Universitet Publik') ? 'po' : 'jo'),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga ' . ($university ?: '[universiteti]') . '.', $documents['student_confirmation'] ?? []),
        'grade_certificate' => document_section('Certifikata e Notave', [
            field_def('average_grade', 'Nota mesatare', 'number', isset($profile['average_grade']) ? (string) $profile['average_grade'] : ''),
            field_def('previous_year_exams_completed', 'Te gjitha provimet e vitit paraprak te perfunduara', 'yesno', document_data_value($documents, 'grade_certificate', 'previous_year_exams_completed', 'po')),
            field_def('september_exams_completed', 'Provimet e perfunduara deri ne afatin e shtatorit', 'yesno', document_data_value($documents, 'grade_certificate', 'september_exams_completed', 'po')),
            field_def('results_academic_year', 'Viti akademik i rezultateve', 'text', document_data_value($documents, 'grade_certificate', 'results_academic_year', '2025/2026')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga ' . ($university ?: '[universiteti]') . '.', $documents['grade_certificate'] ?? []),
        'family_declaration' => document_section('Deklarata e Bashkesise Familjare', [
            field_def('family_members_count', 'Numri i anetareve te familjes', 'number', document_data_value($documents, 'family_declaration', 'family_members_count', '')),
            field_def('family_students_count', 'Numri i studenteve ne familje', 'number', document_data_value($documents, 'family_declaration', 'family_students_count', '')),
            field_def('close_family', 'Jeton ne familje te ngushte', 'yesno', document_data_value($documents, 'family_declaration', 'close_family', 'po')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga Komuna e ' . ($municipality ?: '[komuna]') . '.', $documents['family_declaration'] ?? []),
        'bank_confirmation' => document_section('Konfirmimi Bankar', [
            field_def('bank', 'Banka', 'text', $bank),
            field_def('account_holder', 'Mbajtesi i llogarise', 'text', $profile['bank_account_holder'] ?? ''),
            field_def('account_number', 'Numri i xhirollogarise', 'text', $profile['bank_account_number'] ?? ''),
            field_def('iban', 'IBAN', 'text', $profile['bank_iban'] ?? ''),
            field_def('branch', 'Dega', 'text', $profile['bank_branch'] ?? ''),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga ' . ($bank ?: '[banka]') . '.', $documents['bank_confirmation'] ?? []),
        'tax_confirmation' => document_section('Vertetimi nga Administrata Tatimore e Kosoves', [
            field_def('student_employed', 'Studenti i punesuar', 'yesno', ($profile['employment_status'] ?? '') === 'I punesuar' ? 'po' : 'jo'),
            field_def('active_worker', 'Punetor aktiv', 'yesno', document_data_value($documents, 'tax_confirmation', 'active_worker', ($profile['employment_status'] ?? '') === 'I punesuar' ? 'po' : 'jo')),
            field_def('parents_employed', 'Prinderit te punesuar', 'yesno', document_data_value($documents, 'tax_confirmation', 'parents_employed', 'jo')),
            field_def('self_financing_status', 'Statusi i vetefinancimit', 'text', document_data_value($documents, 'tax_confirmation', 'self_financing_status', '')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga Administrata Tatimore e Kosoves.', $documents['tax_confirmation'] ?? []),
        'social_assistance_confirmation' => document_section('Vertetimi per Ndihme Sociale', [
            field_def('receives_social_assistance', 'Perfitues i ndihmes sociale', 'yesno', !empty($profile['receives_social_assistance']) ? 'po' : 'jo'),
            field_def('social_work_center', 'Qendra per pune sociale', 'text', document_data_value($documents, 'social_assistance_confirmation', 'social_work_center', '')),
            field_def('case_reference', 'Numri/reference i rastit', 'text', document_data_value($documents, 'social_assistance_confirmation', 'case_reference', '')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga Qendra per Pune Sociale.', $documents['social_assistance_confirmation'] ?? []),
        'war_category_confirmation' => document_section('Vertetimi per Kategori te Luftes', [
            field_def('martyr_child', 'Femije deshmori', 'yesno', document_data_value($documents, 'war_category_confirmation', 'martyr_child', 'jo')),
            field_def('veteran_child', 'Femije veterani', 'yesno', !empty($profile['is_veteran_child']) ? 'po' : 'jo'),
            field_def('martyr_family', 'Familje e deshmorit', 'yesno', document_data_value($documents, 'war_category_confirmation', 'martyr_family', 'jo')),
            field_def('disabled_war', 'Invalid i luftes', 'yesno', document_data_value($documents, 'war_category_confirmation', 'disabled_war', 'jo')),
            field_def('veteran_war', 'Veteran i luftes', 'yesno', document_data_value($documents, 'war_category_confirmation', 'veteran_war', 'jo')),
            field_def('war_martyr', 'Martir i luftes', 'yesno', document_data_value($documents, 'war_category_confirmation', 'war_martyr', 'jo')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga institucioni perkates per kategorite e dala nga lufta.', $documents['war_category_confirmation'] ?? []),
        'parent_death_certificate' => document_section('Certifikata e Vdekjes se Prinderve', [
            field_def('one_parent_missing', 'Pa njërin prind', 'yesno', !empty($profile['is_orphan']) ? 'po' : 'jo'),
            field_def('two_parents_missing', 'Pa dy prinder', 'yesno', document_data_value($documents, 'parent_death_certificate', 'two_parents_missing', 'jo')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga Agjencia e Regjistrimit Civil.', $documents['parent_death_certificate'] ?? [], 'Opsional, perdoret vetem per bursa ku jep pike/perparesi.'),
        'special_needs_confirmation' => document_section('Vertetimi per Nevoja te Vecanta', [
            field_def('special_needs', 'Student me nevoja te vecanta', 'yesno', document_data_value($documents, 'special_needs_confirmation', 'special_needs', 'jo')),
            field_def('description', 'Pershkrim i shkurter', 'textarea', document_data_value($documents, 'special_needs_confirmation', 'description', '')),
        ], 'Te dhenat jane te pranuara, autentifikuara dhe licencuara nga institucioni perkates shendetesor/social.', $documents['special_needs_confirmation'] ?? []),
        'deficit_program_evidence' => document_section('Deshmi per Drejtime Deficitare', [
            field_def('study_field', 'Drejtimi i studimit', 'text', document_data_value($documents, 'deficit_program_evidence', 'study_field', $program)),
            field_def('is_deficit', 'A eshte drejtim deficitar', 'yesno', in_array($program, $deficitPrograms, true) ? 'po' : document_data_value($documents, 'deficit_program_evidence', 'is_deficit', 'jo')),
        ], 'Kjo e dhene mund te llogaritet automatikisht nga drejtimi i studentit.', $documents['deficit_program_evidence'] ?? []),
    ];
}

function document_section(string $title, array $fields, string $message, array $data = [], string $requiredNote = ''): array
{
    foreach ($fields as $field) {
        $data[$field['name']] ??= $field['default'];
    }
    return compact('title', 'fields', 'message', 'data') + ['required_note' => $requiredNote];
}

function field_def(string $name, string $label, string $type, mixed $default = '', array $options = []): array
{
    return compact('name', 'label', 'type', 'default', 'options');
}

function document_data_value(array $documents, string $section, string $field, mixed $fallback = ''): string
{
    return is_scalar($documents[$section][$field] ?? null) ? (string) $documents[$section][$field] : (string) $fallback;
}

function document_field_value(array $data, array $field): string
{
    return is_scalar($data[$field['name']] ?? null) ? (string) $data[$field['name']] : '';
}

function render_document_input(array $field, string $value): void
{
    $name = 'document_data[' . $field['name'] . ']';
    ?>
    <label><?= e($field['label']) ?>
        <?php if ($field['type'] === 'select'): ?>
            <select name="<?= e($name) ?>">
                <option value="">Zgjedh</option>
                <?php foreach ($field['options'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= selected($option, $value) ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        <?php elseif ($field['type'] === 'yesno'): ?>
            <select name="<?= e($name) ?>">
                <option value="po" <?= selected('po', strtolower($value)) ?>>po</option>
                <option value="jo" <?= selected('jo', strtolower($value)) ?>>jo</option>
            </select>
        <?php elseif ($field['type'] === 'textarea'): ?>
            <textarea name="<?= e($name) ?>"><?= e($value) ?></textarea>
        <?php else: ?>
            <input name="<?= e($name) ?>" type="<?= e($field['type']) ?>" value="<?= e($value) ?>">
        <?php endif; ?>
    </label>
    <?php
}

function render_student_profile_form(array $profile, string $firstName, string $lastName): void
{
    render_structured_student_documents($profile, $firstName, $lastName, true);
    return;

    $education = decode_previous_education($profile['previous_education'] ?? '');
    $hasCourse = education_has_any($education, 'courses.course');
    $hasCraft = education_has_any($education, 'crafts.craft');
    $bankCard = decode_bank_card_metadata($profile['bank_branch'] ?? '');
    $hasBank = trim(implode('', [
        (string) ($profile['bank_name'] ?? ''),
        (string) ($profile['bank_account_holder'] ?? ''),
        (string) ($profile['bank_account_number'] ?? ''),
        (string) ($profile['bank_iban'] ?? ''),
        (string) ($bankCard['expiry'] ?? ''),
        (string) ($bankCard['cvv'] ?? ''),
    ])) !== '';
    $currentStudies = study_entries($education, 'current');
    $pastStudies = study_entries($education, 'past');
    $hasCurrentStudy = $currentStudies !== [];
    $hasPastStudy = $pastStudies !== [];
    $studyInstitutionTypes = (array) ($education['studies']['student_meta']['institution_type'] ?? []);
    ?>
    <section class="page-head">
        <div>
            <h1>Ndrysho te dhenat personale</h1>
            <p class="muted-text">Dokumentet e verifikuara mbeten te lidhura me institucionet simuluese.</p>
        </div>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?page=profile">Kthehu te profili</a>
    </section>

    <section class="panel">
        <div class="form grid-form profile-edit-form">
            <div class="form-section-card profile-collapsible-section wide <?= section_open_class('personal') ?>" data-section="personal">
                <?php render_section_header('personal_data'); ?>
                <div class="section-view-body readonly-grid">
                    <?php render_readonly_field('first_name', $firstName); ?>
                    <?php render_readonly_field('last_name', $lastName); ?>
                    <?php render_readonly_field('email', $profile['email'] ?? ''); ?>
                    <?php render_readonly_field('birth_date', $profile['birth_date'] ?? ''); ?>
                    <?php render_readonly_field('birth_place', $profile['birth_place'] ?? ''); ?>
                    <?php render_readonly_field('residence', $profile['residence'] ?? ($profile['city'] ?? '')); ?>
                    <?php render_readonly_field('gender', $profile['gender'] ?? ''); ?>
                    <?php render_readonly_field('annual_circulation', money_label($profile['annual_circulation'] ?? null)); ?>
                    <?php render_readonly_field('employment_status', $profile['employment_status'] ?? ''); ?>
                </div>
                <form method="post" class="section-edit-body">
                    <?php render_section_form_fields('personal'); ?>
                    <div class="form-section-grid">
                        <label><?= e(t('first_name')) ?><input name="first_name" value="<?= e($firstName) ?>" required></label>
                        <label><?= e(t('last_name')) ?><input name="last_name" value="<?= e($lastName) ?>" required></label>
                        <label><?= e(t('email')) ?><input name="email" type="email" value="<?= e($profile['email'] ?? '') ?>" required></label>
                        <label><?= e(t('birth_date')) ?><input name="birth_date" type="date" value="<?= e($profile['birth_date'] ?? '') ?>"></label>
                        <label><?= e(t('birth_place')) ?><input name="birth_place" value="<?= e($profile['birth_place'] ?? '') ?>"></label>
                        <label><?= e(t('residence')) ?><input name="residence" value="<?= e($profile['residence'] ?? ($profile['city'] ?? '')) ?>"></label>
                        <label><?= e(t('gender')) ?>
                            <select name="gender">
                                <option value="Mashkull" <?= selected('Mashkull', $profile['gender'] ?? '') ?>>Mashkull</option>
                                <option value="Femer" <?= selected('Femer', $profile['gender'] ?? '') ?>>Femer</option>
                                <option value="Tjeter" <?= selected('Tjeter', $profile['gender'] ?? 'Tjeter') ?>>Tjeter</option>
                            </select>
                        </label>
                        <label><?= e(t('annual_circulation')) ?><input name="annual_circulation" type="number" min="0" step="0.01" value="<?= e((string) ($profile['annual_circulation'] ?? '0')) ?>"></label>
                        <label><?= e(t('employment_status')) ?>
                            <select name="employment_status">
                                <option value="I papune" <?= selected('I papune', $profile['employment_status'] ?? '') ?>>I papune</option>
                                <option value="I punesuar" <?= selected('I punesuar', $profile['employment_status'] ?? '') ?>>I punesuar</option>
                            </select>
                        </label>
                    </div>
                    <?php render_profile_form_actions('personal'); ?>
                </form>
            </div>

            <div class="form-section-card education-section-card profile-collapsible-section wide <?= section_open_class('education') ?>" data-section="education">
                <?php render_section_header('previous_education'); ?>
                <div class="section-view-body readonly-stack">
                    <div class="readonly-subsection">
                        <h3>Shkollimi Elementar</h3>
                        <div class="readonly-grid">
                            <?php render_readonly_field('Emri i shkolles', education_value($education, 'schools.elementary.school_name')); ?>
                            <?php render_readonly_field('Viti i regjistrimit', education_value($education, 'schools.elementary.start_year')); ?>
                            <?php render_readonly_field('Viti i perfundimit', education_value($education, 'schools.elementary.end_year')); ?>
                            <?php render_readonly_field('Nota mesatare', education_value($education, 'schools.elementary.average_grade')); ?>
                        </div>
                    </div>
                    <div class="readonly-subsection">
                        <h3>Shkollimi i Mesem i Ulet</h3>
                        <div class="readonly-grid">
                            <?php render_readonly_field('Emri i shkolles', education_value($education, 'schools.lower_secondary.school_name')); ?>
                            <?php render_readonly_field('Viti i regjistrimit', education_value($education, 'schools.lower_secondary.start_year')); ?>
                            <?php render_readonly_field('Viti i perfundimit', education_value($education, 'schools.lower_secondary.end_year')); ?>
                            <?php render_readonly_field('Nota mesatare', education_value($education, 'schools.lower_secondary.average_grade')); ?>
                        </div>
                    </div>
                    <div class="readonly-subsection">
                        <h3>Shkollimi i Mesem i Larte</h3>
                        <div class="readonly-grid">
                            <?php render_readonly_field('Lloji i shkolles', implode(', ', (array) ($education['schools']['secondary']['school_type'] ?? []))); ?>
                            <?php render_readonly_field('Lloji i Shkolles se Mesme', education_value($education, 'schools.secondary.school_kind')); ?>
                            <?php render_readonly_field('Fusha e Studimit', education_value($education, 'schools.secondary.study_field')); ?>
                            <?php render_readonly_field('Emri i shkolles', education_value($education, 'schools.secondary.school_name')); ?>
                            <?php render_readonly_field('Vendi ku ndodhet shkolla', education_value($education, 'schools.secondary.school_location')); ?>
                            <?php render_readonly_field('Viti i regjistrimit', education_value($education, 'schools.secondary.start_year')); ?>
                            <?php render_readonly_field('Viti i perfundimit', education_value($education, 'schools.secondary.end_year')); ?>
                            <?php render_readonly_field('Nota mesatare', education_value($education, 'schools.secondary.average_grade')); ?>
                        </div>
                    </div>
                </div>
                <form method="post" class="section-edit-body">
                    <?php render_section_form_fields('education'); ?>
                <div class="form-subsection">
                    <h3>Shkollimi Elementar</h3>
                    <div class="form-section-grid">
                        <label>Emri i shkolles<input name="previous_education[schools][elementary][school_name]" value="<?= e(education_value($education, 'schools.elementary.school_name')) ?>"></label>
                        <label>Viti i regjistrimit<input name="previous_education[schools][elementary][start_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'schools.elementary.start_year')) ?>"></label>
                        <label>Viti i perfundimit<input name="previous_education[schools][elementary][end_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'schools.elementary.end_year')) ?>"></label>
                        <label>Nota mesatare<input name="previous_education[schools][elementary][average_grade]" type="number" min="1" max="5" step="0.01" value="<?= e(education_value($education, 'schools.elementary.average_grade')) ?>"></label>
                    </div>
                </div>
                <div class="form-subsection">
                    <h3>Shkollimi i Mesem i Ulet</h3>
                    <div class="form-section-grid">
                        <label>Emri i shkolles<input name="previous_education[schools][lower_secondary][school_name]" value="<?= e(education_value($education, 'schools.lower_secondary.school_name')) ?>"></label>
                        <label>Viti i regjistrimit<input name="previous_education[schools][lower_secondary][start_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'schools.lower_secondary.start_year')) ?>"></label>
                        <label>Viti i perfundimit<input name="previous_education[schools][lower_secondary][end_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'schools.lower_secondary.end_year')) ?>"></label>
                        <label>Nota mesatare<input name="previous_education[schools][lower_secondary][average_grade]" type="number" min="1" max="5" step="0.01" value="<?= e(education_value($education, 'schools.lower_secondary.average_grade')) ?>"></label>
                    </div>
                </div>
                <div class="form-subsection">
                    <div class="form-subsection-head">
                        <h3>Shkollimi i Mesem i Larte</h3>
                        <div class="checkbox-group inline-checks">
                            <label class="check"><input type="checkbox" name="previous_education[schools][secondary][school_type][]" value="Shkolle Publike" <?= checked_bool(education_has_option($education, 'schools.secondary.school_type', 'Shkolle Publike')) ?>> Shkolle Publike</label>
                            <label class="check"><input type="checkbox" name="previous_education[schools][secondary][school_type][]" value="Shkolle Private" <?= checked_bool(education_has_option($education, 'schools.secondary.school_type', 'Shkolle Private')) ?>> Shkolle Private</label>
                        </div>
                    </div>
                    <div class="form-section-grid">
                        <label>Lloji i Shkolles se Mesme
                            <select class="secondary-school-kind" name="previous_education[schools][secondary][school_kind]" data-study-target="secondaryStudyField">
                                <?php foreach (['', 'Gjimnaz', 'Shkolla e mesme teknike', 'Shkolla e mesme e mjekesise', 'Shkolla e mesme profesionale', 'Shkolla e mesme e ekonomise', 'Shkolla e mesme e muzikes'] as $kind): ?>
                                    <option value="<?= e($kind) ?>" <?= selected($kind, education_value($education, 'schools.secondary.school_kind')) ?>><?= e($kind !== '' ? $kind : 'Zgjedh llojin') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="study-field-wrap is-hidden" id="secondaryStudyField">Fusha e Studimit
                            <select class="study-field-select" name="previous_education[schools][secondary][study_field]" data-current="<?= e(education_value($education, 'schools.secondary.study_field')) ?>">
                                <option value="">Zgjedh fushen</option>
                            </select>
                        </label>
                        <label>Emri i shkolles<input name="previous_education[schools][secondary][school_name]" value="<?= e(education_value($education, 'schools.secondary.school_name')) ?>"></label>
                        <label>Vendi ku ndodhet shkolla<input name="previous_education[schools][secondary][school_location]" value="<?= e(education_value($education, 'schools.secondary.school_location')) ?>"></label>
                        <label>Viti i regjistrimit<input name="previous_education[schools][secondary][start_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'schools.secondary.start_year')) ?>"></label>
                        <label>Viti i perfundimit<input name="previous_education[schools][secondary][end_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'schools.secondary.end_year')) ?>"></label>
                        <label>Nota mesatare<input name="previous_education[schools][secondary][average_grade]" type="number" min="1" max="5" step="0.01" value="<?= e(education_value($education, 'schools.secondary.average_grade')) ?>"></label>
                    </div>
                </div>
                    <?php render_profile_form_actions('education'); ?>
                </form>
            </div>

            <div class="form-section-card education-section-card profile-collapsible-section wide <?= section_open_class('courses') ?>" data-section="courses">
                <?php render_section_header('completed_courses', !$hasCourse ? 'none' : null); ?>
                <?php if ($hasCourse): ?>
                    <div class="section-view-body readonly-grid">
                        <?php render_readonly_field('Lloji i kursit', education_value($education, 'courses.course.type')); ?>
                        <?php render_readonly_field('Emri i institucionit', education_value($education, 'courses.course.institution')); ?>
                        <?php render_readonly_field('Emri i ligjeruesit/es', education_value($education, 'courses.course.lecturer')); ?>
                        <?php render_readonly_field('Niveli', education_value($education, 'courses.course.level')); ?>
                        <?php render_readonly_field('Kohezgjatja e kursit', education_value($education, 'courses.course.duration')); ?>
                        <?php render_readonly_field('Viti i fillimit', education_value($education, 'courses.course.start_year')); ?>
                        <?php render_readonly_field('Viti i perfundimit', education_value($education, 'courses.course.end_year')); ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="section-edit-body">
                    <?php render_section_form_fields('courses'); ?>
                <button class="btn btn-outline optional-section-toggle <?= $hasCourse ? 'is-hidden' : '' ?>" type="button" data-target="courseFields" onclick="showOptionalSection('courseFields', this)">Shto kurs</button>
                <div class="form-subsection optional-section-body <?= $hasCourse ? '' : 'is-hidden' ?>" id="courseFields">
                    <h3>Kursi</h3>
                    <div class="form-section-grid">
                        <label>Lloji i kursit
                            <select name="previous_education[courses][course][type]">
                                <?php foreach (['', 'Gjuhesor', 'Shkencor', 'Laboratorik'] as $type): ?>
                                    <option value="<?= e($type) ?>" <?= selected($type, education_value($education, 'courses.course.type')) ?>><?= e($type !== '' ? $type : 'Zgjedh llojin e kursit') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Emri i institucionit<input name="previous_education[courses][course][institution]" value="<?= e(education_value($education, 'courses.course.institution')) ?>"></label>
                        <label>Emri i ligjeruesit/es<input name="previous_education[courses][course][lecturer]" value="<?= e(education_value($education, 'courses.course.lecturer')) ?>"></label>
                        <label>Niveli i gjuhes / niveli shkollor i nxenesit<input name="previous_education[courses][course][level]" value="<?= e(education_value($education, 'courses.course.level')) ?>"></label>
                        <label>Kohezgjatja e kursit<input name="previous_education[courses][course][duration]" value="<?= e(education_value($education, 'courses.course.duration')) ?>"></label>
                        <label>Viti i fillimit<input name="previous_education[courses][course][start_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'courses.course.start_year')) ?>"></label>
                        <label>Viti i perfundimit<input name="previous_education[courses][course][end_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'courses.course.end_year')) ?>"></label>
                    </div>
                </div>
                    <?php render_profile_form_actions('courses'); ?>
                </form>
            </div>

            <div class="form-section-card education-section-card profile-collapsible-section wide <?= section_open_class('crafts') ?>" data-section="crafts">
                <?php render_section_header('vocational_skills', !$hasCraft ? 'none' : null); ?>
                <?php if ($hasCraft): ?>
                    <div class="section-view-body readonly-grid">
                        <?php render_readonly_field('Profesioni', education_value($education, 'crafts.craft.profession')); ?>
                        <?php render_readonly_field('Emri i institucionit', education_value($education, 'crafts.craft.institution')); ?>
                        <?php render_readonly_field('Shefi i punes', education_value($education, 'crafts.craft.supervisor')); ?>
                        <?php render_readonly_field('Kohezgjatja e zanatit', education_value($education, 'crafts.craft.duration')); ?>
                        <?php render_readonly_field('Viti i fillimit', education_value($education, 'crafts.craft.start_year')); ?>
                        <?php render_readonly_field('Viti i perfundimit', education_value($education, 'crafts.craft.end_year')); ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="section-edit-body">
                    <?php render_section_form_fields('crafts'); ?>
                <button class="btn btn-outline optional-section-toggle <?= $hasCraft ? 'is-hidden' : '' ?>" type="button" data-target="craftFields" onclick="showOptionalSection('craftFields', this)">Shto zanat</button>
                <div class="form-subsection optional-section-body <?= $hasCraft ? '' : 'is-hidden' ?>" id="craftFields">
                    <h3>Zanati</h3>
                    <div class="form-section-grid">
                        <label>Profesioni<input name="previous_education[crafts][craft][profession]" value="<?= e(education_value($education, 'crafts.craft.profession')) ?>"></label>
                        <label>Emri i institucionit<input name="previous_education[crafts][craft][institution]" value="<?= e(education_value($education, 'crafts.craft.institution')) ?>"></label>
                        <label>Shefi i punes<input name="previous_education[crafts][craft][supervisor]" value="<?= e(education_value($education, 'crafts.craft.supervisor')) ?>"></label>
                        <label>Kohezgjatja e zanatit<input name="previous_education[crafts][craft][duration]" value="<?= e(education_value($education, 'crafts.craft.duration')) ?>"></label>
                        <label>Viti i fillimit<input name="previous_education[crafts][craft][start_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'crafts.craft.start_year')) ?>"></label>
                        <label>Viti i perfundimit<input name="previous_education[crafts][craft][end_year]" type="number" min="1950" max="2100" value="<?= e(education_value($education, 'crafts.craft.end_year')) ?>"></label>
                    </div>
                </div>
                    <?php render_profile_form_actions('crafts'); ?>
                </form>
            </div>

            <div class="form-section-card profile-collapsible-section wide <?= section_open_class('student') ?>" data-section="student">
                <?php render_section_header('student'); ?>
                <div class="section-view-body readonly-stack">
                    <div class="readonly-subsection">
                        <h3>Te dhenat studentore</h3>
                        <div class="readonly-grid">
                            <?php render_readonly_field('university', $profile['university'] ?? ''); ?>
                            <?php render_readonly_field('city', $profile['city'] ?? ''); ?>
                            <?php render_readonly_field('study_level', education_value($education, 'studies.student_meta.study_level')); ?>
                            <?php render_readonly_field('study_year', education_value($education, 'studies.student_meta.study_year')); ?>
                            <?php render_readonly_field('institution_type', implode(', ', $studyInstitutionTypes)); ?>
                            <?php render_readonly_field('average_grade', isset($profile['average_grade']) ? (string) $profile['average_grade'] : ''); ?>
                            <?php render_readonly_field('active_student', !empty($profile['student_active']) ? 'yes' : 'no'); ?>
                            <?php render_readonly_field('veteran_child', yes_no((int) ($profile['is_veteran_child'] ?? 0))); ?>
                            <?php render_readonly_field('orphan', yes_no((int) ($profile['is_orphan'] ?? 0))); ?>
                            <?php render_readonly_field('social_assistance', yes_no((int) ($profile['receives_social_assistance'] ?? 0))); ?>
                        </div>
                    </div>
                    <?php foreach ($currentStudies as $index => $study): ?>
                        <div class="readonly-subsection">
                            <h3>Studimet e reja <?= count($currentStudies) > 1 ? e((string) ($index + 1)) : '' ?></h3>
                            <div class="readonly-grid">
                                <?php render_readonly_field('Institucioni', study_entry_value($study, 'institution')); ?>
                                <?php render_readonly_field('Lloji i institucionit', implode(', ', (array) ($study['institution_type'] ?? []))); ?>
                                <?php render_readonly_field('Programi / drejtimi', study_entry_value($study, 'program')); ?>
                                <?php render_readonly_field('Niveli i studimeve', study_entry_value($study, 'level')); ?>
                                <?php render_readonly_field('Viti i studimeve', study_entry_value($study, 'study_year')); ?>
                                <?php render_readonly_field('Viti i fillimit', study_entry_value($study, 'start_year')); ?>
                                <?php render_readonly_field('Nota mesatare', study_entry_value($study, 'average_grade')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($pastStudies as $index => $study): ?>
                        <div class="readonly-subsection">
                            <h3>Studimet e kaluara <?= count($pastStudies) > 1 ? e((string) ($index + 1)) : '' ?></h3>
                            <div class="readonly-grid">
                                <?php render_readonly_field('Institucioni', study_entry_value($study, 'institution')); ?>
                                <?php render_readonly_field('Lloji i institucionit', implode(', ', (array) ($study['institution_type'] ?? []))); ?>
                                <?php render_readonly_field('Programi / drejtimi', study_entry_value($study, 'program')); ?>
                                <?php render_readonly_field('Niveli i studimeve', study_entry_value($study, 'level')); ?>
                                <?php render_readonly_field('Viti i fillimit', study_entry_value($study, 'start_year')); ?>
                                <?php render_readonly_field('Viti i perfundimit', study_entry_value($study, 'end_year')); ?>
                                <?php render_readonly_field('Nota mesatare', study_entry_value($study, 'average_grade')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" class="section-edit-body">
                    <?php render_section_form_fields('student'); ?>
                    <div class="form-section-grid">
                        <label><?= e(t('university')) ?>
                            <select name="university">
                                <option <?= selected('Universiteti Kadri Zeka', $profile['university'] ?? '') ?>>Universiteti Kadri Zeka</option>
                                <option <?= selected('Universiteti Hasan Prishtina', $profile['university'] ?? '') ?>>Universiteti Hasan Prishtina</option>
                                <option <?= selected('Universiteti Haxhi Zeka', $profile['university'] ?? '') ?>>Universiteti Haxhi Zeka</option>
                            </select>
                        </label>
                        <label><?= e(t('city')) ?>
                            <select name="city">
                                <option <?= selected('Kamenice', $profile['city'] ?? '') ?>>Kamenice</option>
                                <option <?= selected('Gjilan', $profile['city'] ?? '') ?>>Gjilan</option>
                                <option <?= selected('Viti', $profile['city'] ?? '') ?>>Viti</option>
                                <option <?= selected('Ferizaj', $profile['city'] ?? '') ?>>Ferizaj</option>
                            </select>
                        </label>
                        <label><?= e(t('study_level')) ?>
                            <select name="previous_education[studies][student_meta][study_level]">
                                <?php foreach (['', 'Bachelor', 'Master', 'Doktorature', 'Studime profesionale'] as $level): ?>
                                    <option value="<?= e($level) ?>" <?= selected($level, education_value($education, 'studies.student_meta.study_level')) ?>><?= e($level !== '' ? $level : 'Zgjedh nivelin') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label><?= e(t('study_year')) ?><input name="previous_education[studies][student_meta][study_year]" type="number" min="1" max="8" value="<?= e(education_value($education, 'studies.student_meta.study_year')) ?>"></label>
                        <div class="checkbox-group inline-checks">
                            <label class="check"><input type="checkbox" name="previous_education[studies][student_meta][institution_type][]" value="Universitet Publik" <?= checked_bool(education_has_option($education, 'studies.student_meta.institution_type', 'Universitet Publik')) ?>> Universitet Publik</label>
                            <label class="check"><input type="checkbox" name="previous_education[studies][student_meta][institution_type][]" value="Kolegj Privat" <?= checked_bool(education_has_option($education, 'studies.student_meta.institution_type', 'Kolegj Privat')) ?>> Kolegj Privat</label>
                        </div>
                        <label><?= e(t('average_grade')) ?><input name="average_grade" type="number" min="6" max="10" step="0.01" value="<?= e((string) ($profile['average_grade'] ?? '8.00')) ?>" required></label>
                        <label class="check"><input type="checkbox" name="student_active" <?= checked_bool(!empty($profile['student_active'])) ?>> Student aktiv</label>
                        <label class="check"><input type="checkbox" name="is_veteran_child" <?= checked_bool(!empty($profile['is_veteran_child'])) ?>> Femije veterani</label>
                        <label class="check"><input type="checkbox" name="is_orphan" <?= checked_bool(!empty($profile['is_orphan'])) ?>> Jetim</label>
                        <label class="check"><input type="checkbox" name="receives_social_assistance" <?= checked_bool(!empty($profile['receives_social_assistance'])) ?>> Pranon ndihme sociale</label>
                    </div>
                    <div class="study-entry-list" id="currentStudyList">
                        <?php foreach ($currentStudies as $index => $study): ?>
                            <?php render_study_entry_form('current', (string) $index, $study, 'Studimet e reja', false); ?>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-outline add-study-entry" type="button" data-template="currentStudyTemplate" data-target="currentStudyList">Shto studime te reja</button>
                    <div class="study-entry-list" id="pastStudyList">
                        <?php foreach ($pastStudies as $index => $study): ?>
                            <?php render_study_entry_form('past', (string) $index, $study, 'Studimet e kaluara', true); ?>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-outline add-study-entry" type="button" data-template="pastStudyTemplate" data-target="pastStudyList">Shto studime te kaluara</button>
                    <template id="currentStudyTemplate">
                        <?php render_study_entry_form('current', '__INDEX__', [], 'Studimet e reja', false); ?>
                    </template>
                    <template id="pastStudyTemplate">
                        <?php render_study_entry_form('past', '__INDEX__', [], 'Studimet e kaluara', true); ?>
                    </template>
                    <?php render_profile_form_actions('student'); ?>
                </form>
            </div>

            <div class="form-section-card profile-collapsible-section wide <?= section_open_class('bank') ?>" data-section="bank">
                <?php render_section_header('bank', !$hasBank ? 'none' : null); ?>
                <?php if ($hasBank): ?>
                    <div class="section-view-body readonly-grid">
                        <?php render_readonly_field('Banka', $profile['bank_name'] ?? ''); ?>
                        <?php render_readonly_field('Mbajtesi i karteles', $profile['bank_account_holder'] ?? ''); ?>
                        <?php render_readonly_field('Numri i karteles', $profile['bank_account_number'] ?? ''); ?>
                        <?php render_readonly_field('IBAN', $profile['bank_iban'] ?? ''); ?>
                        <?php render_readonly_field('Data e skadences', $bankCard['expiry'] ?? ''); ?>
                        <?php render_readonly_field('CVV', $bankCard['cvv'] ?? ''); ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="section-edit-body">
                    <?php render_section_form_fields('bank'); ?>
                    <button class="btn btn-outline optional-section-toggle <?= $hasBank ? 'is-hidden' : '' ?>" type="button" data-target="bankFields" onclick="showOptionalSection('bankFields', this)">Shto banke</button>
                    <div class="optional-section-body <?= $hasBank ? '' : 'is-hidden' ?>" id="bankFields">
                    <div class="form-section-grid">
                    <label>Banka<input name="bank_name" value="<?= e($profile['bank_name'] ?? '') ?>"></label>
                    <label>Mbajtesi i karteles<input name="bank_account_holder" value="<?= e($profile['bank_account_holder'] ?? '') ?>"></label>
                    <label>Numri i karteles<input class="card-number-input" name="bank_account_number" inputmode="numeric" placeholder="0000 0000 0000 0000" maxlength="19" pattern="[0-9 ]{19}" value="<?= e($profile['bank_account_number'] ?? '') ?>"></label>
                    <label>IBAN<input name="bank_iban" placeholder="XK05 1212 0123 4567 8906" value="<?= e($profile['bank_iban'] ?? '') ?>"></label>
                    <label>Data e skadences<input class="card-expiry-input" name="bank_card_expiry" inputmode="numeric" placeholder="MM/YY" maxlength="5" pattern="(0[1-9]|1[0-2])/[0-9]{2}" value="<?= e($bankCard['expiry']) ?>"></label>
                    <label>CVV<input class="cvv-input" name="bank_card_cvv" inputmode="numeric" maxlength="3" pattern="[0-9]{3}" value="<?= e($bankCard['cvv']) ?>"></label>
                    </div>
                    </div>
                    <?php render_profile_form_actions('bank'); ?>
                </form>
            </div>
        </div>
    </section>
    <?php
}

function render_study_entry_form(string $kind, string $index, array $study, string $title, bool $includeEndYear): void
{
    $base = 'previous_education[studies][' . $kind . '][' . $index . ']';
    ?>
    <div class="form-subsection study-entry">
        <div class="form-subsection-head">
            <h3><?= e($title) ?></h3>
            <button class="btn btn-outline remove-study-entry" type="button">Fshij</button>
        </div>
        <div class="form-section-grid">
            <label>Institucioni<input name="<?= e($base) ?>[institution]" value="<?= e(study_entry_value($study, 'institution')) ?>"></label>
            <div class="checkbox-group inline-checks">
                <label class="check"><input type="checkbox" name="<?= e($base) ?>[institution_type][]" value="Universitet Publik" <?= checked_bool(study_entry_has_option($study, 'institution_type', 'Universitet Publik')) ?>> Universitet Publik</label>
                <label class="check"><input type="checkbox" name="<?= e($base) ?>[institution_type][]" value="Kolegj Privat" <?= checked_bool(study_entry_has_option($study, 'institution_type', 'Kolegj Privat')) ?>> Kolegj Privat</label>
            </div>
            <label>Programi / drejtimi<input name="<?= e($base) ?>[program]" value="<?= e(study_entry_value($study, 'program')) ?>"></label>
            <label>Niveli i studimeve
                <select name="<?= e($base) ?>[level]">
                    <?php foreach (['', 'Bachelor', 'Master', 'Doktorature', 'Studime profesionale'] as $level): ?>
                        <option value="<?= e($level) ?>" <?= selected($level, study_entry_value($study, 'level')) ?>><?= e($level !== '' ? $level : 'Zgjedh nivelin') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (!$includeEndYear): ?>
                <label>Viti i studimeve<input name="<?= e($base) ?>[study_year]" type="number" min="1" max="8" value="<?= e(study_entry_value($study, 'study_year')) ?>"></label>
            <?php endif; ?>
            <label>Viti i fillimit<input name="<?= e($base) ?>[start_year]" type="number" min="1950" max="2100" value="<?= e(study_entry_value($study, 'start_year')) ?>"></label>
            <?php if ($includeEndYear): ?>
                <label>Viti i perfundimit<input name="<?= e($base) ?>[end_year]" type="number" min="1950" max="2100" value="<?= e(study_entry_value($study, 'end_year')) ?>"></label>
            <?php endif; ?>
            <label>Nota mesatare<input name="<?= e($base) ?>[average_grade]" type="number" min="6" max="10" step="0.01" value="<?= e(study_entry_value($study, 'average_grade')) ?>"></label>
        </div>
    </div>
    <?php
}

function render_section_header(string $title, ?string $note = null): void
{
    ?>
    <div class="section-card-head">
        <h2><?= e(t($title)) ?><?= $note ? ' <span>(' . e(t($note)) . ')</span>' : '' ?></h2>
        <button class="section-edit-button" type="button" aria-label="<?= e(t('edit')) ?> <?= e(t($title)) ?>">✎</button>
    </div>
    <?php
}

function render_profile_form_actions(string $section): void
{
    ?>
    <div class="form-actions section-actions">
        <button class="btn"><?= e(t('save_changes')) ?></button>
        <a class="btn btn-outline cancel-section-edit" href="<?= BASE_URL ?>/index.php?page=profile&edit=1"><?= e(t('cancel')) ?></a>
    </div>
    <?php
}

function render_readonly_field(string $label, mixed $value): void
{
    $text = trim((string) $value);
    ?>
    <div class="readonly-field">
        <span><?= e(t($label)) ?></span>
        <strong><?= e($text !== '' ? t($text) : t('not_filled')) ?></strong>
    </div>
    <?php
}

function render_section_form_fields(string $section): void
{
    ?>
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="update_profile">
    <input type="hidden" name="section_name" value="<?= e($section) ?>">
    <?php
}

function section_open_class(string $section): string
{
    return ($_GET['open_section'] ?? '') === $section ? 'is-editing' : '';
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
        <span><?= e(t($label)) ?></span>
        <strong><?= e($value !== null && $value !== '' ? t($value) : '-') ?></strong>
    </div>
    <?php
}

function yes_no(int $value): string
{
    return $value === 1 ? 'yes' : 'no';
}

function yes_value(string $value): bool
{
    return in_array(strtolower(trim($value)), ['po', 'yes', '1', 'true'], true);
}

function social_status_from_profile_flags(?bool $socialAssistance = null, ?bool $veteranChild = null, ?bool $orphan = null): string
{
    $profile = current_student_profile();
    $statuses = [];

    if ($veteranChild ?? !empty($profile['is_veteran_child'])) {
        $statuses[] = 'Femije veterani';
    }

    if ($orphan ?? !empty($profile['is_orphan'])) {
        $statuses[] = 'Jetim';
    }

    if ($socialAssistance ?? !empty($profile['receives_social_assistance'])) {
        $statuses[] = 'Ndihme sociale';
    }

    return $statuses ? implode(', ', $statuses) : 'Standard';
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
        'verified' => t('verified'),
        'missing' => t('missing'),
        'not_required' => t('not_required'),
    ][$status] ?? t($status);
}

function social_status_from_flags(): string
{
    $statuses = [];

    if (isset($_POST['is_veteran_child'])) {
        $statuses[] = 'Femije veterani';
    }

    if (isset($_POST['is_orphan'])) {
        $statuses[] = 'Jetim';
    }

    if (isset($_POST['receives_social_assistance'])) {
        $statuses[] = 'Ndihme sociale';
    }

    return $statuses ? implode(', ', $statuses) : 'Standard';
}

function decode_previous_education(?string $value): array
{
    if (!$value) {
        return [];
    }

    $decoded = json_decode($value, true);

    return is_array($decoded) ? $decoded : [];
}

function encode_previous_education(mixed $value): string
{
    if (!is_array($value)) {
        return '';
    }

    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

function education_value(array $education, string $path): string
{
    $current = $education;

    foreach (explode('.', $path) as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return '';
        }

        $current = $current[$segment];
    }

    return is_scalar($current) ? (string) $current : '';
}

function education_has_option(array $education, string $path, string $option): bool
{
    $current = $education;

    foreach (explode('.', $path) as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return false;
        }

        $current = $current[$segment];
    }

    return is_array($current)
        ? in_array($option, $current, true)
        : (string) $current === $option;
}

function education_has_any(array $education, string $path): bool
{
    $current = $education;

    foreach (explode('.', $path) as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return false;
        }

        $current = $current[$segment];
    }

    if (!is_array($current)) {
        return trim((string) $current) !== '';
    }

    return array_has_filled_value($current);
}

function study_entries(array $education, string $key): array
{
    $items = $education['studies'][$key] ?? [];
    if (!is_array($items) || $items === []) {
        return [];
    }

    if (is_list_array($items)) {
        return array_values(array_filter($items, fn($item) => is_array($item) && array_has_filled_value($item)));
    }

    return array_has_filled_value($items) ? [$items] : [];
}

function study_entry_value(array $entry, string $key): string
{
    $value = $entry[$key] ?? '';
    return is_scalar($value) ? (string) $value : '';
}

function study_entry_has_option(array $entry, string $key, string $option): bool
{
    $value = $entry[$key] ?? [];
    return is_array($value) ? in_array($option, $value, true) : (string) $value === $option;
}

function is_list_array(array $items): bool
{
    return array_keys($items) === range(0, count($items) - 1);
}

function array_has_filled_value(array $items): bool
{
    foreach ($items as $value) {
        if (is_array($value) && array_has_filled_value($value)) {
            return true;
        }

        if (!is_array($value) && trim((string) $value) !== '') {
            return true;
        }
    }

    return false;
}

function validate_year_ranges(array $items): void
{
    $start = isset($items['start_year']) && $items['start_year'] !== '' ? (int) $items['start_year'] : null;
    $end = isset($items['end_year']) && $items['end_year'] !== '' ? (int) $items['end_year'] : null;

    if ($start !== null && $end !== null && $start > $end) {
        throw new RuntimeException('Invalid year range');
    }

    foreach ($items as $value) {
        if (is_array($value)) {
            validate_year_ranges($value);
        }
    }
}

function encode_bank_card_metadata(array $data): string
{
    return json_encode([
        'expiry' => trim($data['bank_card_expiry'] ?? ''),
        'cvv' => trim($data['bank_card_cvv'] ?? ''),
    ], JSON_UNESCAPED_UNICODE);
}

function decode_bank_card_metadata(?string $value): array
{
    if (!$value) {
        return ['expiry' => '', 'cvv' => ''];
    }

    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return [
            'expiry' => (string) ($decoded['expiry'] ?? ''),
            'cvv' => (string) ($decoded['cvv'] ?? ''),
        ];
    }

    return ['expiry' => '', 'cvv' => preg_match('/^\d{3,4}$/', $value) ? $value : ''];
}

function allowed_value(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}
