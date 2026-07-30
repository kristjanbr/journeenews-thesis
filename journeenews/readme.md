# JourneeNews (**NEvarna** različica)

### Aplikacija za diplomsko nalogo, FRI

#### Pomembno:

V direktorij static je potrebno dodati datoteko _secrets.php_, katera vsebuje API ključ za avtentikacijo s sitemom NextERP ter podatke za prijavo v novo podatkovno bazo po predlogi:

```php
<?php
define("ERP_URL", "url_sistema_ERPNext");
define("ERP_API_KEY", "api_kljuc_NextERP");
define("ERP_API_SECRET", "skrivnost_api_NextERP");

define("DB_HOST", "localhost");
define("DB_USER", "up_ime");
define("DB_PASSWD", "up_geslo");
define("DB_SCHEMA", "ime_pz");
?>
```
