---
description: Поднять версию плагина и синхронизировать update.xml
---

Подними версию `plg_system_bs_extended_product_jshop`.

Прочитай только `.cursor/skills/joomla-5-6/versions.md`. Номер бери из трёх файлов, не из справочника.

Если пользователь не назвал номер: patch — исправление, minor — новая возможность. Запиши одно значение в манифест, `BsExtendedProductJshop::VERSION`, `update.xml` и downloadurl (`vX.Y.Z` и `plg_system_bs_extended_product_jshop_vX.Y.Z.zip`).

`<folder>system</folder>`, `<client>site</client>` и `targetplatform` `(4|5|6)\.` не меняй. Коммит не делай. Ответ — по-русски: старая → новая версия и что залить руками (тег и ZIP).
