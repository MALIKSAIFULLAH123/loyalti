/**
 * @type: ui
 * name: appbar.item.tab
 * chunkName: appbarAs
 */
import { RouteLink as Link } from '@metafox/framework';
import { LineIcon, MenuItemViewProps as Props } from '@metafox/ui';
import clsx from 'clsx';
import React from 'react';

export default function AsTabItem({ item, classes, pathname }: Props) {
  return (
    <Link
      role="tab"
      key={item.to}
      to={item.to}
      data-testid={item.testid || item.name}
      aria-selected={item.to === pathname}
      className={clsx(
        classes.menuButton,
        item.to === pathname && classes.menuActive
      )}
    >
      <span className={classes.tabLink}>
        <LineIcon className={classes.menuButtonIcon} icon={item.icon} />
      </span>
    </Link>
  );
}
