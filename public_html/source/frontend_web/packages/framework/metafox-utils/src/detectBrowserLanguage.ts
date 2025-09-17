// detect navigator language in support language list.
// @link https://caniuse.com/?search=navigator.language

import { intersection, isString } from 'lodash';

// @link https://caniuse.com/?search=navigator.languages
export default function detectBrowserLanguage(supports: object) {
  const keys: string[] = Object.keys(supports ?? { en: '1.0' });

  if (keys.length < 2) {
    return keys.pop();
  }

  let prefer = navigator?.languages || navigator?.language;

  if (isString(prefer)) {
    prefer = prefer.split(',');
  }

  prefer = intersection(prefer, Object.keys(supports ?? { en: '1.0' }));

  if (prefer) {
    return (prefer as string[]).shift();
  }
}
