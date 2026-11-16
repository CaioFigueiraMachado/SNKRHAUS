<?php
$titulo = 'Criar conta';
$classeBody = 'pagina-auth';
require_once 'includes/funcoes.php';
if (logado()) { header('Location: index.php'); exit; }

$erros = [];
$nome = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma'] ?? '';

    if ($nome === '' && $email === '' && $senha === '') {
        $erros[] = 'Preencha todos os campos.';
    } else {
        if (mb_strlen($nome) < 3) $erros[] = 'Informe seu nome (mínimo 3 letras).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um e-mail válido.';
        if (strlen($senha) < 6) $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
        if ($senha !== $confirma) $erros[] = 'As senhas não conferem.';
    }
    if (!$erros) {
        $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) $erros[] = 'Este e-mail já está cadastrado.';
    }
    if (!$erros) {
        $st = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, "comum")');
        $st->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
        flash('sucesso', 'Conta criada com sucesso! Faça login para continuar.');
        header('Location: login.php');
        exit;
    }
}
require_once 'includes/header.php';
?>
<section class="auth">
  <div class="auth-card">
    <div class="auth-logo"><?= logoSH() ?></div>
    <h1>Criar conta</h1>
    <p class="auth-sub">Entre para a SNKRHAUS / Community</p>
    <?php foreach ($erros as $er): ?><div class="alerta alerta-erro"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" novalidate>
      <label>Nome completo<input type="text" name="nome" value="<?= e($nome) ?>" placeholder="Seu nome"></label>
      <label>E-mail<input type="email" name="email" value="<?= e($email) ?>" placeholder="voce@email.com"></label>
      <label>Senha<input type="password" name="senha" placeholder="Mínimo 6 caracteres"></label>
      <label>Confirmar senha<input type="password" name="confirma" placeholder="Repita a senha"></label>
      <button class="btn btn-laranja btn-bloco" type="submit">Criar conta</button>
    </form>
    <p class="auth-rodape">Já tem conta? <a href="login.php">Entrar</a></p>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
