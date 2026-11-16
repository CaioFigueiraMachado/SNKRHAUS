</main>
<footer class="rodape">
  <div class="container rodape-grid">
    <div>
      <a href="index.php" class="marca marca-rodape"><?= logoSH() ?><span class="marca-texto">SNKR<br>HAUS</span></a>
      <p class="rodape-desc">Sneakers originais, direto das marcas. Design e inovação em cada detalhe.</p>
      <span class="selo-est">/ EST. 2026</span>
    </div>
    <div>
      <h4>Loja</h4>
      <a href="catalogo.php?secao=lancamento">Lançamentos</a>
      <a href="catalogo.php?secao=mais_vendido">Mais vendidos</a>
      <a href="catalogo.php?secao=curation">Curation</a>
      <a href="catalogo.php?ofertas=1">Ofertas</a>
    </div>
    <div>
      <h4>Conta</h4>
      <?php if (logado()): ?>
        <a href="perfil.php">Meu perfil</a>
        <a href="logout.php">Sair</a>
      <?php else: ?>
        <a href="login.php">Entrar</a>
        <a href="cadastro.php">Criar conta</a>
        <a href="redefinir_senha.php">Esqueci minha senha</a>
      <?php endif; ?>
    </div>
    <div>
      <h4>SNKRHAUS</h4>
      <span>Lisboa · Berlin · São Paulo</span>
      <span>100% originais</span>
      <span>Compras simuladas (projeto acadêmico)</span>
    </div>
  </div>
  <div class="rodape-base">© 2026 SNKRHAUS — Todos os direitos reservados.</div>
</footer>
<script src="assets/js/main.js"></script>
</body>
</html>
