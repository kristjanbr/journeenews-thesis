# JourneeNews (**NEvarna** različica)

### Aplikacija za diplomsko nalogo, FRI

#### Pomembno:

V sistemske spremenljivke je potrebno dodati podatke, kateri vsebujejo API ključ za avtentikacijo s sitemom NextERP ter podatke za prijavo v novo podatkovno bazo. To lahko dosežemo tako, da npr. ustvarimo datoteko _.env_ v korenskem imeniku po predlogi:

```text
ERP_URL=url_sistema_ERPNext
ERP_API_KEY=api_kljuc_NextERP
ERP_API_SECRET=skrivnost_api_NextERP

DB_HOST=db
DB_USER=up_ime
DB_PASSWD=up_geslo
DB_SCHEMA=ime_pz
```
