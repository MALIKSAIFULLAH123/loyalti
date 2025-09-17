import ReactHtmlParser from 'react-html-parser';
import { isValidElement } from 'react';

export function decodeHtml(html): string {
  const txt = document.createElement('textarea');

  txt.innerHTML = html;

  return txt.value;
}

export default function htmlToText(htmlText: string): string {
  if (!htmlText) return '';

  const parser = ReactHtmlParser(htmlText);

  const results = [];

  parser.forEach(item => {
    if (isValidElement(item)) {
      results.push(item.props?.title);
    }

    if (typeof item === 'string') {
      results.push(item);
    }
  });

  return results.join('');
}
