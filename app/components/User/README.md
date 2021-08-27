# Komponenta: Fotogaléria k článku

*Vytvorenie fotogalérie na stránke článku vo vue.js.*

**Inštalácia**
1. nakopírovanie archývu do `app\components`,
2. do `app\AdminModule\presenters\ArticlePresenter` doplniť `use PeterVojtech\Clanky\Fotogalery\fotogaleryTrait;`,
3. do `app\FrontModule\presenters\ClankyPresenter` doplniť `use PeterVojtech\Clanky\Fotogalery\fotogaleryTrait;`,
4. do `app\config\komponenty.neon` doplniť:
```neon
parameters:
  komponenty:
#...
    fotogalery:
      nazov: 'Fotogaléria k článku'
      jedinecna: FALSE  # Ci je mozne pridat len raz k clanku
      fa_ikonka: 'images'
      parametre: 
        id_hlavne_menu: 
          nazov: 'Id článku'
        template:
          nazov: 'Názov vzhľadu'
#          hodnoty: 
#            default: 'Základný'
#            bez_avatara: 'Len odkaz bez avatara'
#            to_foto_galery: 'Odkaz na fotogalériu'
#            to_article: 'Odkaz na článok'
#            foto_album: 'Odkaz pre fotogalériu'
#...

# Component Clanky\Fotogalery
  - PeterVojtech\Clanky\Fotogalery\IFotogaleryControl
```