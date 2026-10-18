# Description плагина (установщик)

Два слоя, не смешивать:

1. **`<description>` в `bs_extended_product_jshop.xml`** — ключ `PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_XML_DESCRIPTION` (не сырой HTML, пока не попросили CDATA).
2. **Тот же ключ в обоих `.sys.ini`** — текст в списке расширений.

Инструкции полей карточки — `forms/project_blocks.xml` через `.ini`.

## HTML в description

- Без `<script>`, `<iframe>`, внешних stylesheet.
- Суть: три блока в карточке товара JoomShopping 5, правка в админке товара.
- Не дублируй весь README.

## Когда править

| Изменение | XML description | sys.ini |
|---|---|---|
| Новая возможность, видимая админу | да | да |
| Внутренний фикс / санитайз | нет | нет |
| Смена позиции вывода / зависимости | да | да |

Автор: Andrey Uvikov, `order@bestsite-studio.ru`, `https://bestsite-tver.ru`. Не затирать.
