import { Link } from '@metafox/framework';
import clsx from 'clsx';
import React, { forwardRef } from 'react';
import { ProfileMenuItemProps } from '../../types';

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
