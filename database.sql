-- SNKRHAUS - Banco de dados com Tênis e Imagens Reais
-- Importar no phpMyAdmin (aba Importar) ou colar na aba SQL.

DROP DATABASE IF EXISTS snkrhaus;
CREATE DATABASE snkrhaus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE snkrhaus;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  telefone VARCHAR(20) DEFAULT NULL,
  foto VARCHAR(255) DEFAULT NULL,
  tipo ENUM('comum','admin') NOT NULL DEFAULT 'comum',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  marca VARCHAR(50) NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL,
  preco_antigo DECIMAL(10,2) DEFAULT NULL,
  estoque INT NOT NULL DEFAULT 0,
  imagem VARCHAR(255) DEFAULT NULL,
  cor_principal VARCHAR(7) NOT NULL DEFAULT '#1A1A1A',
  cor_detalhe VARCHAR(7) NOT NULL DEFAULT '#F4511E',
  cor_sola VARCHAR(7) NOT NULL DEFAULT '#F2F1EC',
  secao ENUM('lancamento','mais_vendido','curation','recente') NOT NULL DEFAULT 'recente',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  produto_id INT NOT NULL,
  tamanho INT NOT NULL,
  quantidade INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  pagamento ENUM('pix','cartao','boleto') NOT NULL,
  endereco VARCHAR(255) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE redefinicoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  token VARCHAR(64) NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Usuarios iniciais
-- admin@snkrhaus.com / admin123     (administrador)
-- cliente@snkrhaus.com / cliente123 (usuario comum)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador SNKRHAUS', 'admin@snkrhaus.com', '$2y$10$NyMM5LKNaFNsD1QXBG7WweI2rrSafg9iRelDbgojy3FyTY0KWCsRe', 'admin'),
('Cliente Teste', 'cliente@snkrhaus.com', '$2y$10$DuJEZNtUJ1ykz4RQBzzPDuPeUpFUz8QIR0/YtX/sHeYOml3REAJAO', 'comum');

-- Catálogo de Tênis Reais (com imagens locais em assets/img/produtos/)
INSERT INTO produtos (nome, marca, categoria, descricao, preco, preco_antigo, estoque, imagem, secao) VALUES
('Air Force 1 Wild Sesame', 'Nike', 'Lifestyle', 'Sneaker autêntico Nike Air Force 1 Wild Sesame. Cabedal estruturado, detalhes utilitários e solado clássico. 100% original.', 499.95, NULL, 30, 'prod_1.jpg', 'lancamento'),
('Air Force 1 07 Black White', 'Nike', 'Lifestyle', 'O lendário Air Force 1 na consagrada combinação Black & White com amortecimento Nike Air encapsulado.', 527.93, NULL, 25, 'prod_2.jpg', 'lancamento'),
('Air Max 1 PRM Baltic Blue', 'Nike', 'Corrida', 'O pioneiro com a cápsula Air visível em edição Premium Baltic Blue de camurça aveludada.', 410.97, 513.71, 20, 'prod_3.jpg', 'lancamento'),
('Air Max 90 White White', 'Nike', 'Corrida', 'Silhueta icônica dos anos 90 totalmente monocromática em branco com máximo conforto.', 659.93, NULL, 35, 'prod_4.jpg', 'lancamento'),
('Cortez Pink Foam', 'Nike', 'Retrô', 'O primeiro clássico de corrida da Nike em versão moderna e suave Pink Foam.', 461.93, NULL, 18, 'prod_5.jpg', 'lancamento'),
('Field General Malachite', 'Nike', 'Lifestyle', 'Visual inspirado no futebol americano clássico dos anos 80 em verde malaquita.', 527.93, NULL, 22, 'prod_6.jpg', 'lancamento'),
('Gel-1130 Cream Cream', 'Asics', 'Corrida', 'Estética retrô-running dos anos 2000 com tecnologia GEL visível e tons neutros de creme.', 899.90, 1124.88, 20, 'prod_7.jpg', 'lancamento'),
('Gel-1130 Cream Cocoa Powder', 'Asics', 'Corrida', 'Combinação requintada em creme e marrom cacau com amortecimento GEL duradouro.', 699.90, NULL, 18, 'prod_8.jpg', 'mais_vendido'),
('Gel-DS Trainer Loop Black Gunmetal', 'Asics', 'Corrida', 'Design esportivo de alta performance com detalhes metálicos escuros e estabilidade superior.', 899.90, NULL, 15, 'prod_9.jpg', 'mais_vendido'),
('Skyhand OG Light Sage Ash Green', 'Asics', 'Lifestyle', 'Clássico das quadras de handball reeditado com camurça premium verde sálvia.', 799.90, 999.88, 16, 'prod_10.jpg', 'mais_vendido'),
('Japan S Real White Black', 'Asics', 'Lifestyle', 'Inspirado nos arquivos de basquete de 1981, perfil baixo com listras icônicas pretas.', 499.90, NULL, 40, 'prod_11.jpg', 'mais_vendido'),
('Speedcat VTG x Rosé Burgundy', 'Puma', 'Lifestyle', 'Edição especial Speedcat em colaboração com Rosé no tom bordô apaixonante.', 699.90, NULL, 12, 'prod_12.jpg', 'mais_vendido'),
('Speedcat VTG x Rosé Espresso', 'Puma', 'Lifestyle', 'Camurça aveludada marrom café com perfil racer aerodinâmico e solado fino.', 699.90, NULL, 14, 'prod_13.jpg', 'mais_vendido'),
('Grandstand II Suede Atmosphere Grey', 'Nike', 'Lifestyle', 'Visual minimalista em camurça cinza atmosfera, perfeito para composições urbanas.', 395.93, NULL, 22, 'prod_14.jpg', 'mais_vendido'),
('Palermo Artisan Alpine Snow', 'Puma', 'Retrô', 'O clássico terrace italiano com etiqueta dourada, biqueira T-toe e sola de borracha gum.', 599.90, 749.90, 20, 'prod_15.jpg', 'curation'),
('Cloudmonster Void Ember Ox', 'On Running', 'Corrida', 'A maior tecnologia CloudTec da On para amortecimento colossal e retorno de energia incomparável.', 1199.00, NULL, 10, 'prod_16.jpg', 'curation'),
('Cloudmonster White Frost', 'On Running', 'Corrida', 'Sensação inconfundível de correr sobre as nuvens em acabamento clean White Frost.', 1199.00, NULL, 12, 'prod_17.jpg', 'curation'),
('The Roger Dew Argil', 'On Running', 'Lifestyle', 'Desenvolvido em parceria com Roger Federer, unindo elegância do tênis tradicional com conforto CloudTec.', 999.00, NULL, 15, 'prod_18.jpg', 'curation'),
('Cloudtilt Cinder Iceberg', 'On Running', 'Lifestyle', 'Ultra leveza com engenharia CloudTec Phase que se comprime sequencialmente a cada passo.', 1099.00, NULL, 14, 'prod_19.jpg', 'curation'),
('Cortez VNTG Summit White', 'Nike', 'Retrô', 'Construção vintage com espuma exposta na língua e detalhes nostálgicos da herança Nike.', 769.89, NULL, 16, 'prod_20.jpg', 'curation'),
('530 Beige Classic', 'New Balance', 'Corrida', 'O queridinho do streetwear global, trazendo a estética dad shoe dos anos 2000 com tecnologia ABZORB.', 699.90, 849.90, 28, 'prod_21.jpg', 'recente'),
('530 White Navy', 'New Balance', 'Corrida', 'Cabedal em mesh respirável com sobreposições metálicas e marinho clássico.', 699.90, NULL, 24, 'prod_22.jpg', 'recente'),
('Chuck 70 Ox Sequins Almond', 'Converse', 'Lifestyle', 'Edição especial com lantejoulas sutis em tom amêndoa sobre a silhueta premium Chuck 70.', 499.90, NULL, 18, 'prod_23.jpg', 'recente'),
('Chuck 70 Ox Sequins Black', 'Converse', 'Lifestyle', 'O icônico Chuck 70 com detalhes reluzentes pretos e lona 12oz de alta gramatura.', 499.90, NULL, 20, 'prod_24.jpg', 'recente'),
('Run Star Crush Ox Croc Leather Bordeaux', 'Converse', 'Lifestyle', 'Solado futurista platform com textura de couro croco em bordô profundo.', 599.90, NULL, 15, 'prod_25.jpg', 'recente'),
('Sambae Core Black White', 'Adidas', 'Lifestyle', 'Nova interpretação do lendário Samba com plataforma elevada e biqueira bordada.', 699.99, 874.99, 26, 'prod_26.jpg', 'recente');
