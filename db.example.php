<?php
// Template de connexion — copier en db.php et remplir les valeurs
// Ne JAMAIS commiter db.php (voir .gitignore)
//
// Variables d'environnement à configurer dans Vercel Dashboard :
//   DB_HOST  = db.xxxxxxxxxxxx.supabase.co
//   DB_NAME  = postgres
//   DB_USER  = postgres
//   DB_PASS  = <mot_de_passe_supabase>
//   DB_PORT  = 5432  (optionnel, défaut = 5432)

$host   = getenv('DB_HOST')   ?: 'localhost';
$dbname = getenv('DB_NAME')   ?: 'verbeek-hugo';
$user   = getenv('DB_USER')   ?: 'root';
$pass   = getenv('DB_PASS')   ?: '';
$port   = getenv('DB_PORT')   ?: '5432';

try {
    if (getenv('DB_HOST')) {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    } else {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    }
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
