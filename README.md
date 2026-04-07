# ⚖️ JustAlert (justalert.eu)

> **Sistem gratuit și open-source de monitorizare a dosarelor pe portalul instanțelor din România (portalquery.just.ro).**

![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)
![MySQL](https://img.shields.io/badge/MySQL-Supported-4479A1.svg)

## 📖 Povestea Proiectului (De ce am construit JustAlert?)
Eram pe punctul de a rata o dezbatere de moștenire extrem de importantă dintr-un motiv complet banal: adresa mea a fost trecuta necunoscuta în dosar, motiv pentru care nu am primit niciodată citația fizică acasă. 

Atunci am realizat că în România, dacă nu îți verifici manual numele pe portalul instanțelor de judecată, poți fi parte într-un proces fără să ai habar, riscând să pierzi termene și drepturi. Așa a luat naștere **JustAlert** – o plasă de siguranță digitală pentru orice cetățean.

Platforma verifică automat și zilnic numele tău și te notifică pe email dacă apare un dosar nou sau dacă unul existent primește un termen nou.

## ✨ Funcționalități Principale
* 🛡️ **Autentificare fără parole (Magic Link):** Acces securizat direct de pe email.
* 📩 **Alerte Inteligente:** Gruparea notificărilor pentru a evita spam-ul.
* 🌍 **Suport Multilingv:** Interfață completă în Română și Engleză.
* ⚖️ **GDPR & Transparență:** Exportarea datelor (JSON) și ștergerea istoricului cu un singur click.
* 🤖 **AI Ready:** Infrastructură pregătită pentru rezumarea explicațiilor din dosare via AI (în faza BETA).

## 🔒 Privacy by Design (Cum procesăm datele)
JustAlert **NU** este un instrument de spionaj și **NU** construiește o bază de date paralelă cu dosarele cetățenilor.

* **Sistem de Amprentare (HMAC Fingerprinting):** Când API-ul oficial returnează un dosar, nu îi salvăm conținutul. Generăm un *hash criptografic SHA-256* al stării curente a dosarului (ex: termene, stadiu, părți) pe care îl salvăm în baza de date. 
* La următoarea verificare, dacă hash-ul diferă, înseamnă că a apărut o modificare și declanșăm notificarea. Imediat după expedierea emailului, datele în clar ale dosarului sunt șterse din memoria temporară (RAM) a serverului.

## 🚀 Instalare și Configurare (Pentru Developeri)

Dacă vrei să contribui sau să îți rulezi propria instanță JustAlert, urmează acești pași:

### 1. Cerințe de sistem
* PHP >= 8.0
* MySQL / MariaDB
* Composer

### 2. Clonarea și Instalarea
Clonează repository-ul și instalează pachetele necesare (PHPMailer, etc.):
```bash
git clone [https://github.com/justalert/prod.git](https://github.com/justalert/prod.git)
cd prod
composer install
```

### 3. Configurarea Mediului
Copiază fișierul de configurare și completează-l cu datele tale:

```bash
cp .env.example .env
```
Deschide .env și completează accesul la baza de date, serverul SMTP pentru emailuri și setează o cheie puternică pentru JUSTALERT_HMAC_SECRET.

### 4. Baza de Date
Importă structura bazei de date folosind fișierul inclus:

```bash
mysql -u username -p database_name < justalert_schema.sql
```

### 5. Automatizarea (Cronjob)
Pentru a declanșa verificările zilnice ale dosarelor, trebuie să setezi un cronjob pe serverul tău care să apeleze scriptul run_alerts.php.
Exemplu (rulează zilnic la ora 08:00):

```bash
0 8 * * * /usr/bin/php /calea/catre/proiect/run_alerts.php >> /calea/catre/proiect/cron.log 2>&1
```

### 🤝 Contribuții
Orice contribuție, de la corecturi de bug-uri, audituri de securitate, până la funcționalități noi, este binevenită!

Fă un Fork acestui repository.
Creează un branch nou (git checkout -b feature/FunctionalitateNoua).
Fă commit la modificări (git commit -m 'Adaugă o funcționalitate nouă').
Fă push pe branch (git push origin feature/FunctionalitateNoua).
Deschide un Pull Request.

### 📄 Licență
Acest proiect este licențiat sub MIT License. Este complet gratuit pentru utilizare și modificare.
