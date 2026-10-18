# Структура плагина Joomla 5/6

Актуально для **этого** репозитория. Не описывай CMS целиком.

## Текущее дерево

```
bs_extended_product_jshop.xml   манифест plugin/system
script.php                      колонка custom_blocks, enable
services/provider.php           Application + Database
src/Extension/BsExtendedProductJshop.php
src/Helper/BlocksHelper.php     HTML/текст/пути/JSON
forms/project_blocks.xml        поля в карточке товара
tmpl/default.php                витрина
media/css/product.css
media/js/product.js             лайтбокс без innerHTML
language/ru-RU|en-GB/           .ini и .sys.ini
update.xml
```

Манифест: `type="plugin" group="system" method="upgrade"`.
Namespace: `Joomla\Plugin\System\BsExtendedProductJshop`.

## Совместимость 4, 5 и 6

- PHP 8.1, API `Joomla\CMS\*`. Запрещены `JFactory` / `JText` / `JHtml` / `JRequest`.
- Bootstrap шаблона не требовать: аккордеон — `<details>`. jQuery не добавлять.
- Ассеты: WebAssetManager, `version` = `BsExtendedProductJshop::VERSION`.

## События JoomShopping 5

| Событие | Зачем |
|---|---|
| `onBeforeDisplayEditProductView` | HTML в `$view->{'plugin_template_description_'.$lang}` первой вкладки, обёртка `<tr><td colspan="2">` |
| `onBeforeDisplaySaveProduct` | санитизированный JSON из RAW `project_blocks`, без затирания если ключа нет |
| `onAfterSaveProduct` | UPDATE `custom_blocks`, pending не сбрасывать |
| `onAfterSaveProductEnd` | финальный UPDATE после `load()+store()` картинок |
| `onBeforeDisplayProductView` | `$view->project_blocks`, HTML в выбранную позицию |

Override layout: `templates/<tmpl>/html/plg_system_bs_extended_product_jshop/default.php`.

## Куда класть изменение

| Изменение | Файлы |
|---|---|
| Поле в карточке | `forms/project_blocks.xml` + оба `.ini` + `BlocksHelper::sanitize` |
| Вывод блока | `tmpl/default.php`, при стиле — `media/css/product.css` |
| Параметр плагина | `bs_extended_product_jshop.xml` + оба `.ini` |
| Санитайз | только `BlocksHelper` |
