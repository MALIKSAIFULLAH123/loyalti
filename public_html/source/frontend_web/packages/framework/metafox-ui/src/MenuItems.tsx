import { MenuShape, useGlobal } from '@metafox/framework';
import { MenuItemViewProps } from '@metafox/ui';
import { assign } from 'lodash';

export type Props = Pick<
  MenuItemViewProps,
  'pathname' | 'classes' | 'handleAction'
> & {
  items: MenuShape['items'];
  prefixName?: string;
  fallbackName?: string;
  identity?: string;
  closeMenu(): void;
};

export default function MenuItems({
  prefixName = 'menuItem.as.',
  fallbackName = 'normal',
  identity,
  items,
  closeMenu,
  ...rest
}: Props): JSX.Element {
  const { jsxBackend } = useGlobal();

  return items.map((item, index) =>
    jsxBackend.render({
      component: `${prefixName}${item.as || fallbackName}`,
      props: assign({ key: index, item, identity, closeMenu }, rest)
    })
  ) as any;
}
