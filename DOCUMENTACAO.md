# SENAI São Paulo
## ATIVIDADE DE FIXAÇÃO E APROFUNDAMENTO — Casos de Teste · Trio
### PROJETO: SNKRHAUS — Loja Conceito de Sneakers

---

## Identificação do trio

| Papel | Nome | Responsável por |
|---|---|---|
| **Desenvolvedor Full Stack** | **Caio Figueira Machado** | Sistema, resumo e requisitos funcionais. |
| **QA Sênior** | **Lucas Soares** | Escrever os 38 casos de teste (5 por tela + 3 de banco). |
| **QA Júnior** | **Thomas Duarte** | Executar os 38 casos de teste pelo IP, coletar prints e preencher resultados. |

---

## 1. O que é o sistema
*Escrito pelo Desenvolvedor Full Stack*

**SNKRHAUS** é o e-commerce de calçados e sneakers streetwear exclusivos. O cliente cria uma conta, entra, visualiza as vitrines e o catálogo com filtros por categoria, escolhe o modelo, numeração e quantidade e realiza o pedido com checkout seguro (Pix, Cartão ou Boleto). O cliente acompanha seus pedidos, personaliza seu perfil com upload de foto de avatar e o administrador gerencia os usuários cadastrados.

Feito em PHP, MySQL, HTML e CSS próprio, sem framework. Banco local no phpMyAdmin, chamado `snkrhaus`. Todas as consultas usam `prepare` e as senhas são gravadas com `password_hash`.

---

## Tabelas do banco

| Tabela | Colunas |
|---|---|
| **usuarios** | `id`, `nome`, `email` (único), `senha` (hash), `telefone`, `foto`, `tipo` (comum ou admin), `criado_em` |
| **produtos** | `id`, `nome`, `marca`, `categoria`, `descricao`, `preco`, `preco_antigo`, `estoque`, `imagem`, `secao`, `criado_em` |
| **pedidos** | `id`, `usuario_id`, `produto_id`, `tamanho`, `quantidade`, `total`, `pagamento`, `endereco`, `criado_em` |
| **redefinicoes** | `id`, `email`, `token`, `usado`, `criado_em` |

---

## 3. Requisitos funcionais por tela
*Escrito pelo Desenvolvedor Full Stack*

### Home
| Código | Requisito |
|---|---|
| **RF01** | Exibir, para quem não está logado, a apresentação da loja e vitrines de tênis com preço. |
| **RF02** | Oferecer os links Criar conta e Entrar. |
| **RF03** | Na home de logado, escrever “Olá, nome” e quantos pedidos a pessoa já fez. |
| **RF04** | Mostrar o menu conforme o tipo: o link Admin só aparece para o administrador. |
| **RF05** | Sair encerra a sessão. Páginas restritas sem login levam ao login com aviso. |

### Cadastro
| Código | Requisito |
|---|---|
| **RF06** | Receber nome, e-mail e senha. Todos obrigatórios. |
| **RF07** | Aceitar só e-mail em formato válido e que ainda não exista no banco. |
| **RF08** | Exigir senha com pelo menos 6 caracteres e gravá-la com password_hash. |
| **RF09** | Gravar o usuário na tabela usuarios com o tipo comum. |

### Login
| Código | Requisito |
|---|---|
| **RF10** | Conferir e-mail e senha com password_verify. |
| **RF11** | Levar o administrador para admin.php e o usuário comum para index.php. |
| **RF12** | Em caso de erro, mostrar só “E-mail ou senha inválidos.”, sem dizer qual campo errou. |

### Perfil
| Código | Requisito |
|---|---|
| **RF13** | Alterar nome, e-mail e foto de perfil. O e-mail precisa ser válido e não pode ser de outra conta. |
| **RF14** | Trocar a senha só com a senha atual correta. Nova senha com pelo menos 6 caracteres. |

### Fazer pedido (tela de execução)
| Código | Requisito |
|---|---|
| **RF15** | Abrir só para quem está logado. |
| **RF16** | Escolher produto, tamanho e quantidade. Mínimo 1 e máximo o estoque disponível. |
| **RF17** | Gravar o pedido na tabela pedidos, calcular o total e baixar o estoque. |
| **RF18** | Listar os últimos 5 pedidos do usuário. |

### Redefinir senha
| Código | Requisito |
|---|---|
| **RF19** | Pedir o e-mail, validar o formato e conferir se ele existe no banco. |
| **RF20** | Registrar o pedido na tabela redefinicoes e mostrar a confirmação de envio. A troca da senha não é feita, como permite a atividade. |

### Área do administrador
| Código | Requisito |
|---|---|
| **RF21** | Abrir só para o tipo admin. O usuário comum volta para a home com “Acesso restrito”. |
| **RF22** | Listar os usuários com ID, nome, e-mail e tipo. |
| **RF23** | Editar nome, e-mail e tipo de um usuário. |
| **RF24** | Excluir usuário com confirmação. O admin não pode excluir a própria conta. |

---

## 4. Estrutura de Arquivos do Projeto

```
c:\xampp\htdocs\SNKRHAUS\
├── assets\
│   ├── css\style.css             (CSS3 com design system editorial streetwear, motion, glassmorphism)
│   ├── js\main.js                (3D Card Tilt, Cursor glow aura, Scroll reveal, drag carousels)
│   └── img\
│       ├── logo\snkrhaus-logo.png
│       ├── produtos\prod_1.jpg ... prod_26.jpg (26 sneakers reais locais)
│       └── avatars\avatar_padrao.svg
├── includes\
│   ├── conexao.php               (Conexão PDO MySQL com utf8mb4 e prepare)
│   ├── funcoes.php               (Sanitização, sessões, flash messages, contadores)
│   ├── header.php                (Header fixo com glassmorphism, menu dinâmico e avatar)
│   └── footer.php                (Footer institucional e newsletter)
├── index.php                     (Home - Vitrines, banners, estado logado/deslogado)
├── catalogo.php                  (Catálogo completo de tênis com filtros por categoria)
├── produto.php                   (Página de detalhes do produto, seleção de numeração)
├── checkout.php                  (Fazer pedido / tela de execução com Pix, Cartão ou Boleto)
├── pedido_confirmado.php         (Confirmação de compra com resumo do pedido)
├── cadastro.php                  (Cadastro de cliente com validações rigorosas)
├── login.php                     (Login seguro com password_verify)
├── logout.php                    (Encerramento seguro de sessão)
├── perfil.php                    (Edição de nome/e-mail, foto avatar e troca de senha)
├── redefinir_senha.php           (Solicitação de redefinição gravada na tabela redefinicoes)
├── admin.php                     (Painel exclusivo com listagem e exclusão de usuários)
├── admin_editar.php              (Edição de usuário pelo administrador)
├── database.sql                  (Script DDL/DML completo do banco snkrhaus)
├── Atividade_Casos_de_Teste_SNKRHAUS.docx  (Arquivo oficial editado da atividade SENAI)
└── DOCUMENTACAO.md               (Documentação detalhada do projeto)
```
