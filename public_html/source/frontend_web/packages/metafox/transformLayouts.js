const glob = require('glob');
const fs = require('fs');

function clone(obj) {
  return JSON.parse(JSON.stringify(obj));
}

glob.sync('**/layouts.json', {
  cwd: process.cwd(),
  realpath: true
}).forEach((file) => {
  const data = JSON.parse(fs.readFileSync(file, { encoding: 'utf8' }));

  Object.values(data).forEach((layouts) => {
    if (!layouts.large) {
      return;
    }

    if (!layouts.small) {
      layouts.small = clone(layouts.large);
      layouts.small.templateName = 'mobile';
    }

    if (layouts.xlarge && layouts.large) {
      delete layouts.xlarge;
    }
  });
  
  fs.writeFileSync(file, JSON.stringify(data, null, '  '), { encoding: 'utf8' });

});