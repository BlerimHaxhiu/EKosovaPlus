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
        'Klikoni këtu për të ngarkuar dokumentin' => 'Click here to upload the document',
        'Asnjë dokument i zgjedhur' => 'No document selected',
        'Ngarko' => 'Upload',
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
        'Klikoni këtu për të ngarkuar dokumentin' => 'Kliknite ovde da otpremite dokument',
        'Asnjë dokument i zgjedhur' => 'Nijedan dokument nije izabran',
        'Ngarko' => 'Otpremi',
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
        flash(t('scholarship_required_fields'), 'error');
        redirect('provider');
    }

    if ($id > 0) {
        $data[] = $id;
        $data[] = current_user()['id'];
        $stmt = db()->prepare('UPDATE scholarships SET title=?, description=?, amount=?, deadline=?, min_grade=?, required_university=?, required_city=?, required_social_status=?, requires_veteran_child=?, requires_orphan=?, requires_social_assistance=?, status=? WHERE id=? AND provider_id=?');
        $stmt->execute($data);
        flash(t('scholarship_updated'));
    } else {
        array_unshift($data, current_user()['id']);
        $stmt = db()->prepare('INSERT INTO scholarships (provider_id, title, description, amount, deadline, min_grade, required_university, required_city, required_social_status, requires_veteran_child, requires_orphan, requires_social_assistance, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute($data);
        flash(t('scholarship_created'));
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
        flash(t('scholarship_or_profile_missing'), 'error');
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

    flash($status === 'approved' ? t('application_auto_approved') : t('application_auto_rejected'), $status === 'approved' ? 'success' : 'error');
    redirect('dashboard');
}

function action_complaint(): void
{
    require_role(['student']);
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($message === '') {
        flash(t('complaint_reason_required'), 'error');
        redirect('complaint&application_id=' . $applicationId);
    }

    $stmt = db()->prepare('INSERT INTO complaints (application_id, student_id, message, status) VALUES (?, ?, ?, "pending")');
    $stmt->execute([$applicationId, current_user()['id'], $message]);
    flash(t('complaint_sent'));
    redirect('dashboard');
}

function action_update_profile(): void
{
    require_role(['student']);

    $section = allowed_value($_POST['section_name'] ?? '', ['personal', 'education', 'courses', 'crafts', 'student', 'bank'], '');
    if ($section === '') {
        flash(t('invalid_section'), 'error');
        redirect('profile&edit=1');
    }

    try {
        match ($section) {
            'personal' => update_profile_personal_section(),
            'education' => update_profile_previous_education_section('schools'),
            'courses' => update_profile_previous_education_section('courses'),
            'crafts' => update_profile_previous_education_section('crafts'),
            'student' => update_profile_student_section(),
            'bank' => update_profile_bank_section(),
        };

        refresh_session_user((int) current_user()['id']);
        flash(section_success_message($section));
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

    flash(t('user_saved'));
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
                <label class="help-upload">Klikoni këtu për të ngarkuar dokumentin
                    <span>
                        <em id="helpFileName" data-empty="Asnjë dokument i zgjedhur">Asnjë dokument i zgjedhur</em>
                        <button type="button">Ngarko</button>
                    </span>
                    <input id="helpFileInput" name="document" type="file">
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
        'pending' => t('pending'),
        'approved' => t('approved'),
        'rejected' => t('rejected'),
    ][$status] ?? t($status);
}

function render_student_profile_form(array $profile, string $firstName, string $lastName): void
{
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
