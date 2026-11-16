<?php
require_once 'includes/funcoes.php';
$_SESSION = [];
session_destroy();
session_start();
flash('sucesso', 'Você saiu da sua conta.');
header('Location: index.php');
exit;
