<?php
$titulo = 'Redefinir senha';
$classeBody = 'pagina-auth';
require_once 'includes/funcoes.php';

$erro = '';
$linkSimulado = '';
$token = $_GET['token'] ?? '';
$etapa = $token !== '' ? 'nova' : 'email';
$email = '';

if ($etapa === 'nova') {
    $st = $pdo->prepare('SELECT * FROM redefinicoes WHERE token = ? AND usado = 0 AND criado_em >= (NOW() - INTERVAL 1 HOUR)');
    $st->execute([$token]);
    $red = $st->fetch();
    if (!$red) {
        flash('erro', 'Link de redefinição inválido ou expirado.');
        header('Location: redefinir_senha.php');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nova = $_POST['nova_senha'] ?? '';
        $conf = $_POST['confirma_senha'] ?? '';
        if (strlen($nova) < 6) $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
        elseif ($nova !== $conf) $erro = 'As senhas não conferem.';
        else {
            $pdo->prepare('UPDATE usuarios SET senha = ? WHERE email = ?')->execute([password_hash($nova, PASSWORD_DEFAULT), $red['email']]);
            $pdo->prepare('UPDATE redefinicoes SET usado = 1 WHERE id = ?')->execute([$red['id']]);
            flash('sucesso', 'Senha redefinida! Faça login com a nova senha.');
            header('Location: login.php');
            exit;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') $erro = 'Informe seu e-mail.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erro = 'Informe um e-mail válido.';
    else {
        $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        if (!$st->fetch()) {
            $erro = 'Não encontramos uma conta com este e-mail.';
        } else {
            $tk = bin2hex(random_bytes(16));
            $pdo->prepare('INSERT INTO redefinicoes (email, token) VALUES (?, ?)')->execute([$email, $tk]);
            $linkSimulado = 'redefinir_senha.php?token=' . $tk;
        }
    }
}
require_once 'includes/header.php';
?>
<section class="auth">
  <div class="auth-card">
    <div class="auth-logo"><?= logoSH() ?></div>
    <h1>Redefinir senha</h1>
    <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

    <?php if ($etapa === 'nova'): ?>
      <p class="auth-sub">Crie uma nova senha para <strong><?= e($red['email']) ?></strong></p>
      <form method="post" novalidate>
        <label>Nova senha<input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres"></label>
        <label>Confirmar nova senha<input type="password" name="confirma_senha"></label>
        <button class="btn btn-laranja btn-bloco" type="submit">Salvar nova senha</button>
      </form>
    <?php elseif ($linkSimulado): ?>
      <div class="alerta alerta-sucesso">Enviamos as instruções para <strong><?= e($email) ?></strong>.</div>
      <div class="email-simulado">
        <span class="tag">/ E-mail simulado</span>
        <p>Olá! Recebemos um pedido para redefinir sua senha na SNKRHAUS. Clique no botão abaixo (válido por 1 hora):</p>
        <a href="<?= e($linkSimulado) ?>" class="btn btn-escuro btn-bloco">Redefinir minha senha</a>
      </div>
    <?php else: ?>
      <p class="auth-sub">Informe o e-mail da sua conta para receber o link de redefinição.</p>
      <form method="post" novalidate>
        <label>E-mail<input type="email" name="email" value="<?= e($email) ?>" placeholder="voce@email.com"></label>
        <button class="btn btn-escuro btn-bloco" type="submit">Enviar link</button>
      </form>
    <?php endif; ?>
    <p class="auth-rodape"><a href="login.php">← Voltar para o login</a></p>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
