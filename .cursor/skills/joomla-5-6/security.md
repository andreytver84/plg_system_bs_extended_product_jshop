# Безопасность плагина

Данные блоков — stored XSS: админ пишет HTML, гости читают карточку. Санитайз на записи **и** на чтении.

## Обязательно

```php
defined('_JEXEC') or die;
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
```

В namespaced-файлах: `\defined('_JEXEC') or die;`.

Сохранение только `isClient('administrator')`. Колонка `custom_blocks` — MEDIUMTEXT JSON.

## Вход

`project_blocks` содержит HTML редактора. Фильтр `array`/`STRING` и `Form::filter()` его вырезают или обнуляют, поэтому POST берут RAW-массивом **и сразу** `BlocksHelper::sanitize()`. Если ключа нет в POST или это не массив — колонку не трогать, пустым JSON не затирать.

В БД — только результат sanitize. `eval`, `unserialize`, `content.prepare` по этим полям запрещены. Повторная запись — в `onAfterSaveProductEnd`, после `load()+store()` картинок.

## HTML редактора

`InputFilter::getInstance([], [], 1, 1)` + `clean(..., 'HTML')`. Скрипты, iframe, object, on-атрибуты отбрасываются. Вывод колонок — этот HTML, не `htmlspecialchars` целиком (иначе сломается вёрстка).

Текст заголовков/подписей: `clean(..., 'STRING')`, затем `htmlspecialchars`. `{name}` подставлять после экранирования имени товара.

## Картинки

Только относительный путь `images/…` или `media/…`. Отрезать `#joomlaImage://…`. Запрещены `..`, схемы, `//`, `javascript:`, `data:`, абсолютные диски, SVG. На витрине `src` = `Uri::root() . $path`.

## SQL

Query/bind. `product_id` — INTEGER. Имя колонки — константа `custom_blocks`, не из запроса. `ALTER TABLE` только если `getTableColumns` не видит колонку. При удалении плагина колонку не дропать.

## JS

Без `innerHTML`. Лайтбокс: `textContent`, `setAttribute('src')`, проверка same-origin. Не вставлять пользовательский HTML в DOM через JS.
