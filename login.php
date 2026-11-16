<?php
$titulo = 'Entrar';
$classeBody = 'pagina-auth';
require_once 'includes/funcoes.php';
if (logado()) { header('Location: ' . (eAdmin() ? 'admin.php' : 'index.php')); exit; }

$erro = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $st = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($senha, $u['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $u['id'];
            $_SESSION['usuario_nome'] = $u['nome'];
            $_SESSION['usuario_tipo'] = $u['tipo'];
            $_SESSION['usuario_foto'] = $u['foto'] ?? null;
            if ($u['tipo'] === 'admin') {
                flash('sucesso', 'Bem-vindo ao painel do administrador.');
                header('Location: admin.php');
            } else {
                $destino = $_SESSION['voltar_para'] ?? 'index.php';
                unset($_SESSION['voltar_para']);
                header('Location: ' . $destino);
            }
            exit;
        }
        $erro = 'E-mail ou senha incorretos.';
    }
}
require_once 'includes/header.php';
?>
<section class="auth">
  <div class="auth-card">
    <div class="auth-logo"><?= logoSH() ?></div>
    <h1>Entrar</h1>
    <p class="auth-sub">Acesse sua conta SNKRHAUS</p>
    <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <label>E-mail<input type="email" name="email" value="<?= e($email) ?>" placeholder="voce@email.com"></label>
      <label>Senha<input type="password" name="senha" placeholder="Sua senha"></label>
      <a href="redefinir_senha.php" class="link-esqueci">Esqueci minha senha</a>
      <button class="btn btn-escuro btn-bloco" type="submit">Entrar →</button>
    </form>
    <p class="auth-rodape">Ainda não tem conta? <a href="cadastro.php">Criar conta</a></p>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
