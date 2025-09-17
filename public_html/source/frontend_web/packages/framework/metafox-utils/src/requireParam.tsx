/**
 * check dependencies requirements
 */
import { get } from 'lodash';

/**
 * @param props
 * @param attrs
 */
export default function requireParam(
  props: Record<string, any>,
  params: string,
  message: string = 'Missing parameters'
): void {
  const missing = params
    .split(',')
    .map(x => x.trim())
    .filter(k => undefined === get(props, k))
    .join(', ');

  if (missing) {
    throw new Error(`${message} "${missing}".`);
  }
}
