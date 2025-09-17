import { Link, useGlobal } from '@metafox/framework';
import { ClickOutsideListener, LineIcon } from '@metafox/ui';
import Popper from '@mui/material/Popper';
import clsx from 'clsx';
import { debounce } from 'lodash';
import React, { useEffect, useRef } from 'react';
import { ProfileMenuProps } from '../../types';
import useStyles from './ProfileMenu.styles';

function ProfileMenu({ menuItems, prefix, activeTab }: ProfileMenuProps) {
  const classes = useStyles();
  const [state, setState] = React.useState<{
    menuOpened: boolean;
    splitIndex: number;
  }>({ menuOpened: false, splitIndex: -1 });
  const [open, setOpen] = React.useState<boolean>(false);
  const { i18n } = useGlobal();
  const rootRef = useRef<HTMLDivElement>();
  const secondMenuRef = useRef<HTMLDivElement>();
  const refMenuMore = useRef(null);

  const onResize = React.useCallback(() => {
    if (!rootRef.current) return;

    const cc: HTMLCollection = rootRef.current?.children;
    const maxWidth =
      rootRef.current.getBoundingClientRect().width -
      secondMenuRef.current.getBoundingClientRect().width;

    if (!cc || !cc.length) return;

    let width = 0;

    let splitIndex;

    for (let i = 0; i < cc.length; i++) {
      width = width + cc[i].getBoundingClientRect().width;

      if (width > maxWidth) break;

      splitIndex = i;
    }

    if (splitIndex >= cc.length - 1) {
      splitIndex = -1;
    }

    setState(prev => ({ ...prev, splitIndex }));
  }, []);

  useEffect((): (() => void) | void => {
    if ('object' === typeof window) {
      window.addEventListener('resize', debounce(onResize, 100));

      onResize();

      return () => {
        window.removeEventListener('resize', debounce(onResize, 100));
      };
    }
  }, [onResize]);

  const toggleOpen = () => setOpen((prev: boolean) => !prev);
  const closeMenu = () => setOpen(false);

  return (
    <div className={classes.root} ref={rootRef}>
      <ClickOutsideListener onClickAway={closeMenu}>
        <div
          ref={secondMenuRef}
          className={clsx(
            classes.secondMenu,
            0 > state.splitIndex && classes.invisible
          )}
        >
          <div
            role="tab"
            ref={refMenuMore}
            aria-selected="false"
            onClick={toggleOpen}
            className={clsx(classes.tabItem, classes.tabItemMore)}
          >
            {i18n.formatMessage({ id: 'more' })}{' '}
            <LineIcon icon="ico-caret-down" />
          </div>
          <Popper
            open={open && 0 < state.splitIndex}
            anchorEl={refMenuMore.current}
            className={classes.popperMenu}
          >
            {menuItems.slice(state.splitIndex).map((item, index) => (
              <Link
                role="tab"
                aria-disabled={item.tab === activeTab ? false : true}
                key={index.toString()}
                to={`${prefix}/${item.tab}`}
                keepScroll
                asChildPage
                className={clsx(
                  classes.menuItem,
                  item.tab === activeTab && classes.menuItemActive
                )}
              >
                {item.label}
              </Link>
            ))}
          </Popper>
        </div>
      </ClickOutsideListener>
      {menuItems.map((item, index) => (
        <Link
          role="tab"
          aria-selected={item.tab === activeTab ? false : true}
          key={index.toString()}
          to={`${prefix}/${item.tab}`}
          id={item.tab}
          keepScroll
          asChildPage
          className={clsx(
            classes.tabItem,
            item.tab === activeTab && classes.tabItemActive
          )}
        >
          {item.label}
        </Link>
      ))}
    </div>
  );
}

export default React.memo(ProfileMenu, () => true);
