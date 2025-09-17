const fs = require('fs');
const chalk = require('chalk');

function normalizeName(name) {
  return name.replace(/[^\w\.-@]/g, '');
}

function normalizeValue(value) {
  const ret = value
    .replace(/(\*\/$|\s*$)/g, '')
    .trim()
    .replace(/(^"|"$)/g, '');
  switch (ret) {
    case 'true':
      return true;
    case 'false':
      return false;
    case '0':
      return 0;
    case '1':
      return 1;
    default:
      return ret;
  }
}

function getCommentedInfo(filename) {
  const lines = fs.readFileSync(filename, { encoding: 'utf8' }).split('\n');

  const candidates = [];
  let found = false;
  for (let i = 0; i < lines.length; ++i) {
    if (!lines[i]) {
      // avoid empty line
    } else if (lines[i].startsWith('/*')) {
      found = true;
      candidates.push(lines[i]);
      if (/\*\/\s*$/.test(lines[i])) break;
    } else if (found) {
      candidates.push(lines[i]);
      if (/\*\/\s*$/.test(lines[i])) break;
    } else {
      break;
    }
  }
  if (!candidates.length) return false;

  const result = {};

  for (let i = 0; i < candidates.length; i++) {
    const line = candidates[i];
    const position = line.indexOf(':');
    if (position < 1) continue;

    const key = normalizeName(line.substr(0, position));
    const value = normalizeValue(line.substr(position + 1));

    result[key] = value;
  }
  return result;
}

module.exports = getCommentedInfo;
