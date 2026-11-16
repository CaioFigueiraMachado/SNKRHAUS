<?php
$titulo = 'Área do administrador';
require_once 'includes/funcoes.php';
exigirAdmin();

// Exclusão (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    $idExc = (int)($_POST['id'] ?? 0);
    if ($idExc === (int)$_SESSION['usuario_id']) {
        flash('erro', 'Você não pode excluir a sua própria conta.');
    } else {
        $st = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        $st->execute([$idExc]);
        flash($st->rowCount() ? 'sucesso' : 'erro', $st->rowCount() ? 'Usuário excluído com sucesso.' : 'Usuário não encontrado.');
    }
    header('Location: admin.php');
    exit;
}

$busca = trim($_GET['busca'] ?? '');
if ($busca !== '') {
    $st = $pdo->prepare('SELECT u.*, (SELECT COUNT(*) FROM pedidos p WHERE p.usuario_id = u.id) AS qtd_pedidos FROM usuarios u WHERE u.nome LIKE ? OR u.email LIKE ? ORDER BY u.id');
    $st->execute(["%$busca%", "%$busca%"]);
} else {
    $st = $pdo->query('SELECT u.*, (SELECT COUNT(*) FROM pedidos p WHERE p.usuario_id = u.id) AS qtd_pedidos FROM usuarios u ORDER BY u.id');
}
$usuarios = $st->fetchAll();

$stats = [
    'usuarios' => $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
    'pedidos' => $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
    'faturamento' => $pdo->query('SELECT COALESCE(SUM(total),0) FROM pedidos')->fetchColumn(),
    'produtos' => $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn(),
];
require_once 'includes/header.php';
?>
<section class="container pagina">
  <div class="pagina-cabecalho">
    <span class="tag tag-laranja">/ Painel administrativo</span>
    <h1>Área do administrador</h1>
    <p>Gerencie os usuários cadastrados na SNKRHAUS.</p>
  </div>

  <div class="stats">
    <div class="stat"><span>Usuários</span><strong><?= (int)$stats['usuarios'] ?></strong></div>
    <div class="stat"><span>Pedidos</span><strong><?= (int)$stats['pedidos'] ?></strong></div>
    <div class="stat"><span>Faturamento (simulado)</span><strong><?= preco($stats['faturamento']) ?></strong></div>
    <div class="stat"><span>Produtos</span><strong><?= (int)$stats['produtos'] ?></strong></div>
  </div>

  <div class="painel">
    <div class="painel-topo">
      <h2>Usuários</h2>
      <form method="get" class="busca-admin">
        <input type="search" name="busca" value="<?= e($busca) ?>" placeholder="Buscar por nome ou e-mail">
        <button class="btn-mini btn-escuro-mini" type="submit">Buscar</button>
      </form>
    </div>
    <div class="tabela-wrap"><table class="tabela">
      <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Pedidos</th><th>Cadastro</th><th>Ações</th></tr></thead>
      <tbody>
      <?php if (!$usuarios): ?><tr><td colspan="7" class="vazio">Nenhum usuário encontrado.</td></tr><?php endif; ?>
      <?php foreach ($usuarios as $u): $proprio = (int)$u['id'] === (int)$_SESSION['usuario_id']; ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <?= avatarUsuario($u, 'avatar-mini') ?>
              <span><?= e($u['nome']) ?><?= $proprio ? ' <em class="voce">(você)</em>' : '' ?></span>
            </div>
          </td>
          <td><?= e($u['email']) ?></td>
          <td><span class="pill pill-<?= e($u['tipo']) ?>"><?= $u['tipo'] === 'admin' ? 'Admin' : 'Comum' ?></span></td>
          <td><?= (int)$u['qtd_pedidos'] ?></td>
          <td><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
          <td class="acoes">
            <a href="admin_editar.php?id=<?= (int)$u['id'] ?>" class="btn-mini btn-escuro-mini">Editar</a>
            <?php if (!$proprio): ?>
            <form method="post" onsubmit="return confirm('Excluir o usuário <?= e(addslashes($u['nome'])) ?>? Esta ação não pode ser desfeita.')">
              <input type="hidden" name="acao" value="excluir">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="btn-mini btn-perigo" type="submit">Excluir</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
