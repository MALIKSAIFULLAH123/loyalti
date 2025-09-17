# @metafox/emoji

## Update EmojiPedia

/// paste content to ./src/components/EmojiData.json

Visit following link

- https://emojipedia.org/people/
- https://emojipedia.org/nature/
- https://emojipedia.org/food-drink/
- https://emojipedia.org/activity/
- https://emojipedia.org/travel-places/
- https://emojipedia.org/objects/
- https://emojipedia.org/symbols/
- https://emojipedia.org/flags/

Copy to console log then run

```js
(() => {
  const h1 = document.querySelector('h1');

  let dict = {
    label: h1.lastChild.textContent.trim(),
    data: h1.firstElementChild.innerText,
    emojis: []
  };

  document.querySelectorAll('.emoji-list a').forEach(node => {
    dict.emojis.push(node.firstChild.innerText);
  });
  document.write(JSON.stringify(dict, null, '  '));
})();
```
