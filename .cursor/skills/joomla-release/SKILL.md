---
name: joomla-release
description: >-
  Bumps plg_system_bs_extended_product_jshop version, syncs update.xml download URL,
  installer description, and release checklist. Use when the user asks for a new version,
  changelog, update server, ZIP, GitHub release, or XML description update.
---

# Релиз plg_system_bs_extended_product_jshop

1. Прочитай `.cursor/skills/joomla-5-6/versions.md`. Если меняется текст установщика — ещё `.cursor/skills/joomla-5-6/description.md`.
2. Если правок много — субагент `joomla-release`.
3. Не поднимай версию без синхронного downloadurl. Номер в `versions.md` не записывай. `<folder>system</folder>`, `<client>site</client>` и `targetplatform` `(4|5|6)\.` оставь как в `update.xml`.
4. В чат: старая → новая версия, список файлов, что осталось сделать руками (tag/ZIP).
