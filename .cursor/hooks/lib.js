const fs = require('fs');
const path = require('path');

function readInput() {
  try {
    const raw = fs.readFileSync(0, 'utf8').trim();
    return raw ? JSON.parse(raw) : {};
  } catch {
    return {};
  }
}

function writeOutput(obj) {
  process.stdout.write(JSON.stringify(obj));
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function readRepoFile(rel) {
  return fs.readFileSync(path.join(repoRoot(), rel), 'utf8');
}

function versions() {
  const xml = readRepoFile('bs_extended_product_jshop.xml');
  const php = readRepoFile('src/Extension/BsExtendedProductJshop.php');
  const upd = readRepoFile('update.xml');
  const manifest = (xml.match(/<version>([^<]+)<\/version>/) || [])[1] || '';
  const pluginVer = (php.match(/VERSION\s*=\s*'([^']+)'/) || [])[1] || '';
  const update = (upd.match(/<version>([^<]+)<\/version>/) || [])[1] || '';
  const url = (upd.match(/<downloadurl[^>]*>([^<]+)<\/downloadurl>/) || [])[1] || '';
  const urlOk = !update || (url.includes('v' + update) && url.includes(update));
  return {
    manifest,
    pluginVer,
    update,
    url,
    urlOk,
    synced: Boolean(manifest && manifest === pluginVer && pluginVer === update && urlOk),
  };
}

function iniKeys(rel) {
  const keys = [];
  for (const line of readRepoFile(rel).split(/\r?\n/)) {
    const match = line.match(/^([A-Z0-9_]+)=/);
    if (match) {
      keys.push(match[1]);
    }
  }
  return keys;
}

function langDrift() {
  const pairs = [
    [
      'language/ru-RU/plg_system_bs_extended_product_jshop.ini',
      'language/en-GB/plg_system_bs_extended_product_jshop.ini',
    ],
    [
      'language/ru-RU/plg_system_bs_extended_product_jshop.sys.ini',
      'language/en-GB/plg_system_bs_extended_product_jshop.sys.ini',
    ],
  ];
  const missing = [];

  for (const [ruRel, enRel] of pairs) {
    const ru = new Set(iniKeys(ruRel));
    const en = new Set(iniKeys(enRel));
    for (const key of ru) {
      if (!en.has(key)) {
        missing.push('en без ' + key);
      }
    }
    for (const key of en) {
      if (!ru.has(key)) {
        missing.push('ru без ' + key);
      }
    }
  }

  return missing;
}

function securityFlags() {
  const files = [
    'src/Extension/BsExtendedProductJshop.php',
    'src/Helper/BlocksHelper.php',
    'tmpl/default.php',
    'script.php',
    'services/provider.php',
    'media/js/product.js',
  ];
  const flags = [];

  for (const rel of files) {
    let text = '';
    try {
      text = readRepoFile(rel);
    } catch {
      continue;
    }

    if (/\bJFactory\b|\bJText::|\bJHtml::/.test(text)) {
      flags.push(rel + ': legacy JFactory/JText/JHtml');
    }
    if (/innerHTML/.test(text)) {
      flags.push(rel + ': innerHTML');
    }
    if (/content\.prepare/.test(text)) {
      flags.push(rel + ': content.prepare');
    }
    if (/unserialize\(/.test(text)) {
      flags.push(rel + ': unserialize');
    }
    if (rel.endsWith('.php') && !/_JEXEC/.test(text)) {
      flags.push(rel + ': нет _JEXEC');
    }
  }

  try {
    const ext = readRepoFile('src/Extension/BsExtendedProductJshop.php');
    const hasRaw = /get\('project_blocks',\s*\[\],\s*'RAW'\)/.test(ext);
    const hasSanitize = /BlocksHelper::sanitize/.test(ext);
    if (hasRaw && !hasSanitize) {
      flags.push('src/Extension/BsExtendedProductJshop.php: RAW без BlocksHelper::sanitize');
    }
    if (!/isClient\('administrator'\)/.test(ext)) {
      flags.push('src/Extension/BsExtendedProductJshop.php: сохранение без проверки administrator');
    }
  } catch {
    // ignore
  }

  return flags;
}

module.exports = {
  readInput,
  writeOutput,
  repoRoot,
  readRepoFile,
  versions,
  langDrift,
  securityFlags,
};
