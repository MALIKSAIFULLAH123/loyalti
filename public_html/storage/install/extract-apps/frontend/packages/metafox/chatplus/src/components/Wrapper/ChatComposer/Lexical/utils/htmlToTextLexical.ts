const mentionReg = /<a href="(\w+)" data-lexical-mention="true">([^<]+)<\/a>/gm;

function decodeHtml(html) {
  const txt = document.createElement('textarea');
  txt.innerHTML = html;

  return txt.value;
}

function htmlToTextLexical(text): string {
  return decodeHtml(
    text
      .replace(mentionReg, (match, $1, $2) => {
        if (/all|here/g.test($1)) {
          return `[mention=${$1}]${$2}[/mention]`;
        }

        return `[username=${$1}]${$2}[/username]`;
      })
      .replaceAll('<br>', '\n')
      .replace(/&nbsp;/gm, ' ')
      .replace('&amp;', '&')
      .replace(/(<([^>]+)>)/gi, '')
  );
}

export default htmlToTextLexical;
