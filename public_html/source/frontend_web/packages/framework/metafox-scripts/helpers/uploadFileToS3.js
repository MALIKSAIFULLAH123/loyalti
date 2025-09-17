const fs = require('fs');
const AWS = require('aws-sdk');

const uploadFileToS3 = async (
  stream,
  fileName,
  Bucket,
  accessKeyId,
  secretAccessKey
) => {
  // Setting up S3 upload parameters
  const params = {
    Bucket: Bucket,
    Key: fileName, // File name you want to save as in S3
    Body: fs.readFileSync(stream),
    ACL: 'public-read'
  };

  const s3 = new AWS.S3({
    accessKeyId,
    secretAccessKey
  });

  // Uploading files to the bucket
  s3.upload(params, (err, data) => {
    if (err) {
      console.error(`File uploaded error. ${err}`);
    } else {
      console.log(`File uploaded successfully. ${data.Location}`);
    }
  });
};

module.exports = uploadFileToS3;
