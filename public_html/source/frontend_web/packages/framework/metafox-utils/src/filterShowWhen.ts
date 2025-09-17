import { MenuItemShape } from '@metafox/ui';
import when from './when';

export default function filterShowWhen(
  items: MenuItemShape[],
  input: unknown
): MenuItemShape[] {
  let prev: string = '';

  if (!items) return [];

  return items
    .filter((item: MenuItemShape) => {
      // filter showWhen
      if (item.showWhen && Array.isArray(item.showWhen)) {
        return when(input, item.showWhen);
      }

      return true;
    })
    .filter((item: MenuItemShape, index: number) => {
      // filter divider
      if ('divider' === item.as) {
        if (0 === index || 'divider' === prev) {
          prev = item.as;

          return false;
        }
      }

      prev = item.as;

      return true;
    })
    .filter(Boolean);
}
