<?php
$titulo = 'Editar usuário';
require_once 'includes/funcoes.php';
exigirAdmin();

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$st->execute([$id]);
$u = $st->fetch();
if (!$u) { flash('erro', 'Usuário não encontrado.'); header('Location: admin.php'); exit; }
$proprio = $id === (int)$_SESSION['usuario_id'];

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tipo = $proprio ? 'admin' : ($_POST['tipo'] ?? 'comum');
    $novaSenha = $_POST['nova_senha'] ?? '';
    if (mb_strlen($nome) < 3) $erros[] = 'Informe um nome válido (mínimo 3 letras).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um e-mail válido.';
    if (!in_array($tipo, ['comum', 'admin'], true)) $erros[] = 'Tipo inválido.';
    if ($novaSenha !== '' && strlen($novaSenha) < 6) $erros[] = 'A nova senha deve ter pelo menos 6 caracteres.';
    if (!$erros) {
        $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $st->execute([$email, $id]);
        if ($st->fetch()) $erros[] = 'Este e-mail já pertence a outra conta.';
    }
    if (!$erros) {
        $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, tipo = ? WHERE id = ?')->execute([$nome, $email, $tipo, $id]);
        if ($novaSenha !== '') {
            $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?')->execute([password_hash($novaSenha, PASSWORD_DEFAULT), $id]);
        }
        if ($proprio) $_SESSION['usuario_nome'] = $nome;
        flash('sucesso', 'Usuário atualizado com sucesso.');
        header('Location: admin.php');
        exit;
    }
    $u = array_merge($u, ['nome' => $nome, 'email' => $email, 'tipo' => $tipo]);
}
require_once 'includes/header.php';
?>
<section class="container pagina pagina-estreita">
  <div class="pagina-cabecalho">
    <span class="tag tag-laranja">/ Painel administrativo</span>
    <h1>Editar usuário #<?= (int)$id ?></h1>
  </div>
  <?php foreach ($erros as $er): ?><div class="alerta alerta-erro"><?= e($er) ?></div><?php endforeach; ?>
  <div class="painel">
    <form method="post" novalidate>
      <label>Nome<input type="text" name="nome" value="<?= e($u['nome']) ?>"></label>
      <label>E-mail<input type="email" name="email" value="<?= e($u['email']) ?>"></label>
      <label>Tipo de conta
        <select name="tipo" <?= $proprio ? 'disabled' : '' ?>>
          <option value="comum" <?= $u['tipo'] === 'comum' ? 'selected' : '' ?>>Comum</option>
          <option value="admin" <?= $u['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select>
      </label>
      <?php if ($proprio): ?><p class="mini-aviso">Você não pode remover o seu próprio acesso de administrador.</p><?php endif; ?>
      <label>Nova senha (deixe em branco para manter)<input type="password" name="nova_senha"></label>
      <div class="cta-acoes">
        <button class="btn btn-laranja" type="submit">Salvar</button>
        <a href="admin.php" class="btn btn-contorno-escuro">Cancelar</a>
      </div>
    </form>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
