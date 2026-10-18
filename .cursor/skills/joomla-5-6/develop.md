# Как развивать плагин

Один сценарий за раз. Версию не меняй, пока пользователь не попросил.

## Поле в карточке товара

1. `<field>` в `forms/project_blocks.xml`. `label`/`description` — ключи `PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_*`.
2. Тот же ключ в `language/ru-RU/*.ini` и `language/en-GB/*.ini`. В `.sys.ini` только имя и `XML_DESCRIPTION`.
3. Добавь ключ в `BlocksHelper::sanitize` и вывод в `tmpl/default.php`.
4. Редактор: `filter="safehtml"`, `hide="readmore,pagebreak,module,moduleposition"`. Текст: `filter="string"`. Медиа: `types="images"`.
5. Пустой блок на витрине не выводить.

## Новый визуальный блок

Цепочка одна:

1. Поля формы + языки.
2. Санитайз в `BlocksHelper` (HTML / text / mediaPath).
3. Разметка в `tmpl/default.php` с экранированием.
4. Стили `.bsep-*`. JS — только если нельзя на HTML (лайтбокс уже есть).
5. Класс/data-атрибут не ломать: контракт [ui.md](ui.md).

Три блока по умолчанию: Особенности (две колонки editor), Планировка (subform image+caption), Комплектация (subform title+textarea).

## Параметр плагина (позиция вывода)

Поле в `<config>` манифеста. Значения `cmd`: `description`, `after_description`, `after_buttons`, `end`, `none`. Чтение: `$this->params->get('position', 'description')`.

## После правки

- Ключи ru и en совпадают.
- RAW POST не попадает в JSON без sanitize.
- Четыре? Нет: три номера версии совпадают, если просили поднять версию.
