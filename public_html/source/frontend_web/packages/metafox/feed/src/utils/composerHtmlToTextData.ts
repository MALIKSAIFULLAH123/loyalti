const htmlToText = html => {
  const mentionReg =
    /<a href="@mention\/(\w+)\/(\d+)" data-lexical-mention="true">([^<]+)<\/a>/gm;

  return html
    .replace(mentionReg, '[$1=$2]$3[/$1]')
    .replaceAll('<br>', '\n')
    .replace(/&nbsp;/gm, ' ')
    .replace('&amp;', '&')
    .replace(/(<([^>]+)>)/gi, '');
};

export default htmlToText;
