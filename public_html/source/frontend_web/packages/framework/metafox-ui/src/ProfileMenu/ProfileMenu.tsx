import { Link, useGlobal, useLocation } from '@metafox/framework';
import { LineIcon, MenuItemShape } from '@metafox/ui';
import Popper from '@mui/material/Popper';
import clsx from 'clsx';
import { isEqual } from 'lodash';
import React, { useEffect, useRef } from 'react';
import ReactResizeDetector from 'react-resize-detector';
import ClickOutsideListener from '../ClickOutsideListener';
import useStyles from './ProfileMenu.styles';
import { comparePathName } from '@metafox/utils';

const IsUrlReg = /^(http|https)?:?\/\//s;

export type ProfileMenuProps = {
  items?: MenuItemShape[];
  activeTab?: string;
  [key: string]: any;
  maxDisplayTab?: number;
};

function ProfileMenu({
  items,
  activeTab,
  maxDisplayTab,
  prefix: prefixProp,
  replace = true
}: ProfileMenuProps) {
  const classes = useStyles();
  const [state, setState] = React.useState<{
    splitIndex: number;
  }>({ splitIndex: -1 });
  const [open, setOpen] = React.useState<boolean>(false);
  const [moreMenu, setMoreMenu] = React.useState<boolean>(true);
  const { i18n, usePageParams } = useGlobal();
  const hiddenMenuRef = useRef<HTMLDivElement>();
  const menuRef = useRef<HTMLDivElement>();
  const visibleMenuRef = useRef<HTMLDivElement>();
  const refMenuMore = useRef(null);
  const refMenuMoreHide = useRef(null);
  const { id: user_id, resource_name } = usePageParams();
  const prefix = prefixProp || `/${resource_name}/${user_id}`;
  const activeTabRef = React.useRef(null);
  const { pathname } = useLocation();

  const onResize = React.useCallback(() => {
    if (!hiddenMenuRef.current) return;

    const cc: HTMLCollection = hiddenMenuRef.current?.children;

    if (!cc || !cc.length) return;

    const maxWidth = menuRef.current.getBoundingClientRect().width;
    const moreMenuWidth = refMenuMoreHide.current.getBoundingClientRect().width;

    let splitIndex = -1;
    let totalWidth = 0;

    while (
      totalWidth + moreMenuWidth < maxWidth &&
      splitIndex <= maxDisplayTab
    ) {
      const nextItemSize = cc[splitIndex + 1]?.getBoundingClientRect().width;
      totalWidth += nextItemSize;
      splitIndex++;
    }

    const moreMenuStates =
      0 <= splitIndex && splitIndex < cc.length ? true : false;
    setMoreMenu(moreMenuStates);

    setState(prev => {
      return {
        ...prev,
        splitIndex: maxDisplayTab
          ? Math.min(splitIndex, maxDisplayTab)
          : splitIndex
      };
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [maxDisplayTab, menuRef.current]);

  React.useLayoutEffect(() => {
    onResize();
  }, [onResize, items]);

  useEffect(() => {
    activeTabRef.current?.scrollIntoView({
      behavior: 'auto',
      block: 'start',
      inline: 'center'
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const toggleOpen = () => setOpen((prev: boolean) => !prev);
  const closeMenu = () => setOpen(false);
  const parseUrl = React.useCallback(
    (item: MenuItemShape) => {
      if (item.tab) return `${prefix}/${item.tab}`;

      if (item.to) {
        if (IsUrlReg.test(item.to) || item.to.startsWith('/')) {
          return item.to;
        }
      }

      return `${prefix}/${item.to}`;
    },
    [prefix]
  );

  const checkActive = (item: MenuItemShape) => {
    return item.tab === activeTab || comparePathName(pathname, parseUrl(item));
  };

  return (
    <div className={classes.root} ref={menuRef} data-testid="menuProfile">
      <ReactResizeDetector
        handleWidth
        handleHeight={false}
        onResize={onResize}
        refreshMode="debounce"
        refreshRate={100}
        skipOnMount
      />
      <div ref={hiddenMenuRef} className={classes.hiddenMenu}>
        {items.map((item, index) => (
          <Link
            role="tab"
            key={index.toString()}
            to={parseUrl(item)}
            id={item.tab}
            className={classes.tabItem}
            underline="none"
            replace={replace}
          >
            {item.label}
          </Link>
        ))}
        <div
          role="tab"
          ref={refMenuMoreHide}
          aria-selected="false"
          className={clsx(classes.tabItem)}
        >
          {i18n.formatMessage({ id: 'more' })}{' '}
          <LineIcon icon="ico-caret-down" />
        </div>
      </div>
      <div
        data-testid="menuProfileWrap"
        className={classes.visibleMenu}
        ref={visibleMenuRef}
      >
        {items.slice(0, state.splitIndex).map((item, index) => (
          <Link
            data-testid="itemMenuProfile"
            role="tab"
            aria-selected={checkActive(item)}
            key={index.toString()}
            to={parseUrl(item)}
            id={item.tab}
            keepScroll
            asChildPage
            underline="none"
            className={clsx(
              classes.tabItem,
              checkActive(item) ? classes.tabItemActive : undefined
            )}
            replace={replace}
          >
            {item.label}
          </Link>
        ))}
      </div>
      <ClickOutsideListener onClickAway={closeMenu}>
        <div
          className={clsx(
            classes.secondMenu,
            0 > state.splitIndex && classes.invisible
          )}
        >
          <div
            data-testid="moreMenuProfile"
            role="tab"
            ref={refMenuMore}
            aria-selected="false"
            onClick={toggleOpen}
            className={clsx(
              classes.tabItem,
              classes.tabItemMore,
              moreMenu && classes.moreMenu
            )}
          >
            {i18n.formatMessage({ id: 'more' })}{' '}
            <LineIcon icon="ico-caret-down" />
          </div>
          <Popper
            open={open}
            anchorEl={refMenuMore.current}
            className={classes.popperMenu}
          >
            {items.slice(state.splitIndex).map((item, index) => (
              <Link
                role="tab"
                aria-disabled={checkActive(item)}
                key={index.toString()}
                to={parseUrl(item)}
                keepScroll
                asChildPage
                replace={replace}
                className={clsx(
                  classes.menuItem,
                  checkActive(item) && classes.menuItemActive
                )}
              >
                {item.label}
              </Link>
            ))}
          </Popper>
        </div>
      </ClickOutsideListener>
    </div>
  );
}

export default React.memo(ProfileMenu, isEqual);
