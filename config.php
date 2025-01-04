<?php


// Connexion à la base des donner local
// $host = 'localhost';
// $dbname = 'embauche';
// $username = 'root';
// $password = '';

// Connexion à la base des donner en ligne
$host = 'localhost';
$dbname = 'c2374646c_embauche';
$username = 'c2374646c_ibro';
$password = 'IBROibro123+';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection a la base de donner echouer : " . $e->getMessage());
}
