<?php
require_once 'includes/funcoes.php';
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { flash('erro', 'Produto não encontrado.'); header('Location: catalogo.php'); exit; }
$titulo = $p['marca'] . ' ' . $p['nome'];

$st = $pdo->prepare('SELECT * FROM produtos WHERE (marca = ? OR categoria = ?) AND id <> ? LIMIT 8');
$st->execute([$p['marca'], $p['categoria'], $id]);
$relacionados = $st->fetchAll();
require_once 'includes/header.php';
?>
<section class="container pagina">
  <nav class="trilha"><a href="index.php">Início</a> / <a href="catalogo.php?categoria=<?= urlencode($p['categoria']) ?>"><?= e($p['categoria']) ?></a> / <span><?= e($p['nome']) ?></span></nav>
  <div class="produto-grid">
    <div class="produto-foto">
      <span class="selo-original">100% Original</span>
      <?= fotoProduto($p, 'flutua') ?>
    </div>
    <div class="produto-info">
      <span class="card-marca"><?= e($p['marca']) ?> · <?= e($p['categoria']) ?></span>
      <h1><?= e($p['nome']) ?></h1>
      <div class="produto-preco">
        <?php if ($p['preco_antigo']): ?><s><?= preco($p['preco_antigo']) ?></s><?php endif; ?>
        <strong><?= preco($p['preco']) ?></strong>
        <small><?= parcelas($p['preco']) ?></small>
      </div>
      <p class="produto-desc"><?= e($p['descricao']) ?></p>

      <form action="checkout.php" method="get" class="form-compra" novalidate>
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <span class="rotulo">Tamanho (BR)</span>
        <div class="tamanhos">
          <?php for ($t = 36; $t <= 44; $t++): ?>
            <label class="tam"><input type="radio" name="tamanho" value="<?= $t ?>"><span><?= $t ?></span></label>
          <?php endfor; ?>
        </div>
        <span class="rotulo">Quantidade</span>
        <div class="qtd">
          <button type="button" data-menos>−</button>
          <input type="number" name="quantidade" value="1" min="1" max="<?= (int)$p['estoque'] ?>">
          <button type="button" data-mais>+</button>
          <span class="estoque"><?= $p['estoque'] > 0 ? (int)$p['estoque'] . ' em estoque' : 'Esgotado' ?></span>
        </div>
        <?php if ($p['estoque'] > 0): ?>
          <button type="submit" class="btn btn-laranja btn-bloco">Comprar agora →</button>
          <?php if (!logado()): ?><p class="aviso-login">Você precisará <a href="login.php">entrar</a> para finalizar a compra.</p><?php endif; ?>
        <?php else: ?>
          <button type="button" class="btn btn-bloco" disabled>Esgotado</button>
        <?php endif; ?>
      </form>
      <ul class="beneficios">
        <li>Frete grátis acima de R$ 999</li>
        <li>Troca grátis em até 30 dias</li>
        <li>Autenticidade garantida, direto da marca</li>
      </ul>
    </div>
  </div>

  <?php if ($relacionados): ?>
  <div class="secao">
    <div class="secao-titulo"><h2>Você também pode curtir</h2></div>
    <div class="trilho" data-trilho><?php foreach ($relacionados as $r) echo cardProduto($r); ?></div>
  </div>
  <?php endif; ?>
</section>
<?php require_once 'includes/footer.php'; ?>
