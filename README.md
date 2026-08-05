This is my 2025 final year project as I study in TARUMT. This project was proposed to allow people of all ages to utilize AI summaries features in online forums to understand content better in shorter format. 

This project is not intended to release publicly for actual production runs (for now).

Setup:
Install MariaDB, then create FYP_Web (or can also use XAMPP)<br>
Use DBeaver management tool to connect to the database "FYP_Web" and import tables.sql<br>
Go to php.ini and enable mysqli and pdo_mysql extension first (otherwise database cannot connect)<br>
In same file, in CURL and OpenSSL section, define the certificate file path (download from https://curl.se/docs/sslcerts.html first)<br>
Run using "php -S localhost:8000" in cmd<br>
