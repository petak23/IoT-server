# Komponenta pre zobrazenie favicon-ov

**Inštalácia**

1. nakopírovanie archývu do `app\components`,
2. do `BasePresenter` alebo iného potrebného doplniť do hlavičky `use PeterVojtech` a do tela objektu `use PeterVojtech\MainLayout\Favicon\faviconTrait;`,
3. do config-u doplniť:

```neon
services:
  - PeterVojtech\MainLayout\Favicon\IFaviconControl

```

4. Vygenerovanie faviconov na stránke: https://www.favicon-generator.org/
5. Nakopírovanie súborov pre favikon do adresára `www/favicon`.
6. do hlavného template `@layout.latte` doplniť do hlavičky `{control favicon}`.
