<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/conexao.php';

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function preco($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }

function logado() { return isset($_SESSION['usuario_id']); }

function eAdmin() { return logado() && ($_SESSION['usuario_tipo'] ?? '') === 'admin'; }

function exigirLogin() {
    global $pdo;
    if (!logado()) {
        $_SESSION['voltar_para'] = basename($_SERVER['REQUEST_URI']);
        flash('erro', 'Faça login para acessar esta página.');
        header('Location: login.php');
        exit;
    }
    // Verifica se o usuário da sessão realmente existe no banco atual
    $st = $pdo->prepare('SELECT id, nome, tipo, foto FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['usuario_id']]);
    $u = $st->fetch();
    if (!$u) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_tipo'], $_SESSION['usuario_foto']);
        $_SESSION['voltar_para'] = basename($_SERVER['REQUEST_URI']);
        flash('erro', 'Sua sessão expirou ou o banco foi reiniciado. Faça login novamente.');
        header('Location: login.php');
        exit;
    }
    $_SESSION['usuario_nome'] = $u['nome'];
    $_SESSION['usuario_tipo'] = $u['tipo'];
    $_SESSION['usuario_foto'] = $u['foto'] ?? null;
}

function exigirAdmin() {
    global $pdo;
    exigirLogin();
    if (!eAdmin()) {
        flash('erro', 'Acesso negado. Área exclusiva do administrador.');
        header('Location: index.php');
        exit;
    }
}

function flash($tipo, $msg) { $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg]; }

function mostrarFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alerta alerta-' . e($f['tipo']) . '">' . e($f['msg']) . '</div>';
    }
}

function primeiroNome($nome) { return explode(' ', trim($nome))[0]; }

function avatarUsuario($u, $classe = '') {
    $nome = is_array($u) ? ($u['nome'] ?? 'U') : ($u ?? 'U');
    $foto = is_array($u) ? ($u['foto'] ?? null) : null;
    if (!$foto && !empty($_SESSION['usuario_foto']) && is_array($u) && isset($u['id']) && $u['id'] == ($_SESSION['usuario_id'] ?? 0)) {
        $foto = $_SESSION['usuario_foto'];
    }
    if (!empty($foto)) {
        $rel = 'assets/img/avatars/' . $foto;
        if (file_exists(__DIR__ . '/../' . $rel)) {
            return '<img src="' . e($rel) . '" alt="' . e($nome) . '" class="avatar-foto ' . e($classe) . '">';
        }
    }
    $ini = strtoupper(mb_substr(trim($nome), 0, 1, 'UTF-8'));
    return '<div class="avatar-iniciais ' . e($classe) . '"><span>' . e($ini) . '</span></div>';
}

function parcelas($v) {
    $n = $v >= 600 ? 6 : 4;
    return 'Até ' . $n . 'x de ' . preco($v / $n) . ' sem juros';
}

// Ilustração vetorial do tênis (fallback quando não houver foto)
function tenisSVG($p, $classe = '') {
    $c1 = e($p['cor_principal'] ?? '#1A1A1A'); $c2 = e($p['cor_detalhe'] ?? '#F4511E'); $c3 = e($p['cor_sola'] ?? '#F2F1EC');
    return <<<SVG
<svg class="tenis-svg $classe" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <ellipse cx="200" cy="178" rx="170" ry="10" fill="rgba(0,0,0,.08)"/>
  <path d="M30 150 C30 128 40 118 58 112 L120 92 C140 70 160 52 190 46 L222 44 C232 60 250 74 280 84 L340 104 C368 112 378 128 376 150 Z" fill="$c1" stroke="#1A1A1A" stroke-width="3" stroke-linejoin="round"/>
  <path d="M120 92 C150 100 200 104 250 98 L280 84" fill="none" stroke="#1A1A1A" stroke-width="2" opacity=".35"/>
  <path d="M140 120 C190 96 250 90 320 112 C290 126 230 132 150 136 Z" fill="$c2" stroke="#1A1A1A" stroke-width="2.5" stroke-linejoin="round"/>
  <path d="M190 46 L222 44 L232 92 L198 98 Z" fill="$c2" opacity=".55"/>
  <g stroke="#1A1A1A" stroke-width="3" stroke-linecap="round"><line x1="200" y1="62" x2="226" y2="58"/><line x1="206" y1="74" x2="234" y2="70"/><line x1="212" y1="86" x2="242" y2="82"/></g>
  <path d="M24 150 L380 150 C382 160 378 170 366 172 L40 172 C28 172 22 162 24 150 Z" fill="$c3" stroke="#1A1A1A" stroke-width="3" stroke-linejoin="round"/>
  <line x1="40" y1="161" x2="364" y2="161" stroke="#1A1A1A" stroke-width="1.5" opacity=".3"/>
</svg>
SVG;
}

// Foto real do tênis ou fallback vetorial
function fotoProduto($p, $classe = '') {
    if (!empty($p['imagem'])) {
        $imgRel = 'assets/img/produtos/' . $p['imagem'];
        if (file_exists(__DIR__ . '/../' . $imgRel)) {
            return '<img src="' . e($imgRel) . '" alt="' . e($p['nome'] ?? 'Tênis') . '" class="tenis-real ' . e($classe) . '" loading="lazy">';
        }
    }
    return tenisSVG($p, $classe);
}

// Card de produto (estilo premium streetwear)
function cardProduto($p) {
    $desc = '';
    if (!empty($p['preco_antigo']) && $p['preco_antigo'] > $p['preco']) {
        $pct = round(100 - ($p['preco'] / $p['preco_antigo'] * 100));
        $desc = '<span class="badge-off">-' . $pct . '% OFF</span>';
    } else {
        $desc = '<span class="badge-tag">ORIGINAL</span>';
    }
    $antigo = !empty($p['preco_antigo']) ? '<s>' . preco($p['preco_antigo']) . '</s>' : '';
    return '<a class="card-produto revela" data-tilt href="produto.php?id=' . (int)$p['id'] . '">'
        . '<div class="card-topo">'
        . '  <div><span class="card-marca">' . e($p['marca']) . '</span><h3 class="card-nome">' . e($p['nome']) . '</h3></div>'
        . '  <div class="card-preco">' . $antigo . '<strong>' . preco($p['preco']) . '</strong></div>'
        . '</div>'
        . $desc
        . '<div class="card-img"><div class="card-aura"></div>' . fotoProduto($p) . '</div>'
        . '<div class="card-rodape">'
        . '  <span class="card-parcela">' . parcelas($p['preco']) . '</span>'
        . '  <span class="card-cta">Ver detalhes <span>→</span></span>'
        . '</div>'
        . '</a>';
}

function logoSH($classe = '') {
    return '<svg class="logo-sh ' . $classe . '" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-label="SH">'
        . '<path d="M10 22 L52 22 L52 36 L26 36 L26 44 L52 44 L52 78 L10 78 L10 64 L36 64 L36 58 L10 58 Z" fill="currentColor"/>'
        . '<path d="M58 22 L72 22 L72 44 L76 44 L76 22 L90 22 L90 78 L76 78 L76 58 L72 58 L72 78 L58 78 Z" fill="currentColor"/>'
        . '<path d="M52 58 L62 44" stroke="var(--off,#F2F1EC)" stroke-width="4"/>'
        . '</svg>';
}
