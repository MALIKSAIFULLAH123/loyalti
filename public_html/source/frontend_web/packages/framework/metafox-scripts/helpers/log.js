const chalk = require('chalk');
const os = require('os');
const {
  map,
  isPlainObject,
  isString,
  isNumber,
  isBoolean,
  isArray
} = require('lodash');

function info() {
  const format = [chalk.cyan, chalk.green, chalk.blue];

  const message = map(arguments, (x, index) => {
    if (isPlainObject(x)) {
      x = JSON.stringify(x, null, '  ');
    }
    return format[index % 3](x);
  }).join(' ');

  console.log(message);
}

function error() {
  const format = [chalk.redBright, chalk.green, chalk.blue];

  const message = map(arguments, (x, index) => {
    if (isPlainObject(x)) {
      x = JSON.stringify(x, null, '  ');
    }
    return format[index % 3](x);
  }).join(' ');

  console.log(message);
}

function format(x) {
  if (!x) {
    return '';
  }

  if (isArray(x)) {
    return `[${x.join(', ')}]`;
  }

  if (isPlainObject(x)) {
    return JSON.stringify(x);
  }
  return x;
}

function dump(data) {
  Object.keys(data).forEach(name => {
    info(`${name}:`, format(data[name]));
  });
}

function dashLines() {
  console.log(chalk.green('-'.repeat(60)));
}

function heading(message) {
  console.log(os.EOL);
  console.log(chalk.blueBright('*'.repeat(60)));
  console.log(chalk.blueBright(message));
  console.log(chalk.blueBright('*'.repeat(60)));
}

module.exports = {
  info,
  error,
  dashLines,
  heading,
  dump
};
