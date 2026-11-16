<?php
$titulo = 'Pedido confirmado';
require_once 'includes/funcoes.php';
exigirLogin();
$st = $pdo->prepare('SELECT pe.*, pr.nome AS produto, pr.marca, pr.imagem, pr.cor_principal, pr.cor_detalhe, pr.cor_sola
                     FROM pedidos pe JOIN produtos pr ON pr.id = pe.produto_id WHERE pe.id = ? AND pe.usuario_id = ?');
$st->execute([(int)($_GET['id'] ?? 0), $_SESSION['usuario_id']]);
$pd = $st->fetch();
if (!$pd) { header('Location: perfil.php'); exit; }
require_once 'includes/header.php';
?>
<section class="container pagina">
  <div class="confirmado">
    <div class="caixa-kraft">
      <div class="fita"></div>
      <span class="caixa-logo">SNKR<br>HAUS</span>
      <?= logoSH('caixa-sh') ?>
    </div>
    <span class="tag tag-laranja">/ Pedido #<?= str_pad($pd['id'], 5, '0', STR_PAD_LEFT) ?></span>
    <h1>Pedido confirmado! 🎉</h1>
    <p>Obrigado, <?= e(primeiroNome($_SESSION['usuario_nome'])) ?>. Seu par já está sendo preparado (simulação).</p>
    <div class="painel confirmado-info">
      <div class="checkout-item">
        <div class="checkout-img"><?= fotoProduto($pd) ?></div>
        <div>
          <span class="card-marca"><?= e($pd['marca']) ?></span>
          <h3><?= e($pd['produto']) ?></h3>
          <p>Tamanho <?= (int)$pd['tamanho'] ?> · <?= (int)$pd['quantidade'] ?> par(es) · <?= e(strtoupper($pd['pagamento'])) ?></p>
          <p class="mini-aviso">Entrega: <?= e($pd['endereco']) ?></p>
          <strong class="total-grande"><?= preco($pd['total']) ?></strong>
        </div>
      </div>
    </div>
    <div class="cta-acoes">
      <a href="perfil.php" class="btn btn-escuro">Ver meus pedidos</a>
      <a href="catalogo.php" class="btn btn-contorno-escuro">Continuar comprando</a>
    </div>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
