# Komponenta pre zobrazenie google analytics

**Inštalácia**

1. nakopírovanie archývu do `app\components`,
2. do `...\BasePresenter` doplniť `use PeterVojtech\MainLayout\GoogleAnalytics\googleAnalyticsTrait;`,
3. do `app\config\services.neon` doplniť:

```neon
parameters:
  - ua_code: UA_123456
services:
  - PeterVojtech\MainLayout\GoogleAnalytics\GoogleAnalyticsControl( %ua_code% )

```

4. do hlavného template `@layout.latte` doplniť na koniec `{control googleAnalytics}`.
