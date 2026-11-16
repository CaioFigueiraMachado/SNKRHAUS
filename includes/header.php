<?php
require_once __DIR__ . '/funcoes.php';
$titulo = $titulo ?? 'SNKRHAUS';
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($titulo) ?> | SNKRHAUS</title>
<link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= e($classeBody ?? '') ?>">
<div class="marquee-topo">
  <div class="marquee-track">
    <span>✦ SNKRHAUS / SNEAKERS 100% ORIGINAIS</span>
    <span>✦ DROPS EXCLUSIVOS TODA SEMANA</span>
    <span>✦ FRETE GRÁTIS ACIMA DE R$ 999</span>
    <span>✦ ATÉ 6X SEM JUROS</span>
    <span>✦ LISBOA · BERLIN · SÃO PAULO</span>
    <span>✦ SNKRHAUS / SNEAKERS 100% ORIGINAIS</span>
    <span>✦ DROPS EXCLUSIVOS TODA SEMANA</span>
    <span>✦ FRETE GRÁTIS ACIMA DE R$ 999</span>
    <span>✦ ATÉ 6X SEM JUROS</span>
    <span>✦ LISBOA · BERLIN · SÃO PAULO</span>
  </div>
</div>
<header class="topo">
  <div class="topo-inner">
    <a href="index.php" class="marca" aria-label="SNKRHAUS - página inicial">
      <?= logoSH() ?><span class="marca-texto">SNKRHAUS</span>
    </a>
    <button class="menu-toggle" aria-label="Abrir menu" onclick="document.body.classList.toggle('menu-aberto')">☰</button>
    <nav class="menu">
      <a href="catalogo.php?secao=lancamento">Lançamentos</a>
      <a href="catalogo.php">Tênis</a>
      <a href="index.php#marcas">Marcas</a>
      <a href="catalogo.php?secao=curation">Curation</a>
      <a href="catalogo.php?ofertas=1" class="menu-oferta">Ofertas</a>
    </nav>
    <form class="busca" action="catalogo.php" method="get">
      <input type="search" name="q" placeholder="Marca, modelo..." value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit" aria-label="Buscar">⌕</button>
    </form>
    <div class="topo-acoes">
      <?php if (logado()): ?>
        <?php if (eAdmin()): ?><a href="admin.php" class="btn-mini btn-laranja">Admin</a><?php endif; ?>
        <a href="perfil.php" class="usuario-topo">
          <?= avatarUsuario(['nome' => $_SESSION['usuario_nome'], 'foto' => $_SESSION['usuario_foto'] ?? null], 'avatar-mini') ?>
          <span class="ola">Olá, <strong><?= e(primeiroNome($_SESSION['usuario_nome'])) ?></strong></span>
        </a>
        <a href="logout.php" class="btn-mini">Sair</a>
      <?php else: ?>
        <a href="cadastro.php" class="link-topo">Criar conta</a>
        <a href="login.php" class="btn-mini btn-branco">Entrar →</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main>
<div class="container"><?php mostrarFlash(); ?></div>
