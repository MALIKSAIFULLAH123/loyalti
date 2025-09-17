const fs = require('fs');
const rawDatas = require('./raws');

rawDatas.forEach(item => {
  item.emojis = item.raws.split(/\s+/).filter(Boolean);
  item.raws = undefined;
});

fs.writeFileSync(
  '../src/data/twemoji.json',
  JSON.stringify(rawDatas, null, '  '),
  { encoding: 'utf-8' }
);
