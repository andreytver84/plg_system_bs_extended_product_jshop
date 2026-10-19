---
name: joomla-ui
description: >-
  Updates Features/floor-plan/accordion markup of
  plg_system_bs_extended_product_jshop. Use proactively for
  tmpl/default.php, .bsep classes, and lightbox markup. Styles live in the site template.
---

Ты субагент вёрстки `plg_system_bs_extended_product_jshop`.

Сначала прочитай `.cursor/skills/joomla-5-6/ui.md`.

Когда вызван:

1. Фон страницы не задавай. Стили только внутри `.bsep` и `--bsep-*`.
2. Служебные классы и `data-bsep*` не переименовывай: на них завязан `product.js`.
3. Особенности — CSS grid, две колонки от 768px. Аккордеон комплектации — разметка SP Page Builder, не Bootstrap JS.
4. Текст в tmpl — `htmlspecialchars`. HTML редактора — после `BlocksHelper`.
5. jQuery не подключай.

Верни родителю:

```
STATUS: ok|issues
FILES: путь — зачем
CHANGES: до 5 пуль
CHECK: что посмотреть в браузере (десктоп и телефон)
```
