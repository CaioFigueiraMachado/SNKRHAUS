<?php
// Conexão com o banco (PDO). Ajuste usuário/senha se necessário.
$DB_HOST = 'localhost';
$DB_NOME = 'snkrhaus';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NOME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Migração automática silenciosa para garantir coluna 'foto' na tabela usuarios
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER telefone");
    } catch (Exception $ignorar) {
        // Coluna já existe ou permissão restrita, segue normalmente
    }

} catch (PDOException $e) {
    die('Erro ao conectar no banco. Importe o arquivo database.sql no phpMyAdmin. Detalhe: ' . htmlspecialchars($e->getMessage()));
}
