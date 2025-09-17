/**
 * @type: service
 * name: ItemActionMenu
 */
import { useGetItem, useGlobal, useSession } from '@metafox/framework';
import { ControlMenu, LineIcon } from '@metafox/ui';
import { IconButton, Tooltip } from '@mui/material';
import { assign } from 'lodash';
import React, { useEffect, useState } from 'react';
import { ItemActionMenuProps, MenuItemShape } from '../types';

export default function ItemActionMenu(props: ItemActionMenuProps) {
  const {
    id,
    label,
    dependName,
    appName,
    menuName = 'itemActionMenu',
    icon = 'ico-dottedmore-vertical-o',
    size = 'small',
    disableRipple = false,
    color,
    variant,
    identity,
    testid,
    handleAction,
    className,
    iconClassName,
    control,
    items,
    tooltipTitle = '',
    autoHide = true,
    placement,
    triggerOpen,
    ...rest
  } = props;
  const session = useSession();
  const item = useGetItem(identity);
  const { compactUrl, i18n } = useGlobal();
  const [open, setOpen] = useState<boolean>(false);
  const [menu, setMenu] = useState<MenuItemShape[]>(items ?? []);

  useEffect(() => {
    if (triggerOpen) {
      triggerOpen(open);
    }
  }, [triggerOpen, open]);

  const toggleMenu = () => {
    setOpen(prev => !prev);
  };

  const localHandler = (types: string, data?: unknown, meta?: unknown) => {
    handleAction(
      types,
      data,
      assign(
        {
          setMenu
        },
        meta
      )
    );
  };

  // update items props
  useEffect(() => {
    setMenu(items);
  }, [items]);

  useEffect(() => {
    if (items || (!items && (open || !autoHide))) return;

    localHandler(appName && menuName ? 'presentAppMenu' : 'presentItemMenu', {
      dependName,
      appName,
      menuName
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [appName, dependName, identity, menuName, open, item, autoHide]);

  if (!session.loggedIn || (!menu?.length && autoHide)) return null;

  const menuCompact: MenuItemShape[] = menu?.map(menuItem => {
    return {
      ...menuItem,
      label: menuItem?.label && compactUrl(menuItem?.label, item)
    };
  });

  return (
    <ControlMenu
      open={Boolean(open && menu?.length)}
      id={id ?? menuName}
      label={label ?? i18n.formatMessage({ id: 'action_menu' })}
      handleAction={localHandler}
      items={menuCompact}
      testid={testid ?? 'action menu'}
      onOpen={toggleMenu}
      onClose={toggleMenu}
      identity={identity}
      placement={placement}
      {...rest}
      control={
        control ?? (
          <Tooltip title={tooltipTitle} disableHoverListener={!tooltipTitle}>
            <IconButton
              size={size}
              color={color}
              variant={variant}
              className={className}
              disableRipple={disableRipple}
              disableFocusRipple
            >
              <LineIcon icon={icon} className={iconClassName} />
            </IconButton>
          </Tooltip>
        )
      }
    />
  );
}
