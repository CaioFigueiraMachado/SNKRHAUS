// SNKRHAUS - Motion & Interactivity Engine

document.addEventListener('DOMContentLoaded', function () {

  // ===== 1. Scroll Reveal (IntersectionObserver) =====
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('ativo');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    document.querySelectorAll('.revela, .secao, .cat-card, .marca-box').forEach(function (el) {
      el.classList.add('revela');
      observer.observe(el);
    });
  } else {
    document.querySelectorAll('.revela').forEach(function (el) {
      el.classList.add('ativo');
    });
  }

  // ===== 2. Carrossel Hero com transição suave e pausa ao passar mouse =====
  document.querySelectorAll('[data-carrossel]').forEach(function (c) {
    var slides = c.querySelectorAll('.hero-slide');
    var pontos = c.querySelector('.hero-pontos');
    var i = 0, timer;

    slides.forEach(function (_, idx) {
      var b = document.createElement('button');
      b.setAttribute('aria-label', 'Slide ' + (idx + 1));
      b.onclick = function () { ir(idx); };
      pontos.appendChild(b);
    });

    function ir(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('ativo', k === i); });
      pontos.querySelectorAll('button').forEach(function (b, k) { b.classList.toggle('ativo', k === i); });
      reiniciarTimer();
    }

    function reiniciarTimer() {
      clearInterval(timer);
      timer = setInterval(function () { ir(i + 1); }, 6500);
    }

    var setaAnt = c.querySelector('[data-ant]');
    var setaProx = c.querySelector('[data-prox]');
    if (setaAnt) setaAnt.onclick = function () { ir(i - 1); };
    if (setaProx) setaProx.onclick = function () { ir(i + 1); };

    c.addEventListener('mouseenter', function () { clearInterval(timer); });
    c.addEventListener('mouseleave', reiniciarTimer);

    ir(0);
  });

  // ===== 3. Efeito 3D Tilt nos cards de tênis =====
  if (window.matchMedia('(hover: hover)').matches) {
    document.querySelectorAll('[data-tilt]').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var centerX = rect.width / 2;
        var centerY = rect.height / 2;
        var rotateX = ((y - centerY) / centerY) * -7;
        var rotateY = ((x - centerX) / centerX) * 7;

        card.style.transform = 'perspective(800px) rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg) translateY(-8px)';
      });

      card.addEventListener('mouseleave', function () {
        card.style.transform = '';
      });
    });
  }

  // ===== 4. Drag to Scroll nos trilhos horizontais =====
  document.querySelectorAll('[data-trilho]').forEach(function (trilho) {
    var isDown = false, startX, scrollLeft;

    trilho.addEventListener('mousedown', function (e) {
      isDown = true;
      startX = e.pageX - trilho.offsetLeft;
      scrollLeft = trilho.scrollLeft;
    });

    trilho.addEventListener('mouseleave', function () { isDown = false; });
    trilho.addEventListener('mouseup', function () { isDown = false; });

    trilho.addEventListener('mousemove', function (e) {
      if (!isDown) return;
      e.preventDefault();
      var x = e.pageX - trilho.offsetLeft;
      var walk = (x - startX) * 1.5;
      trilho.scrollLeft = scrollLeft - walk;
    });
  });

  // ===== 5. Barra de progresso do trilho recente =====
  document.querySelectorAll('[data-progresso]').forEach(function (t) {
    var barra = t.parentElement.querySelector('.progresso span');
    if (!barra) return;
    function at() {
      var max = t.scrollWidth - t.clientWidth;
      var vis = t.clientWidth / t.scrollWidth * 100;
      barra.style.width = Math.max(vis, max > 0 ? vis + (t.scrollLeft / max) * (100 - vis) : 100) + '%';
    }
    t.addEventListener('scroll', at);
    window.addEventListener('resize', at);
    at();
  });

  // ===== 6. Quantidade e cálculo dinâmico de total =====
  function moeda(v) {
    return 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  document.querySelectorAll('.qtd').forEach(function (q) {
    var inp = q.querySelector('input');
    if (!inp) return;

    function atualizar() {
      var preco = parseFloat(inp.dataset.preco);
      if (!preco) return;
      var n = Math.max(1, parseInt(inp.value) || 1);
      var sub = preco * n;
      var frete = sub >= 999 ? 0 : 29.90;
      var s = document.querySelector('[data-subtotal]');
      var f = document.querySelector('[data-frete]');
      var t = document.querySelector('[data-total]');
      if (s) s.textContent = moeda(sub);
      if (f) f.textContent = frete === 0 ? 'Grátis' : moeda(frete);
      if (t) t.textContent = moeda(sub + frete);
    }

    var btnMenos = q.querySelector('[data-menos]');
    var btnMais = q.querySelector('[data-mais]');
    if (btnMenos) btnMenos.onclick = function () {
      inp.value = Math.max(1, (parseInt(inp.value) || 1) - 1);
      atualizar();
    };
    if (btnMais) btnMais.onclick = function () {
      var max = parseInt(inp.getAttribute('max')) || 999;
      var cur = parseInt(inp.value) || 0;
      if (cur < max) inp.value = cur + 1;
      atualizar();
    };
    inp.addEventListener('input', atualizar);
  });

  // ===== 7. Alternador dinâmico de dados do cartão de crédito =====
  var cartao = document.querySelector('[data-cartao]');
  if (cartao) {
    function verCartao() {
      var sel = document.querySelector('input[name=pagamento]:checked');
      cartao.classList.toggle('ativo', !!sel && sel.value === 'cartao');
    }
    document.querySelectorAll('input[name=pagamento]').forEach(function (r) {
      r.addEventListener('change', verCartao);
    });
    verCartao();
  }

  // ===== 8. Ambient Cursor Glow (Brutalist Streetwear Motion) =====
  if (window.matchMedia('(pointer: fine)').matches) {
    var glow = document.createElement('div');
    glow.className = 'cursor-glow';
    document.body.appendChild(glow);

    var mouseX = -500, mouseY = -500;
    var currentX = -500, currentY = -500;

    window.addEventListener('mousemove', function (e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
    });

    function animarGlow() {
      currentX += (mouseX - currentX) * 0.15;
      currentY += (mouseY - currentY) * 0.15;
      glow.style.transform = 'translate(' + currentX.toFixed(1) + 'px, ' + currentY.toFixed(1) + 'px) translate(-50%, -50%)';
      requestAnimationFrame(animarGlow);
    }
    requestAnimationFrame(animarGlow);
  }

});
