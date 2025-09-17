const axios = require('axios');
const jsdom = require('jsdom');
const { JSDOM } = jsdom;

const urls = [
  'https://emojipedia.org/people/',
  'https://emojipedia.org/nature/',
  'https://emojipedia.org/food-drink/',
  'https://emojipedia.org/activity/',
  'https://emojipedia.org/travel-places/',
  'https://emojipedia.org/objects/',
  'https://emojipedia.org/symbols/',
  'https://emojipedia.org/flags/'
];
async function crawUrl(vgmUrl) {
  return axios.get(vgmUrl).then(response => {
    const dom = new JSDOM(response.data);
    const h1 = dom.window.document.querySelector('h1');

    const label = h1.lastChild.textContent.trim();
    const data = h1.firstElementChild.innerHTML.toString();

    const items = dom.window.document.querySelectorAll('ul.emoji-list a');

    const emojis = [];
    items.forEach(node => {
      emojis.push({
        url: 'https://emojipedia.org' + node.href.toString(),
        unicode: node.firstChild.innerHTML.toString(),
        label: node.lastChild.text
      });
    });

    const dict = {
      label,
      data,
      emojis
    };

    return dict;
  });
}

async function jsonFormat() {
  await Promise.all(urls.map(crawUrl)).then(collections =>
    console.log(JSON.stringify(collections, null, '  '))
  );
}

async function htmlFormat() {
  await Promise.all(urls.map(crawUrl)).then(collections => {
    collections.forEach('data');
  });
}

async function buildEmoji({ output }) {
  if (output === 'html') {
    await htmlFormat();
  } else {
    await jsonFormat();
  }
}

module.exports = buildEmoji;
