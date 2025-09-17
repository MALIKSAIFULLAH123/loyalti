const babelBuildPackage = require('../helpers/babelBuildPackage');
const copyPackageFile = require('../helpers/copyPackageFiles');

async function run() {
  const packageDir = process.cwd();

  babelBuildPackage(packageDir).catch(function (err) {
    console.error(err);
    process.exit(1);
  });

  copyPackageFile(packageDir).catch(function (err) {
    console.error(err);
    process.exit(1);
  });
}

run().catch(function (err) {
  console.error(err);
  process.exit(1);
});
