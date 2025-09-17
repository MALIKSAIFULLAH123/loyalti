import { get, isPlainObject, isString } from 'lodash';

/**
 * Get nearest bigger image size and keep ratio.
 *
 * @param {Object} img - Get image src path. Example: {"300": "/bgs/01/a1_300.png", "origin": "..."}
 * @param {String} prefers - Prefer size. Example "300,120"
 * @param defaultValue - Default value should be loaded from settings.
 * @returns
 */
export default function getImageSrc(
  img?: Record<string, string> | string,
  prefers: string = '500', // etc: 500,1024, split by comma.
  defaultValue?: string
): string | undefined {
  if (!img) return defaultValue;

  if (isString(img)) return img;

  if (!isPlainObject(img)) return defaultValue;

  if (!prefers) prefers = '500';

  const expected = prefers
    .toString()
    .split(',')
    .map(x => x.trim());

  const size = expected.find(size => img[size]);

  if (size) {
    return img[size];
  }

  const target = parseInt(expected[0], 10);
  const maps = Object.keys(img).sort((a: string, b: string) => {
    return parseInt(a, 10) - parseInt(b, 10);
  });

  const squareMaps = maps.filter(x => x.includes('x'));

  let found;

  if (expected[0].includes('x')) {
    found = squareMaps.find(value => {
      return parseInt(value, 10) > target;
    });
  }

  if (!found) {
    found = maps.find(value => {
      return parseInt(value, 10) > target;
    });
  }

  if (!found) {
    found = 'origin';
  }

  return get(img, found, defaultValue);
}
