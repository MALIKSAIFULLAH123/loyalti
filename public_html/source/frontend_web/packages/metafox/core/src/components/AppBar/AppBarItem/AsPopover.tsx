/**
 * @type: ui
 * name: appbar.item.popover
 * chunkName: appbarAs
 */
import { getStatusSelector } from '@metafox/core/selectors/status';
import { useGlobal, useLocation } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import {
  LineIcon,
  MenuItemViewProps as Props,
  PopoverContext
} from '@metafox/ui';
import { Badge, ClickAwayListener, Tooltip } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import { useSelector } from 'react-redux';

export default function AsPopover({ item, classes }: Props) {
  const { jsxBackend, i18n } = useGlobal();
  const anchorRef = React.useRef(null);
  const [open, setOpen] = React.useState<boolean>(false);
  const toggleOpen = () => setOpen((prev: boolean) => !prev);
  const status = useSelector(getStatusSelector);
  const { pathname } = useLocation();

  const block = useBlock();

  const badgeContent = status[item.name] || undefined;

  const handleClickOutside = () => {
    setOpen(false);
  };

  React.useEffect(() => {
    setOpen(false);
  }, [pathname]);

  return (
    <ClickAwayListener onClickAway={handleClickOutside}>
      <div
        className={clsx(
          classes.menuRefIndex,
          classes.menuButton,
          item.behavior ? classes.moreHeaderMenu : ''
        )}
        ref={anchorRef}
      >
        <div
          data-testid={item.name}
          onClick={toggleOpen}
          className={clsx(
            classes.smallMenuButton,
            pathname === item.to ? classes.activeMenu : '',
            item.active || open ? classes.menuButtonActive : false
          )}
        >
          <Tooltip
            title={item?.label ? i18n.formatMessage({ id: item?.label }) : ''}
          >
            {badgeContent ? (
              <Badge color="error" badgeContent={badgeContent} max={99}>
                <LineIcon className={classes.smallMenuIcon} icon={item.icon} />
              </Badge>
            ) : (
              <LineIcon className={classes.smallMenuIcon} icon={item.icon} />
            )}
          </Tooltip>
        </div>
        <PopoverContext.Provider value={{ closePopover: handleClickOutside }}>
          {jsxBackend.render({
            component: item.content?.component,
            props: {
              closePopover: handleClickOutside,
              open,
              anchorRef,
              placement: item.placement ?? 'bottom-start',
              disablePortal: ![
                'header',
                'main',
                'headerShort',
                'headerBottom'
              ].includes(block?.slotName)
            }
          })}
        </PopoverContext.Provider>
      </div>
    </ClickAwayListener>
  );
}
