import { Link } from '@metafox/framework';
import { MenuItemShape } from '@metafox/ui';
import clsx from 'clsx';
import React, { forwardRef } from 'react';

export type ProfileMenuItemProps = {
  classes?: Record<'menuItem' | 'activeMenuItem' | 'menuLink' | string, string>;
} & MenuItemShape;

export default forwardRef(
  (props: ProfileMenuItemProps, ref: React.LegacyRef<HTMLLIElement>) => {
    const { to, label, classes, keepScroll, active, asChildPage, className } =
      props;

    return (
      <Link
        to={to}
        keepScroll={keepScroll}
        asChildPage={asChildPage}
        children={label}
        className={clsx(
          className,
          classes.menuItem,
          active && classes.activeMenuItem,
          classes.menuLink
        )}
      />
    );
  }
);
