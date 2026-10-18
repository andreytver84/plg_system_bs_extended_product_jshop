# Языковые константы

## Файлы (держать ключи синхронно)

| Файл | Назначение |
|---|---|
| `language/ru-RU/plg_system_bs_extended_product_jshop.ini` | runtime RU |
| `language/en-GB/plg_system_bs_extended_product_jshop.ini` | runtime EN |
| `language/ru-RU/plg_system_bs_extended_product_jshop.sys.ini` | установщик RU |
| `language/en-GB/plg_system_bs_extended_product_jshop.sys.ini` | установщик EN |

Имена без префикса локали (`plg_system_…ini`) — норма Joomla 4+.

## Правила

- UTF-8 без BOM, `\n` переводы строк.
- `KEY="value"`. Ключ: `PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_` + UPPER_SNAKE.
- В XML: `label="…_LABEL"`, `description="…_DESC"`.
- В PHP/tmpl: `Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_…')`.
- `.sys.ini` минимум: имя плагина и `XML_DESCRIPTION`.
- HTML в значениях — только `*_DESC`. Внутри кавычки удваивать: `"Say ""Hello"""`.

## Чеклист новой строки

1. Ключ в RU `.ini` и EN `.ini` (или оба `.sys.ini`).
2. Использование в XML или `Text::_`.
3. Карта ключей совпадает.
4. Не оставлять ключ только в одной локали.
