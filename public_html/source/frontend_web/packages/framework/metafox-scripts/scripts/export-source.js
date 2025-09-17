const { execSync } = require('child_process');
const fs = require('fs');

function exportSource() {
  const currentCwd = process.cwd();

  console.log(`Export source located in "${currentCwd}"`);

  const outputDir = process.env.MFOX_OUTPUT_DIR;

  if (!outputDir) {
    console.log('skip output dir');
    return;
  }

  if (!fs.existsSync(`${outputDir}/source`)) {
    fs.mkdirSync(`${outputDir}/source`);
  }

  const output = `${outputDir}/source/frontend.zip`;

  const excludes = [
    '/.ignore-developer',
    '/.ignore-production',
    '/Dockerfile',
    '/coverage/*',
    '/node_modules*',
    '/thumbnails/*',
    '/.vscode/*',
    '/*.log',
    '/.git*'
  ]
    .map(name => `-x "${name}"`)
    .join(' ');

  const command = `zip -r -9 ${output} ./ ${excludes}`;

  execSync(command, { encoding: 'utf-8', timeout: 30000 });
}

module.exports = exportSource;
