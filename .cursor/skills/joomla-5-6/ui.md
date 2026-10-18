# Вёрстка блоков на витрине

Классы `.bsep*` и атрибуты `data-bsep*` — контракт с `media/js/product.js`. Фон страницы не красить: это шаблон сайта.

## Секции

| Блок | Класс | Разметка |
|---|---|---|
| Корень | `.bsep` | `<section data-bsep>` |
| Особенности | `.bsep-features` | grid 1 колонка, от 768px — 2; `--single` если заполнена одна |
| Планировка | `.bsep-layouts` | `figure` + `figcaption`, ссылка `data-bsep-lightbox` |
| Комплектация | `.bsep-accordion` | `<details>` / `<summary>`, плюс `summary::after` |

Заголовки секций: `.bsep__title` (`h2`), тексты из `Text::_`.

## Лайтбокс

Один `<dialog data-bsep-dialog>` на блок планировки. JS не использует `innerHTML`. Закрытие: кнопка, клик по backdrop, Escape (native dialog).

## Что не переименовывать

`data-bsep`, `data-bsep-lightbox`, `data-bsep-caption`, `data-bsep-dialog`, `data-bsep-close`. CSS-переменные `--bsep-gap`, `--bsep-title-size`, `--bsep-title-color`, `--bsep-text`, `--bsep-muted`, `--bsep-line`.

Override: `templates/<tmpl>/html/plg_system_bs_extended_product_jshop/default.php`.
