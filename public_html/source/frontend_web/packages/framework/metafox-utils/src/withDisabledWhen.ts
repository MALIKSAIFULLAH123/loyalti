import { MenuItemShape } from '@metafox/ui';
import when from './when';

export default function withDisabledWhen(
  items: MenuItemShape[],
  input: unknown
): MenuItemShape[] {
  return items.map((item: MenuItemShape) => {
    // filter showWhen
    if (item.enabledWhen && Array.isArray(item.enabledWhen)) {
      return {
        ...item,
        disabled: !when(input, item.enabledWhen)
      };
    }

    return item;
  });
}
