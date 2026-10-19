const { readInput, writeOutput } = require('./lib.js');

const input = readInput();
const toolInput = input.tool_input || {};
const filePath = String(toolInput.path || toolInput.file_path || '');
const norm = filePath.replace(/\\/g, '/').toLowerCase();

const hints = [];

if (/(^|\/)language\/.+\.ini$/.test(norm)) {
  hints.push('Языки: те же ключи в ru-RU и en-GB; .ini ≠ .sys.ini. См. .cursor/skills/joomla-5-6/language.md');
}

if (/bs_extended_product_jshop\.xml$/.test(norm) || /(^|\/)update\.xml$/.test(norm)) {
  hints.push('XML: версия = BsExtendedProductJshop::VERSION = update.xml + downloadurl. update.xml: folder=system, client=site, targetplatform (4|5|6)\\. Description без <script>.');
}

if (
  /bsextendedproductjshop\.php$/.test(norm) ||
  /blockshelper\.php$/.test(norm) ||
  /(^|\/)tmpl\/.+\.php$/.test(norm) ||
  /(^|\/)script\.php$/.test(norm)
) {
  hints.push('PHP: _JEXEC, htmlspecialchars ENT_QUOTES UTF-8, SQL bind. RAW-массив project_blocks сразу BlocksHelper::sanitize, пустым JSON колонку не затирать. Картинки без SVG/.. . Сохранение только administrator. Финальная запись onAfterSaveProductEnd.');
}

if (/product\.css$/.test(norm)) {
  hints.push('Вёрстка: .bsep и стили шаблона. Аккордеон комплектации — разметка SP Page Builder. Контракт: .cursor/skills/joomla-5-6/ui.md');
}

if (/(^|\/)media\/js\/product\.js$/.test(norm)) {
  hints.push('JS: без innerHTML, лайтбокс same-origin, textContent / setAttribute.');
}

if (hints.length) {
  writeOutput({ additional_context: hints.join(' ') });
} else {
  writeOutput({});
}
