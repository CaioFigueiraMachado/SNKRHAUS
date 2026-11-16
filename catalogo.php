<?php
$titulo = 'Tênis';
require_once 'includes/funcoes.php';

$where = [];
$params = [];
$tituloLista = 'Todos os tênis';
if (!empty($_GET['q'])) {
    $where[] = '(nome LIKE ? OR marca LIKE ? OR categoria LIKE ?)';
    $t = '%' . trim($_GET['q']) . '%';
    array_push($params, $t, $t, $t);
    $tituloLista = 'Busca: "' . trim($_GET['q']) . '"';
}
if (!empty($_GET['marca'])) { $where[] = 'marca = ?'; $params[] = $_GET['marca']; $tituloLista = $_GET['marca']; }
if (!empty($_GET['categoria'])) { $where[] = 'categoria = ?'; $params[] = $_GET['categoria']; $tituloLista = $_GET['categoria']; }
$nomesSecao = ['lancamento' => 'Lançamentos', 'mais_vendido' => 'Mais vendidos', 'curation' => 'SNKRHAUS / Curation', 'recente' => 'Adicionados recentemente'];
if (!empty($_GET['secao']) && isset($nomesSecao[$_GET['secao']])) { $where[] = 'secao = ?'; $params[] = $_GET['secao']; $tituloLista = $nomesSecao[$_GET['secao']]; }
if (!empty($_GET['ofertas'])) { $where[] = 'preco_antigo IS NOT NULL AND preco_antigo > preco'; $tituloLista = 'Ofertas'; }

$ordens = ['menor' => 'preco ASC', 'maior' => 'preco DESC', 'nome' => 'nome ASC'];
$ordem = $ordens[$_GET['ordem'] ?? ''] ?? 'id ASC';

$sql = 'SELECT * FROM produtos' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY ' . $ordem;
$st = $pdo->prepare($sql);
$st->execute($params);
$produtos = $st->fetchAll();
$marcas = $pdo->query('SELECT DISTINCT marca FROM produtos ORDER BY marca')->fetchAll(PDO::FETCH_COLUMN);
$cats = $pdo->query('SELECT DISTINCT categoria FROM produtos ORDER BY categoria')->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>
<section class="container pagina">
  <div class="pagina-cabecalho">
    <span class="tag">/ Tênis</span>
    <h1><?= e($tituloLista) ?></h1>
    <p><?= count($produtos) ?> produto(s) encontrado(s)</p>
  </div>
  <form class="filtros" method="get">
    <?php if (!empty($_GET['q'])): ?><input type="hidden" name="q" value="<?= e($_GET['q']) ?>"><?php endif; ?>
    <select name="marca" onchange="this.form.submit()">
      <option value="">Todas as marcas</option>
      <?php foreach ($marcas as $m): ?><option <?= ($_GET['marca'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?>
    </select>
    <select name="categoria" onchange="this.form.submit()">
      <option value="">Todas as categorias</option>
      <?php foreach ($cats as $c): ?><option <?= ($_GET['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
    </select>
    <select name="ordem" onchange="this.form.submit()">
      <option value="">Ordenar</option>
      <option value="menor" <?= ($_GET['ordem'] ?? '') === 'menor' ? 'selected' : '' ?>>Menor preço</option>
      <option value="maior" <?= ($_GET['ordem'] ?? '') === 'maior' ? 'selected' : '' ?>>Maior preço</option>
      <option value="nome" <?= ($_GET['ordem'] ?? '') === 'nome' ? 'selected' : '' ?>>Nome A-Z</option>
    </select>
    <a href="catalogo.php" class="btn-mini btn-contorno-escuro">Limpar filtros</a>
  </form>
  <?php if (!$produtos): ?>
    <p class="vazio">Nenhum tênis encontrado. <a href="catalogo.php">Ver todos →</a></p>
  <?php else: ?>
    <div class="grade-produtos"><?php foreach ($produtos as $p) echo cardProduto($p); ?></div>
  <?php endif; ?>
</section>
<?php require_once 'includes/footer.php'; ?>
