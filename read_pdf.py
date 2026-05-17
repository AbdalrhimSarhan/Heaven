import PyPDF2
import sys
sys.stdout.reconfigure(encoding='utf-8')
pdf = open('مشروع_مادة_البرمجة_المتوازية_الفصل_الدراسي_2026.pdf', 'rb')
reader = PyPDF2.PdfReader(pdf)
for i in range(len(reader.pages)):
    print(f'--- Page {i+1} ---')
    text = reader.pages[i].extract_text()
    print(text)
