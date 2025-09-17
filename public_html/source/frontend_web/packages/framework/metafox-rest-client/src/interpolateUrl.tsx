/**
 * @type: service
 * name: interpolateUrl
 */
import { isString } from 'lodash';
import qs from 'query-string';

export default function interpolateUrl(
  url: string,
  params: Record<string, any> | string | undefined,
  appendQuery?: boolean
): { pathname: string; searchParams: Record<string, any> } {
  const tokens = [];
  const parts = url.split('?');

  params = isString(params) ? qs.parse(params as string, '&', '=') : params;

  const pathname = parts[0].replace(/(:\w+)/gi, token => {
    const key = token.substr(1);
    tokens.push(key);

    return params && params[key] ? params[key] : token;
  });

  parts[1] = parts[1]
    ? parts[1].replace(/(:\w+)/gi, token => {
        const key = token.substr(1);

        return params && params[key] ? params[key] : token;
      })
    : '';

  const query = Object.assign(
    parts[1] ? qs.parse(parts[1], '&', '=') : {},
    params
  );

  tokens.forEach(token => {
    delete query[token];
  });

  return { pathname, searchParams: query };
}
