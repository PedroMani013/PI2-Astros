<?php

// Configurações do banco de dados
$host = 'localhost';   // Servidor do banco
$db = 'astros';      // Nome do banco
$user = 'root';        // Usuário
$pass = '';            // Senha (Assumindo que é vazia no XAMPP)
$port = '3306';
$charset = 'utf8mb4';  // Conjunto de caracteres

// String de conexão (DSN)
$dsn = "mysql:host=$host;dbname=$db;port=3306;charset=$charset";

// Opções de configuração do PDO
$options = [
    PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=> PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES=> false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

?>