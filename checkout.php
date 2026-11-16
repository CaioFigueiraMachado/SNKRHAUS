<?php
$titulo = 'Finalizar compra';
require_once 'includes/funcoes.php';
exigirLogin();

$id = (int)($_REQUEST['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) { flash('erro', 'Escolha um tênis antes de finalizar a compra.'); header('Location: catalogo.php'); exit; }

$tamanho = (int)($_REQUEST['tamanho'] ?? 0);
$quantidade = (int)($_REQUEST['quantidade'] ?? 1);
$pagamento = $_POST['pagamento'] ?? '';
$end = [
    'cep' => trim($_POST['cep'] ?? ''), 'rua' => trim($_POST['rua'] ?? ''),
    'numero' => trim($_POST['numero'] ?? ''), 'cidade' => trim($_POST['cidade'] ?? ''),
];
$erros = [];

function calcFrete($sub) { return $sub >= 999 ? 0 : 29.90; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tamanho < 36 || $tamanho > 44) $erros[] = 'Selecione um tamanho entre 36 e 44.';
    if ($quantidade < 1) $erros[] = 'A quantidade deve ser no mínimo 1.';
    elseif ($quantidade > $p['estoque']) $erros[] = 'Quantidade indisponível. Temos apenas ' . $p['estoque'] . ' unidade(s) em estoque.';
    if (!preg_match('/^\d{5}-?\d{3}$/', $end['cep'])) $erros[] = 'Informe um CEP válido (00000-000).';
    if ($end['rua'] === '' || $end['numero'] === '' || $end['cidade'] === '') $erros[] = 'Preencha o endereço completo.';
    if (!in_array($pagamento, ['pix', 'cartao', 'boleto'], true)) $erros[] = 'Escolha a forma de pagamento.';
    if ($pagamento === 'cartao') {
        $num = preg_replace('/\D/', '', $_POST['cartao_numero'] ?? '');
        if (strlen($num) < 13 || strlen($num) > 19) $erros[] = 'Número do cartão inválido (simulação).';
        if (trim($_POST['cartao_nome'] ?? '') === '') $erros[] = 'Informe o nome impresso no cartão.';
    }

    if (!$erros) {
        try {
            // Conferir se o usuário existe no banco
            $stU = $pdo->prepare('SELECT id FROM usuarios WHERE id = ?');
            $stU->execute([$_SESSION['usuario_id']]);
            if (!$stU->fetch()) {
                unset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_tipo']);
                flash('erro', 'Sua conta não foi encontrada no banco atual. Por favor, entre novamente.');
                header('Location: login.php');
                exit;
            }

            $pdo->beginTransaction();
            $st = $pdo->prepare('SELECT estoque, preco FROM produtos WHERE id = ? FOR UPDATE');
            $st->execute([$id]);
            $atual = $st->fetch();
            if (!$atual) throw new Exception('Produto não encontrado.');
            if ($atual['estoque'] < $quantidade) throw new Exception('Estoque insuficiente.');
            $sub = $atual['preco'] * $quantidade;
            $total = $sub + calcFrete($sub);
            $endereco = $end['rua'] . ', ' . $end['numero'] . ' - ' . $end['cidade'] . ' - CEP ' . $end['cep'];
            $pdo->prepare('INSERT INTO pedidos (usuario_id, produto_id, tamanho, quantidade, total, pagamento, endereco) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$_SESSION['usuario_id'], $id, $tamanho, $quantidade, $total, $pagamento, $endereco]);
            $pedidoId = $pdo->lastInsertId();
            $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ?')->execute([$quantidade, $id]);
            $pdo->commit();
            header('Location: pedido_confirmado.php?id=' . $pedidoId);
            exit;
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erros[] = 'Não foi possível concluir: ' . $ex->getMessage();
        }
    }
}

$qtdCalc = max(1, $quantidade);
$sub = $p['preco'] * $qtdCalc;
$frete = calcFrete($sub);
require_once 'includes/header.php';
?>
<section class="container pagina">
  <div class="pagina-cabecalho">
    <span class="tag">/ Checkout · simulação</span>
    <h1>Finalizar compra</h1>
    <p>Nenhum pagamento real é realizado. Esta é uma compra simulada.</p>
  </div>
  <?php foreach ($erros as $er): ?><div class="alerta alerta-erro"><?= e($er) ?></div><?php endforeach; ?>

  <form method="post" class="checkout-grid" novalidate>
    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
    <div>
      <div class="painel">
        <h2>1. Seu tênis</h2>
        <div class="checkout-item">
          <div class="checkout-img"><?= fotoProduto($p) ?></div>
          <div>
            <span class="card-marca"><?= e($p['marca']) ?></span>
            <h3><?= e($p['nome']) ?></h3>
            <strong><?= preco($p['preco']) ?></strong>
          </div>
        </div>
        <span class="rotulo">Tamanho (BR)</span>
        <div class="tamanhos">
          <?php for ($t = 36; $t <= 44; $t++): ?>
            <label class="tam"><input type="radio" name="tamanho" value="<?= $t ?>" <?= $tamanho === $t ? 'checked' : '' ?>><span><?= $t ?></span></label>
          <?php endfor; ?>
        </div>
        <span class="rotulo">Quantidade</span>
        <div class="qtd">
          <button type="button" data-menos>−</button>
          <input type="number" name="quantidade" value="<?= (int)$quantidade ?>" data-preco="<?= e($p['preco']) ?>">
          <button type="button" data-mais>+</button>
          <span class="estoque"><?= (int)$p['estoque'] ?> em estoque</span>
        </div>
      </div>

      <div class="painel">
        <h2>2. Endereço de entrega</h2>
        <div class="linha-form">
          <label>CEP<input type="text" name="cep" value="<?= e($end['cep']) ?>" placeholder="00000-000" maxlength="9"></label>
          <label>Cidade<input type="text" name="cidade" value="<?= e($end['cidade']) ?>" placeholder="São Paulo"></label>
        </div>
        <div class="linha-form">
          <label class="flex2">Rua<input type="text" name="rua" value="<?= e($end['rua']) ?>" placeholder="Rua / Avenida"></label>
          <label>Número<input type="text" name="numero" value="<?= e($end['numero']) ?>" placeholder="123"></label>
        </div>
      </div>

      <div class="painel">
        <h2>3. Pagamento</h2>
        <div class="pagamentos">
          <label class="pag"><input type="radio" name="pagamento" value="pix" <?= $pagamento === 'pix' ? 'checked' : '' ?>><span>PIX</span></label>
          <label class="pag"><input type="radio" name="pagamento" value="cartao" <?= $pagamento === 'cartao' ? 'checked' : '' ?>><span>Cartão</span></label>
          <label class="pag"><input type="radio" name="pagamento" value="boleto" <?= $pagamento === 'boleto' ? 'checked' : '' ?>><span>Boleto</span></label>
        </div>
        <div class="dados-cartao" data-cartao>
          <label>Número do cartão (fictício)<input type="text" name="cartao_numero" value="<?= e($_POST['cartao_numero'] ?? '') ?>" placeholder="0000 0000 0000 0000" maxlength="23"></label>
          <label>Nome impresso<input type="text" name="cartao_nome" value="<?= e($_POST['cartao_nome'] ?? '') ?>" placeholder="NOME SOBRENOME"></label>
        </div>
      </div>
    </div>

    <aside class="painel resumo">
      <h2>Resumo</h2>
      <div class="resumo-linha"><span>Subtotal</span><span data-subtotal><?= preco($sub) ?></span></div>
      <div class="resumo-linha"><span>Frete</span><span data-frete><?= $frete ? preco($frete) : 'Grátis' ?></span></div>
      <div class="resumo-linha total"><span>Total</span><strong data-total><?= preco($sub + $frete) ?></strong></div>
      <button type="submit" class="btn btn-laranja btn-bloco">Confirmar pedido</button>
      <a href="produto.php?id=<?= (int)$p['id'] ?>" class="btn btn-contorno-escuro btn-bloco">Voltar</a>
      <p class="mini-aviso">Compra simulada para fins acadêmicos.</p>
    </aside>
  </form>
</section>
<?php require_once 'includes/footer.php'; ?>
