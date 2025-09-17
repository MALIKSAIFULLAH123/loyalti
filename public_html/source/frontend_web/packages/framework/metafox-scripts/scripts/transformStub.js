const fs = require('fs');
const chalk = require('chalk');
const path = require('path');
const glob = require('glob');
const { exit } = require('process');

const stripRootPath = str => str.substring(process.cwd().length + 1);

const isValidPackage = str => {
  if (!/^([a-z][\w-]+)\/([a-z][\w-]+)$/.test(str)) {
    console.log(chalk.red('Invalid package name, Example: [company]/[note]'));
    process.exit(1);
  }
};

const transformString = (content, replacements) => {
  Object.keys(replacements).forEach(from => {
    content = content.replaceAll(from, replacements[from]);
  });

  return content;
};

const transformStub = (
  options,
  stubDir,
  stub,
  target,
  appRoot,
  replacements
) => {
  const stubRoot = path.dirname(
    require.resolve('@metafox/scripts/package.json')
  );

  const overwrite = options.overwrite;
  const from = path.resolve(stubRoot, stubDir, stub);
  const to = path.resolve(appRoot, target);

  // skip if stub not found
  if (!fs.existsSync(from)) {
    console.log(chalk.cyan(`Not found ${stripRootPath(from)}`));
    return;
  }

  // skip if target exists
  if (fs.existsSync(to) && !overwrite) {
    console.log(chalk.cyan(`File exists ${stripRootPath(to)}`));
    return;
  }

  // ensure dir exists
  const ensureDir = path.dirname(to);

  fs.mkdirSync(ensureDir, { recursive: true });

  let content = transformString(
    fs.readFileSync(from, { encoding: 'utf-8' }),
    replacements
  );

  console.log(chalk.cyan(`Updated ${chalk.green(stripRootPath(to))}`));

  fs.writeFileSync(to, content);
};

const scanStubs = (stubDir, replacements) => {
  const stubs = {};

  const stubRoot = path.resolve(
    path.dirname(require.resolve('@metafox/scripts/package.json')),
    stubDir
  );

  glob.sync(`${stubRoot}/**/*.stub`).forEach(file => {
    const from = file.substr(stubRoot.length + 1);
    const target = transformString(from, replacements).replace('.stub', '');
    stubs[target] = from;
  });
  return stubs;
};

const transformAllStubs = (options, stubDir, appRoot, stubs, replacements) => {
  Object.keys(stubs).forEach(file => {
    transformStub(options, stubDir, stubs[file], file, appRoot, replacements);
  });
};

module.exports = {
  scanStubs,
  transformStub,
  transformAllStubs,
  transformString,
  stripRootPath,
  isValidPackage
};
