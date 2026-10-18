---
name: joomla-5-6
description: Joomla 5/6 system plugin plg_system_bs_extended_product_jshop for JoomShopping 5 product blocks (features, floor plan, accordion). Use when changing plugin structure, product card fields, frontend blocks, languages, security, or version.
---

# Joomla 5/6 для этого репозитория

Не загружай все справочники. Прочитай **один** файл по задаче, затем правь код.

| Задача | Файл | Субагент |
|---|---|---|
| Структура плагина, манифест, файлы | [structure.md](structure.md) | `joomla-structure` |
| Поле/блок в карточке товара | [develop.md](develop.md) | `joomla-structure` |
| Вёрстка Особенности / Планировка / аккордеон | [ui.md](ui.md) | `joomla-ui` |
| Языковые константы ru/en | [language.md](language.md) | `joomla-lang` |
| XSS, JEXEC, HTML-фильтр, пути, SQL | [security.md](security.md) | `joomla-security` |
| Версия, update.xml, релиз | [versions.md](versions.md) | `joomla-release` |
| HTML description в XML/установщике | [description.md](description.md) | `joomla-release` |

## Порядок

1. Определи тип задачи по таблице.
2. Если нужно больше 2 файлов или сверка 4 языковых — **делегируй субагенту**. В этот чат верни краткое резюме.
3. Не цитируй справочник пользователю. Не вставляй целые `.ini`/`.xml`.
4. Не раздувай DI дальше provider (Application + Database). Санитайз — `BlocksHelper`.

## Этот проект

- Элемент: `bs_extended_product_jshop`, группа `system`. Зависимость: JoomShopping 5.
- Языки: `ru-RU`, `en-GB`. Префикс ключей: `PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_`.
- Совместимость: Joomla 4/5/6. В `update.xml`: `<folder>system</folder>`, `<client>site</client>`, `targetplatform` `(4|5|6)\.`.
- Версию держать одинаковой в манифесте, `BsExtendedProductJshop::VERSION`, `update.xml`.
