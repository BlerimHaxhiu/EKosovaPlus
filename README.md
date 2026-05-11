# EKosova+

Prototip akademik per lenden **Optimalizim**: modul i integrueshem ne stilin e EKosova per automatizimin e aplikimit per burse studentore.

## Teknologjia

- PHP me PDO dhe prepared statements
- MySQL
- HTML, CSS, pak JavaScript
- XAMPP

## Instalimi

1. Krijo databazen duke importuar `ekosova.sql` ne phpMyAdmin.
2. Kontrollo kredencialet ne `config/config.php`.
3. Hape aplikacionin:
   `http://localhost/Ekosovaplus/public/index.php`

## Llogari testuese

- Admin: `admin` / `admin`
- Golden Eagle: `Geagle` / `123456`
- UKZ: `UKZ` / `123`
- Komuna e Kamenices: `KK06` / `1234`
- Student demo: `student1` / `123456`

## Rrjedha kryesore

Studenti kyqet, shkon te `Sherbime -> Arsimi -> Bursat`, klikon `Apliko`, dhe sistemi simulon verifikimin automatik nga universiteti, komuna dhe regjistrat social.
