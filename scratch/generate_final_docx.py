import zipfile
import xml.etree.ElementTree as ET
import os

input_docx = r'C:/Users/48930344801/.gemini/antigravity/brain/9f9d5c1d-8780-414c-9165-543b4b4442f3/.user_uploaded/media_1794849105730_348a44b1.docx'
output_docx = r'c:/xampp/htdocs/SNKRHAUS/Atividade_Casos_de_Teste_SNKRHAUS.docx'

with zipfile.ZipFile(input_docx, 'r') as zin:
    doc_xml_bytes = zin.read('word/document.xml')

# Register namespaces to avoid ns0 prefix pollution
ET.register_namespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main')
ET.register_namespace('w14', 'http://schemas.microsoft.com/office/word/2010/wordml')
ET.register_namespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
ET.register_namespace('m', 'http://schemas.openxmlformats.org/officeDocument/2006/math')
ET.register_namespace('v', 'urn:schemas-microsoft-com:vml')
ET.register_namespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing')
ET.register_namespace('w10', 'urn:schemas-microsoft-com:office:word')
ET.register_namespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main')
ET.register_namespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture')

root = ET.fromstring(doc_xml_bytes)
W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'

body = root.find(f'{W}body')

# Verify body elements
print(f"Total elements in body: {len(body)}")

# Helper to find all w:t in an element
def set_text(element, new_text):
    t_nodes = element.findall(f'.//{W}t')
    if t_nodes:
        t_nodes[0].text = new_text
        for t in t_nodes[1:]:
            t.text = ''

# Helper to set text on specific run
for p in body.findall(f'.//{W}p'):
    for r in p.findall(f'{W}r'):
        for t in r.findall(f'{W}t'):
            if t.text:
                if 'Desenvolvedora Full Stack' in t.text:
                    t.text = t.text.replace('Desenvolvedora Full Stack', 'Desenvolvedor Full Stack')
                if 'Escrito pela Desenvolvedora' in t.text:
                    t.text = t.text.replace('Escrito pela Desenvolvedora', 'Escrito pelo Desenvolvedor')

# Element [6]: Table - Identificacao do trio
# Make sure Caio Figueira Machado, Lucas Soares, Thomas Duarte are properly written
tbl6 = body[6]
print("Tbl 6 (Trio) updated.")

# Element [8]: Escrito pelo Desenvolvedor Full Stack
# Element [9]: System description
# Let's inspect runs of body[9]
p9 = body[9]
runs = p9.findall(f'{W}r')
print(f"P9 has {len(runs)} runs")
for i, r in enumerate(runs):
    t = r.find(f'{W}t')
    txt = t.text if t is not None else ''
    print(f"  run {i}: {txt}")

# Run 0 is bold "Cardápio Online" -> "SNKRHAUS"
# Run 1 is text: " é o sistema de pedidos..." -> SNKRHAUS description
if len(runs) >= 2:
    t0 = runs[0].find(f'{W}t')
    if t0 is not None:
        t0.text = "SNKRHAUS"
    t1 = runs[1].find(f'{W}t')
    if t1 is not None:
        t1.text = " é o e-commerce de calçados e sneakers streetwear exclusivos. O cliente cria uma conta, entra, visualiza as vitrines e o catálogo com filtros por categoria, escolhe o modelo, numeração e quantidade e realiza o pedido com checkout seguro (Pix, Cartão ou Boleto). O cliente acompanha seus pedidos, personaliza seu perfil com upload de foto de avatar e o administrador gerencia os usuários cadastrados."

# Element [10]: Tech stack
# Inspect runs of body[10]
p10 = body[10]
runs10 = p10.findall(f'{W}r')
print(f"P10 has {len(runs10)} runs")
for i, r in enumerate(runs10):
    t = r.find(f'{W}t')
    txt = t.text if t is not None else ''
    print(f"  run {i}: {txt}")
    if txt == 'cardapionline':
        t.text = 'snkrhaus'

# Element [12]: Table - Tabelas do banco
# Inspect rows
tbl12 = body[12]
rows12 = tbl12.findall(f'{W}tr')
print(f"Tbl 12 has {len(rows12)} rows")
for row_idx, tr in enumerate(rows12):
    cells = tr.findall(f'{W}tc')
    row_texts = []
    for c in cells:
        c_txt = "".join(t.text for t in c.findall(f'.//{W}t') if t.text)
        row_texts.append(c_txt)
    print(f"  row {row_idx}: {' | '.join(row_texts)}")

# Row 1: usuarios
u_cell = rows12[1].findall(f'{W}tc')[1]
set_text(u_cell, "id, nome, email (único), senha (hash), telefone, foto, tipo (comum ou admin), criado_em")

# Row 2: produtos
prod_cell = rows12[2].findall(f'{W}tc')[1]
set_text(prod_cell, "id, nome, marca, categoria, descricao, preco, preco_antigo, estoque, imagem, secao, criado_em")

# Row 3: pedidos
ped_cell = rows12[3].findall(f'{W}tc')[1]
set_text(ped_cell, "id, usuario_id, produto_id, tamanho, quantidade, total, pagamento, endereco, criado_em")

# Row 4: redefinicoes
red_cell = rows12[4].findall(f'{W}tc')[1]
set_text(red_cell, "id, email, token, usado, criado_em")

# Requisitos funcionais (Tables 18, 20, 22, 24, 26, 28, 30)
# Check table 22 row 2 (RF11: home.php -> index.php)
tbl22 = body[22]
for tr in tbl22.findall(f'{W}tr'):
    c_txt = "".join(t.text for t in tr.findall(f'.//{W}t') if t.text)
    if 'RF11' in c_txt:
        # Replace home.php with index.php
        for t in tr.findall(f'.//{W}t'):
            if t.text and 'home.php' in t.text:
                t.text = t.text.replace('home.php', 'index.php')
                print("RF11 updated: home.php -> index.php")

# Check table 24 row 1 (RF13: mention foto)
tbl24 = body[24]
for tr in tbl24.findall(f'{W}tr'):
    c_txt = "".join(t.text for t in tr.findall(f'.//{W}t') if t.text)
    if 'RF13' in c_txt:
        for t in tr.findall(f'.//{W}t'):
            if t.text and 'Alterar nome e e-mail' in t.text:
                t.text = t.text.replace('Alterar nome e e-mail.', 'Alterar nome, e-mail e foto de perfil.')
                print("RF13 updated: foto de perfil added")

# Check table 26 row 2 (RF16: tamanho)
tbl26 = body[26]
for tr in tbl26.findall(f'{W}tr'):
    c_txt = "".join(t.text for t in tr.findall(f'.//{W}t') if t.text)
    if 'RF16' in c_txt:
        for t in tr.findall(f'.//{W}t'):
            if t.text and 'Escolher produto e quantidade' in t.text:
                t.text = t.text.replace('Escolher produto e quantidade.', 'Escolher produto, tamanho e quantidade.')
                print("RF16 updated: tamanho added")

# Save modified XML
new_doc_xml = ET.tostring(root, encoding='utf-8', xml_declaration=True)

# Write back to zip
with zipfile.ZipFile(input_docx, 'r') as zin:
    with zipfile.ZipFile(output_docx, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            if item.filename == 'word/document.xml':
                zout.writestr(item, new_doc_xml)
            else:
                zout.writestr(item, zin.read(item.filename))

print(f"Generated {output_docx} successfully! Size: {os.path.getsize(output_docx)} bytes.")
