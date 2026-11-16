import zipfile
import shutil
import os

input_docx = r'C:/Users/48930344801/.gemini/antigravity/brain/9f9d5c1d-8780-414c-9165-543b4b4442f3/.user_uploaded/media_1794849105730_348a44b1.docx'
output_docx = r'c:/xampp/htdocs/SNKRHAUS/Atividade_Casos_de_Teste_SNKRHAUS.docx'

# Read word/document.xml
with zipfile.ZipFile(input_docx, 'r') as zin:
    doc_xml = zin.read('word/document.xml').decode('utf-8')

# 1. Update "Desenvolvedora Full Stack" to "Desenvolvedor Full Stack" in trio and headers
doc_xml = doc_xml.replace('Desenvolvedora Full Stack', 'Desenvolvedor Full Stack')
doc_xml = doc_xml.replace('Escrito pela Desenvolvedora Full Stack', 'Escrito pelo Desenvolvedor Full Stack')

# 2. Update Section 1 system description
old_p9_target = '<ns0:t xml:space="preserve">Cardápio Online</ns0:t></ns0:r><ns0:r ns0:rsidDel="00000000" ns0:rsidR="00000000" ns0:rsidRPr="00000000"><ns0:rPr><ns0:rtl ns0:val="0" /></ns0:rPr><ns0:t xml:space="preserve"> é o sistema de pedidos da cantina da escola. O aluno cria uma conta, entra, escolhe o lanche e a quantidade e confirma o pedido. Depois paga e retira no balcão, sem pegar fila no intervalo. O administrador da cantina gerencia os usuários cadastrados.</ns0:t>'
new_p9_target = '<ns0:t xml:space="preserve">SNKRHAUS</ns0:t></ns0:r><ns0:r ns0:rsidDel="00000000" ns0:rsidR="00000000" ns0:rsidRPr="00000000"><ns0:rPr><ns0:rtl ns0:val="0" /></ns0:rPr><ns0:t xml:space="preserve"> é o e-commerce streetwear de calçados e sneakers de edição limitada e alta procura. O cliente cria uma conta, entra, navega pelas vitrines e catálogo com filtros por categoria, escolhe o tênis, tamanho e quantidade e confirma o pedido no checkout com pagamento seguro (Pix, Cartão ou Boleto). O cliente acompanha seus pedidos, personaliza seu perfil com upload de foto e o administrador gerencia os usuários cadastrados.</ns0:t>'

if old_p9_target in doc_xml:
    doc_xml = doc_xml.replace(old_p9_target, new_p9_target)
    print("Replaced P9 successfully")
else:
    print("Warning: old_p9_target not found by exact string, checking fallback")
    doc_xml = doc_xml.replace('Cardápio Online', 'SNKRHAUS')
    doc_xml = doc_xml.replace('é o sistema de pedidos da cantina da escola. O aluno cria uma conta, entra, escolhe o lanche e a quantidade e confirma o pedido. Depois paga e retira no balcão, sem pegar fila no intervalo. O administrador da cantina gerencia os usuários cadastrados.', 'é o e-commerce streetwear de calçados e sneakers de edição limitada e alta procura. O cliente cria uma conta, entra, navega pelas vitrines e catálogo com filtros por categoria, escolhe o tênis, tamanho e quantidade e confirma o pedido no checkout com pagamento seguro (Pix, Cartão ou Boleto). O cliente acompanha seus pedidos, personaliza seu perfil com upload de foto e o administrador gerencia os usuários cadastrados.')

# 3. Update database name from cardapionline to snkrhaus
doc_xml = doc_xml.replace('cardapionline', 'snkrhaus')

# 4. Update Database Table columns in Table 12
# Old columns:
# usuarios: id, nome, email (único), senha (hash), tipo (comum ou admin), criado_em
# produtos: id, nome, preco, estoque
# pedidos: id, usuario_id, produto_id, quantidade, total, criado_em
# redefinicoes: id, email, criado_em
doc_xml = doc_xml.replace('id, nome, email (único), senha (hash), tipo (comum ou admin), criado_em',
                          'id, nome, email (único), senha (hash), telefone, foto, tipo (comum ou admin), criado_em')

doc_xml = doc_xml.replace('>id, nome, preco, estoque<',
                          '>id, nome, marca, categoria, descricao, preco, preco_antigo, estoque, imagem, secao, criado_em<')

doc_xml = doc_xml.replace('id, usuario_id, produto_id, quantidade, total, criado_em',
                          'id, usuario_id, produto_id, tamanho, quantidade, total, pagamento, endereco, criado_em')

doc_xml = doc_xml.replace('>id, email, criado_em<',
                          '>id, email, token, usado, criado_em<')

# 5. RF11: home.php -> index.php
doc_xml = doc_xml.replace('para home.php', 'para index.php')

# Write back into new docx
with zipfile.ZipFile(input_docx, 'r') as zin:
    with zipfile.ZipFile(output_docx, 'w', compression=zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            if item.filename == 'word/document.xml':
                zout.writestr(item, doc_xml.encode('utf-8'))
            else:
                zout.writestr(item, zin.read(item.filename))

print(f"Created {output_docx} successfully! Size: {os.path.getsize(output_docx)} bytes")
