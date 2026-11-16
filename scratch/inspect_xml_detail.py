import zipfile
import xml.etree.ElementTree as ET

docx_path = r'C:/Users/48930344801/.gemini/antigravity/brain/9f9d5c1d-8780-414c-9165-543b4b4442f3/.user_uploaded/media_1794849105730_348a44b1.docx'

z = zipfile.ZipFile(docx_path)
xml_content = z.read('word/document.xml').decode('utf-8')
root = ET.fromstring(xml_content)
namespaces = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
body = root.find('w:body', namespaces)

for idx in [6, 8, 9, 10, 12, 14, 22]:
    print(f"--- INDEX {idx} ---")
    elem = body[idx]
    print(ET.tostring(elem, encoding='unicode')[:1000])
