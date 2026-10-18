# Версии и обновления

Источник правды — SemVer в **трёх** местах одним значением. Номер в этот файл не записывай.

| Место | Что править |
|---|---|
| `bs_extended_product_jshop.xml` | `<version>` |
| `src/Extension/BsExtendedProductJshop.php` | `VERSION` |
| `update.xml` | `<version>` и URL ZIP |

`update.xml`:

- `<element>bs_extended_product_jshop</element>` `<type>plugin</type>` `<folder>system</folder>` `<client>site</client>`
- `<targetplatform name="joomla" version="(4|5|6)\." />`
- downloadurl: `.../releases/download/vX.Y.Z/plg_system_bs_extended_product_jshop_vX.Y.Z.zip`

Сервер в манифесте:

`https://raw.githubusercontent.com/andreytver84/plg_system_bs_extended_product_jshop/main/update.xml`

Номер поднимай, когда просят. ZIP и тег заливает человек — в ответе укажи `vX.Y.Z` и имя ZIP.

## Релиз-чеклист

1. Согласовать X.Y.Z (patch = фикс, minor = фича).
2. Заменить версию в xml / VERSION / update.xml (включая downloadurl и тег `vX.Y.Z`).
3. Языки ru+en синхронны.
4. Собрать ZIP **с корнем файлов плагина**, без обёртки папки проекта и без `.cursor/`.
5. GitHub Release tag `vX.Y.Z`, asset как в downloadurl.
