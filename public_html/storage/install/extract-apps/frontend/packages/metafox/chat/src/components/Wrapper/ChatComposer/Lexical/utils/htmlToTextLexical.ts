function decodeHtml(html) {
  const txt = document.createElement('textarea');
  txt.innerHTML = html;

  return txt.value;
}

function htmlToTextLexical(text): string {
  return decodeHtml(
    text
      .replaceAll('<br>', '\n')
      .replace(/&nbsp;/gm, ' ')
      .replace('&amp;', '&')
      .replace(/(<([^>]+)>)/gi, '')
  );
}

export default htmlToTextLexical;
