<?php
$titulo = 'Sneakers originais';
require_once 'includes/header.php';

function porSecao($pdo, $secao, $limite = 10) {
    $st = $pdo->prepare('SELECT * FROM produtos WHERE secao = ? ORDER BY id LIMIT ' . (int)$limite);
    $st->execute([$secao]);
    return $st->fetchAll();
}
$lancamentos = porSecao($pdo, 'lancamento');
$maisVendidos = porSecao($pdo, 'mais_vendido');
$curation = porSecao($pdo, 'curation');
$recentes = $pdo->query('SELECT * FROM produtos ORDER BY criado_em DESC, id DESC LIMIT 10')->fetchAll();
$marcas = $pdo->query('SELECT DISTINCT marca FROM produtos ORDER BY marca')->fetchAll(PDO::FETCH_COLUMN);
$categorias = $pdo->query('SELECT DISTINCT categoria FROM produtos ORDER BY categoria')->fetchAll(PDO::FETCH_COLUMN);
$catProduto = [];
foreach ($categorias as $c) {
    $st = $pdo->prepare('SELECT * FROM produtos WHERE categoria = ? LIMIT 1');
    $st->execute([$c]);
    $catProduto[$c] = $st->fetch();
}
?>

<?php if (logado()): ?>
<section class="container boas-vindas">
  <div>
    <span class="tag">/ Área do cliente</span>
    <h1>Olá, <?= e(primeiroNome($_SESSION['usuario_nome'])) ?>!</h1>
    <p>Bem-vindo de volta à SNKRHAUS. Confira os drops que separamos para você.</p>
  </div>
  <div class="boas-vindas-acoes">
    <a href="catalogo.php" class="btn btn-laranja">Comprar agora →</a>
    <a href="perfil.php" class="btn btn-contorno">Meu perfil e pedidos</a>
    <?php if (eAdmin()): ?><a href="admin.php" class="btn btn-escuro">Painel admin</a><?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="container">
  <div class="hero" data-carrossel>
    <div class="hero-slide ativo" style="--bg:#1A1A1A">
      <div class="hero-texto">
        <span class="tag tag-laranja">/ Drop da semana</span>
        <h2>SNKR<br>HAUS</h2>
        <p>Os modelos mais novos das maiores marcas. 100% originais, direto da fonte.</p>
        <a href="<?= logado() ? 'catalogo.php' : 'cadastro.php' ?>" class="btn btn-branco"><?= logado() ? 'Ver lançamentos' : 'Criar conta' ?> →</a>
        <small>*EM ATÉ 6X SEM JUROS</small>
      </div>
      <div class="hero-img"><?= fotoProduto($lancamentos[1] ?? $lancamentos[0], 'flutua') ?></div>
    </div>
    <div class="hero-slide" style="--bg:#F4511E">
      <div class="hero-texto">
        <span class="tag tag-preta">/ Core Tech · S. Haus</span>
        <h2>Precisão<br>técnica máxima</h2>
        <p>Edição exclusiva Core Tech Runner na cor industrial da casa.</p>
        <a href="catalogo.php?secao=curation" class="btn btn-escuro">Ver coleção →</a>
      </div>
      <div class="hero-img"><?= fotoProduto($curation[0] ?? $lancamentos[0], 'flutua') ?></div>
    </div>
    <div class="hero-slide concreto" style="--bg:#BDBDBD">
      <div class="hero-texto escuro">
        <span class="tag">/ Ofertas</span>
        <h2>Black<br>HAUS</h2>
        <p>Descontos em modelos selecionados por tempo limitado.</p>
        <a href="catalogo.php?ofertas=1" class="btn btn-escuro">Aproveitar →</a>
      </div>
      <div class="hero-img"><?= fotoProduto($recentes[1] ?? $lancamentos[0], 'flutua') ?></div>
    </div>
    <button class="hero-seta esq" data-ant aria-label="Anterior">‹</button>
    <button class="hero-seta dir" data-prox aria-label="Próximo">›</button>
    <div class="hero-pontos"></div>
  </div>
</section>

<section class="container">
  <div class="categorias">
    <?php foreach ($catProduto as $nome => $p): if (!$p) continue; ?>
      <a href="catalogo.php?categoria=<?= urlencode($nome) ?>" class="cat-card">
        <?= fotoProduto($p) ?><span><?= e($nome) ?></span>
      </a>
    <?php endforeach; ?>
    <a href="catalogo.php?secao=curation" class="cat-card cat-destaque"><?= logoSH() ?><span>Collabs & Curation</span></a>
  </div>
</section>

<section class="container" id="marcas">
  <div class="marcas-circulos">
    <?php foreach ($marcas as $m): ?>
      <a href="catalogo.php?marca=<?= urlencode($m) ?>" class="marca-circulo">
        <span class="circulo"><?= e(strtoupper(substr($m, 0, $m === 'New Balance' ? 2 : 3))) ?></span><small><?= e($m) ?></small>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="container secao">
  <div class="secao-titulo"><h2>Lançamentos</h2><a href="catalogo.php?secao=lancamento">Ver todos →</a></div>
  <div class="trilho" data-trilho>
    <?php foreach ($lancamentos as $p) echo cardProduto($p); ?>
  </div>
</section>

<section class="container secao">
  <div class="secao-titulo"><h2>Mais vendidos da semana</h2><a href="catalogo.php?secao=mais_vendido">Ver todos →</a></div>
  <div class="trilho" data-trilho>
    <?php foreach ($maisVendidos as $p) echo cardProduto($p); ?>
  </div>
</section>

<section class="container secao">
  <div class="colecao">
    <div class="colecao-banner">
      <span class="tag tag-laranja">/ SNKRHAUS Curation</span>
      <h2>Curadoria da casa</h2>
      <p>Pares escolhidos a dedo pelo nosso time em Lisboa e Berlin.</p>
      <a href="catalogo.php?secao=curation" class="ver-colecao">VER COLEÇÃO →</a>
      <div class="colecao-tenis"><?= fotoProduto($curation[1] ?? $lancamentos[0]) ?></div>
    </div>
    <div class="trilho colecao-trilho" data-trilho>
      <?php foreach ($curation as $p) echo cardProduto($p); ?>
    </div>
  </div>
</section>

<section class="container secao secao-limpa">
  <div class="secao-titulo"><h2>Adicionados recentemente</h2></div>
  <div class="trilho trilho-limpo" data-trilho data-progresso>
    <?php foreach ($recentes as $p) echo cardProduto($p); ?>
  </div>
  <div class="progresso"><span></span></div>
</section>

<section class="container secao">
  <div class="secao-titulo"><h2>Nossas marcas</h2></div>
  <div class="grade-marcas">
    <?php foreach ($marcas as $m): ?>
      <a href="catalogo.php?marca=<?= urlencode($m) ?>" class="marca-box"><?= e($m) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!logado()): ?>
<section class="container secao">
  <div class="cta-conta">
    <div>
      <h2>Faça parte da SNKRHAUS / Community</h2>
      <p>Crie sua conta para comprar, acompanhar seus pedidos e receber os drops antes de todo mundo.</p>
    </div>
    <div class="cta-acoes">
      <a href="cadastro.php" class="btn btn-laranja">Criar conta</a>
      <a href="login.php" class="btn btn-branco">Entrar</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
