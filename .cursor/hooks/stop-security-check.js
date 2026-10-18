const { writeOutput, securityFlags } = require('./lib.js');

try {
  const flags = securityFlags();

  if (!flags.length) {
    writeOutput({});
    process.exit(0);
  }

  writeOutput({
    followup_message:
      'Проверка безопасности: ' +
      flags.slice(0, 8).join('; ') +
      '. См. .cursor/skills/joomla-5-6/security.md.',
  });
} catch {
  writeOutput({});
}
