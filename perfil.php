<?php
$titulo = 'Meu perfil';
require_once 'includes/funcoes.php';
exigirLogin();

$id = $_SESSION['usuario_id'];
$st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$st->execute([$id]);
$u = $st->fetch();
if (!$u) { header('Location: logout.php'); exit; }

$erros = [];
$acao = $_POST['acao'] ?? '';

// 1. Upload de Foto de Perfil
if ($acao === 'foto') {
    if (isset($_POST['remover_foto'])) {
        if (!empty($u['foto'])) {
            $antigo = __DIR__ . '/assets/img/avatars/' . $u['foto'];
            if (file_exists($antigo)) @unlink($antigo);
        }
        $pdo->prepare('UPDATE usuarios SET foto = NULL WHERE id = ?')->execute([$id]);
        $_SESSION['usuario_foto'] = null;
        flash('sucesso', 'Foto de perfil removida com sucesso.');
        header('Location: perfil.php');
        exit;
    }

    if (!empty($_FILES['foto']['name'])) {
        $arquivo = $_FILES['foto'];
        $extPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Erro ao fazer upload da imagem.';
        } elseif (!in_array($ext, $extPermitidas)) {
            $erros[] = 'Formato inválido. Envie uma foto em JPG, PNG ou WEBP.';
        } elseif ($arquivo['size'] > 5 * 1024 * 1024) {
            $erros[] = 'A foto deve ter no máximo 5MB.';
        } else {
            $pasta = __DIR__ . '/assets/img/avatars/';
            if (!is_dir($pasta)) mkdir($pasta, 0777, true);

            // Remove foto antiga se existir
            if (!empty($u['foto'])) {
                $antigo = $pasta . $u['foto'];
                if (file_exists($antigo)) @unlink($antigo);
            }

            $novoNome = 'avatar_' . $id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($arquivo['tmp_name'], $pasta . $novoNome)) {
                $pdo->prepare('UPDATE usuarios SET foto = ? WHERE id = ?')->execute([$novoNome, $id]);
                $_SESSION['usuario_foto'] = $novoNome;
                flash('sucesso', 'Foto de perfil atualizada com sucesso!');
                header('Location: perfil.php');
                exit;
            } else {
                $erros[] = 'Não foi possível salvar a imagem no servidor.';
            }
        }
    } else {
        $erros[] = 'Selecione uma imagem para enviar.';
    }
} elseif ($acao === 'dados') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    if (mb_strlen($nome) < 3) $erros[] = 'Informe um nome válido (mínimo 3 letras).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'Informe um e-mail válido.';
    if ($telefone !== '' && !preg_match('/^[0-9 ()+\-]{8,20}$/', $telefone)) $erros[] = 'Telefone inválido.';
    if (!$erros) {
        $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $st->execute([$email, $id]);
        if ($st->fetch()) $erros[] = 'Este e-mail já pertence a outra conta.';
    }
    if (!$erros) {
        $st = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?');
        $st->execute([$nome, $email, $telefone ?: null, $id]);
        $_SESSION['usuario_nome'] = $nome;
        flash('sucesso', 'Dados atualizados com sucesso.');
        header('Location: perfil.php');
        exit;
    }
} elseif ($acao === 'senha') {
    $atual = $_POST['senha_atual'] ?? '';
    $nova = $_POST['nova_senha'] ?? '';
    $conf = $_POST['confirma_senha'] ?? '';
    if (!password_verify($atual, $u['senha'])) $erros[] = 'A senha atual está incorreta.';
    elseif (strlen($nova) < 6) $erros[] = 'A nova senha deve ter pelo menos 6 caracteres.';
    elseif ($nova !== $conf) $erros[] = 'A confirmação não confere com a nova senha.';
    if (!$erros) {
        $st = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        $st->execute([password_hash($nova, PASSWORD_DEFAULT), $id]);
        flash('sucesso', 'Senha alterada com sucesso.');
        header('Location: perfil.php');
        exit;
    }
}

$st = $pdo->prepare('SELECT pe.*, pr.nome AS produto, pr.marca, pr.imagem, pr.cor_principal, pr.cor_detalhe, pr.cor_sola
                     FROM pedidos pe JOIN produtos pr ON pr.id = pe.produto_id
                     WHERE pe.usuario_id = ? ORDER BY pe.id DESC');
$st->execute([$id]);
$pedidos = $st->fetchAll();

require_once 'includes/header.php';
?>
<section class="container pagina">
  
  <!-- Banner do Perfil com Avatar e Motion -->
  <div class="perfil-hero revela">
    <div class="perfil-hero-avatar">
      <div class="avatar-moldura" id="avatarPreviewContainer">
        <?= avatarUsuario($u, 'avatar-grande') ?>
      </div>
      <form method="post" enctype="multipart/form-data" class="form-avatar" id="formAvatar">
        <input type="hidden" name="acao" value="foto">
        <label for="inputFoto" class="btn-trocar-foto" title="Escolher nova foto">
          Trocar Foto
        </label>
        <input type="file" name="foto" id="inputFoto" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewEEnviar(this)">
        <?php if (!empty($u['foto'])): ?>
          <button type="submit" name="remover_foto" value="1" class="btn-remover-foto" title="Remover foto atual" onclick="return confirm('Deseja remover sua foto de perfil?')">×</button>
        <?php endif; ?>
      </form>
    </div>
    <div class="perfil-hero-info">
      <span class="tag tag-laranja">// ID: <?= str_pad($u['id'], 4, '0', STR_PAD_LEFT) ?> · <?= $u['tipo'] === 'admin' ? 'ADMINISTRADOR' : 'COMMUNITY MEMBER' ?></span>
      <h1><?= e($u['nome']) ?></h1>
      <p class="perfil-hero-meta">
        <span><?= e($u['email']) ?></span>
        <?php if (!empty($u['telefone'])): ?><span><?= e($u['telefone']) ?></span><?php endif; ?>
        <span>Membro desde <?= date('d/m/Y', strtotime($u['criado_em'])) ?></span>
      </p>
    </div>
    <div class="perfil-hero-badge">
      <span class="badge-status">● CONTA ATIVA</span>
      <small><?= count($pedidos) ?> pedido(s) realizado(s)</small>
    </div>
  </div>

  <?php foreach ($erros as $er): ?><div class="alerta alerta-erro"><?= e($er) ?></div><?php endforeach; ?>

  <div class="perfil-grid">
    <!-- Formulário Dados Pessoais -->
    <div class="painel revela">
      <div class="painel-topo">
        <h2>Dados pessoais</h2>
        <span class="tag">// IDENTIFICAÇÃO</span>
      </div>
      <form method="post" novalidate>
        <input type="hidden" name="acao" value="dados">
        <label>Nome completo
          <input type="text" name="nome" value="<?= e($_POST['nome'] ?? $u['nome']) ?>" required>
        </label>
        <label>E-mail de acesso
          <input type="email" name="email" value="<?= e($_POST['email'] ?? $u['email']) ?>" required>
        </label>
        <label>Telefone / WhatsApp (opcional)
          <input type="text" name="telefone" value="<?= e($_POST['telefone'] ?? $u['telefone']) ?>" placeholder="(11) 99999-9999">
        </label>
        <button class="btn btn-laranja btn-bloco" type="submit">Salvar alterações</button>
      </form>
    </div>

    <!-- Formulário Alterar Senha -->
    <div class="painel revela">
      <div class="painel-topo">
        <h2>Segurança & Senha</h2>
        <span class="tag">// CREDENCIAIS</span>
      </div>
      <form method="post" novalidate>
        <input type="hidden" name="acao" value="senha">
        <label>Senha atual
          <input type="password" name="senha_atual" placeholder="Digite sua senha atual" required>
        </label>
        <label>Nova senha
          <input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres" required>
        </label>
        <label>Confirmar nova senha
          <input type="password" name="confirma_senha" placeholder="Repita a nova senha" required>
        </label>
        <button class="btn btn-escuro btn-bloco" type="submit">Atualizar senha</button>
      </form>
    </div>
  </div>

  <!-- Histórico de Pedidos -->
  <div class="painel revela">
    <div class="painel-topo">
      <h2>Histórico de pedidos</h2>
      <span class="tag">// PURCHASES & DROPS</span>
    </div>
    <?php if (!$pedidos): ?>
      <p class="vazio">Você ainda não realizou nenhum pedido na SNKRHAUS. <a href="catalogo.php">Ver catálogo de sneakers →</a></p>
    <?php else: ?>
      <div class="tabela-wrap"><table class="tabela">
        <thead>
          <tr>
            <th>Pedido</th>
            <th>Tênis</th>
            <th>Tam.</th>
            <th>Qtd.</th>
            <th>Pagamento</th>
            <th>Total</th>
            <th>Data</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pedidos as $pd): ?>
          <tr>
            <td><strong>#<?= str_pad($pd['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
            <td class="td-produto">
              <?= fotoProduto($pd, 'mini') ?>
              <div>
                <span class="card-marca"><?= e($pd['marca']) ?></span>
                <strong><?= e($pd['produto']) ?></strong>
              </div>
            </td>
            <td><span class="pill-tamanho"><?= (int)$pd['tamanho'] ?></span></td>
            <td><?= (int)$pd['quantidade'] ?>x</td>
            <td><span class="pill-pagamento"><?= e(strtoupper($pd['pagamento'])) ?></span></td>
            <td><strong class="preco-pedido"><?= preco($pd['total']) ?></strong></td>
            <td><?= date('d/m/Y H:i', strtotime($pd['criado_em'])) ?></td>
            <td><span class="pill pill-admin">Concluído</span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php endif; ?>
  </div>
</section>

<script>
// Pré-visualização instantânea e envio automático ao selecionar foto
function previewEEnviar(input) {
  if (input.files && input.files[0]) {
    var leitor = new FileReader();
    leitor.onload = function(e) {
      var container = document.getElementById('avatarPreviewContainer');
      container.innerHTML = '<img src="' + e.target.result + '" class="avatar-foto avatar-grande preview-ativo" alt="Pré-visualização">';
    };
    leitor.readAsDataURL(input.files[0]);
    // Submete o formulário automaticamente
    setTimeout(function() {
      document.getElementById('formAvatar').submit();
    }, 400);
  }
}
</script>

<?php require_once 'includes/footer.php'; ?>
