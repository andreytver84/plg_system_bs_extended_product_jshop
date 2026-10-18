---
description: Проверить XSS, HTML-фильтр, пути медиа и SQL плагина
---

Проведи проверку безопасности `plg_system_bs_extended_product_jshop`.

Прочитай только `.cursor/skills/joomla-5-6/security.md`. При необходимости вызови субагента `joomla-security`.

Ищи RAW без sanitize, `innerHTML`, SVG/`..` в путях, `content.prepare` на блоках, SQL без bind, вывод текста без `htmlspecialchars`, сохранение вне administrator.

Ответ — по-русски: дыры с файлом и строкой, что уже закрыто. Код правь только если нашёл реальную дыру.
