<?php
// On inclut database.php pour ne pas répéter les identifiants
require_once __DIR__ . '/database.php';

try {
    // On récupère l'instance PDO déjà configurée dans Database.php
    // Cela garantit qu'on utilise les mêmes logins partout
    $pdo = Database::getInstance();

    // Lecture du fichier SQL
    $sql = file_get_contents(__DIR__ . '/setup.sql');

    // Exécution
    $pdo->exec($sql);
    
    echo "✅ Tables créées avec succès !";
    echo "<br><a href='/'>Retour à l'accueil</a>";

} catch (PDOException $e) {
    die("❌ Erreur Setup : " . $e->getMessage());
}
?>