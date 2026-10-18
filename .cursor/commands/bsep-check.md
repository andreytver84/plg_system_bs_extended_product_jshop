---
description: Сверить языки, версию, санитайз и вывод плагина
---

Проверь плагин перед сдачей, код без явной поломки не переписывай.

1. Ключи `language/ru-RU` и `language/en-GB`: отдельно `.ini` и `.sys.ini`.
2. Один SemVer в `bs_extended_product_jshop.xml`, `BsExtendedProductJshop::VERSION`, `update.xml` и downloadurl.
3. Сохранение: RAW → `Form::filter` → `BlocksHelper::sanitize`. Текст на витрине — `htmlspecialchars`.
4. SQL — bind. Колонка `custom_blocks` после `getTableColumns`. JS без `innerHTML`.

Ответ — по-русски, коротко: что совпало и что починить. Версию сам не поднимай.
