const { execSync } = require('child_process');
const paths = require('../config/paths');
const uploadFileToS3 = require('../helpers/uploadFileToS3');

async function publishSetupWizard() {
  const { appBuild } = paths;

  const stream = `${appBuild}/bundle.zip`;

  execSync(`rm -f ${stream}`);

  const output = execSync(`(cd ${appBuild} && zip -r ${stream} ./)`, {
    encoding: 'utf-8',
    timeout: 30000
  });

  const { AWS_S3_KEY, AWS_S3_SECRET } = process.env;

  const Bucket = 'metafox-build-service';

  if (!AWS_S3_KEY || !AWS_S3_SECRET) {
    throw new Error('Missing env AWS_S3_KEY, AWS_S3_SECRET');
  }

  await uploadFileToS3(
    stream,
    'metafox-installation-5.0.1.zip',
    Bucket,
    AWS_S3_KEY,
    AWS_S3_SECRET
  );

  console.log(output);
}

module.exports = publishSetupWizard;
