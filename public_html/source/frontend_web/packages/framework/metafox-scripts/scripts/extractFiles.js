/**
 * Handle uploaded package from php backend server
 */
const path = require('path');
const fs = require('fs');
const AdmZip = require('adm-zip');
const child_process = require('child_process');

/**
 * Extract zip archive file
 *
 * @param {String} workingDir - current working dir. Example: /app
 * @param {String} zipFile - zip package file name
 * @param {String} settingFile - get setting file
 * @param {String} envFile - get env file
 *
 */
function extractFiles(workingDir, zipFile, settingFile, envFile) {
  const admZip = new AdmZip(zipFile);
  const zipEntries = admZip.getEntries(); // an array of ZipEntry records

  zipEntries.forEach(zipEntry => {
    const from = zipEntry.entryName;

    if (zipEntry.isDirectory) return;

    if (/frontend\/frontend\.json$/.test(from)) {
      const data = JSON.parse(zipEntry.getData().toString('utf8'));
      packageName = data.name;
    }

    if (!/frontend\/packages/.test(from)) return;

    const strip = from.search('frontend/');

    if (!strip) return;

    const to = path.resolve(workingDir, from.substring(strip + 9));
    const data = zipEntry.getData().toString('utf8');

    const dirname = path.dirname(to);

    if (!fs.existsSync(dirname)) {
      fs.mkdirSync(dirname, { recursive: true });
    }

    fs.writeFileSync(to, data, { encoding: 'utf-8' });
  });

  if (settingFile && fs.existsSync(settingFile)) {
    fs.copyFileSync(settingFile, path.resolve(workingDir, 'app/settings.json'));
  }

  if (envFile && fs.existsSync(envFile)) {
    fs.copyFileSync(envFile, path.resolve(workingDir, 'app/.env'));
  }

  const start = new Date().getTime();

  // could not restart yarn because it's circular dependency on port 3000.
  const child = child_process.spawn('yarn', ['bundle'], {
    cwd: '/app',
    env: process.env,
    stdio: ['ignore', process.stdout, process.stderr]
  });
  child.on('error', function (code) {
    console.error(code);
  });
  child.on('message', function (msg) {
    console.log(message);
  });
  child.on('exit', function (code, signal) {
    if (code) {
    } else {
      const end = new Date().getTime();
      console.log('done in ', (end - start) / 1000, '(sec)');
    }
  });
}

module.exports = extractFiles;
