from bs4 import BeautifulSoup #hilf daten aus einer html datei zu entnehmen 
import requests 
import csv



file= open('/home/celvic/DBS/codes/testfile.csv', 'w') #dokument oeffnen im writer format
writer= csv.writer(file, delimiter= ';' )
    

page=requests.get('https://www.heise.de/thema/https') 
pageout=BeautifulSoup(page.text,'html.parser') # erstellt ein soup page object

content = pageout.find_all(class_="keywordliste") #alle elemente finden die zur klasse keywordluste gehoeren also ueberschrift sind

txt=[]
for c in content:
    c=c.findAll("header") 
    for t in c:
        txt.append(t.text)
    writer.writerow(txt) #die ueberschriften sollen in unsere neue csv datei geschrieben werden 
print (txt)
    
file.close()

    
