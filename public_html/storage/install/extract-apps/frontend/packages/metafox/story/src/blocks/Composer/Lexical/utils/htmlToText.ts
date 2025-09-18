const htmlToText = html => {
  const data = html
    .replaceAll('<br>', '\n')
    .replace(/&nbsp;/gm, ' ')
    .replace('&amp;', '&')
    .replace(/(<([^>]+)>)/gi, '');

  return data;
};

export default htmlToText;
