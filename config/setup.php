<?php
require_once __DIR__ . '/database.php';

try {
    $pdo = Database::getInstance();

    $sql = file_get_contents(__DIR__ . '/setup.sql');

    $pdo->exec($sql);
    
    echo "✅ Tables créées avec succès !";
    echo "<br><a href='/'>Retour à l'accueil</a>";

} catch (PDOException $e) {
    die("❌ Erreur Setup : " . $e->getMessage());
}
?>