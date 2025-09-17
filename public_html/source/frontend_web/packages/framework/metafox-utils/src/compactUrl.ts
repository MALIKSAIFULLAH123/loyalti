/**
 * @type: service
 * name: compactUrl
 */
import { isString } from 'lodash';
import qs from 'query-string';

export default function compactUrl(
  url: string,
  data: Record<string, any> | string | undefined
): string {
  if (!url) return;

  data = isString(data) ? qs.parse(data as string, '&', '=') : data;

  if (!data) {
    data = {};
  }

  return url.replace(/(:\w+)/gi, token => {
    return data[token.substr(1)];
  });
}
