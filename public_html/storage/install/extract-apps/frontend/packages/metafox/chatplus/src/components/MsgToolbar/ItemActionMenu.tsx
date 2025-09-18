import { IPropClickAction } from '@metafox/chatplus/types';
import { useGetItem, useGlobal, useSession } from '@metafox/framework';
import {
  ControlMenu,
  ItemActionMenuProps,
  LineIcon,
  MenuItemShape
} from '@metafox/ui';
import { IconButton, Tooltip } from '@mui/material';
import { assign, isFunction } from 'lodash';
import React, { useEffect, useState } from 'react';

const TypeAction = 'actionMenu';

export default function ItemActionMenu(
  props: ItemActionMenuProps & {
    onClickAction?: (obj: IPropClickAction) => void;
    showActionRef?: any;
  }
) {
  const {
    id,
    label,
    dependName,
    appName,
    menuName = 'itemActionMenu',
    icon = 'ico-dottedmore-vertical-o',
    size = 'small',
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
    onClickAction,
    showActionRef,
    ...rest
  } = props;
  const session = useSession();
  const item = useGetItem(identity);
  const { compactUrl, i18n } = useGlobal();
  const [open, setOpen] = useState<boolean>(false);
  const [menu, setMenu] = useState<MenuItemShape[]>(items ?? []);

  const onOpen = () => {
    setOpen(prev => !prev);
    isFunction(onClickAction) && onClickAction({ identity, type: TypeAction });
  };

  const onClose = () => {
    setOpen(false);

    if (
      showActionRef?.current &&
      showActionRef?.current?.id === identity &&
      !showActionRef?.current?.showHover &&
      showActionRef?.current?.type === TypeAction &&
      isFunction(onClickAction)
    ) {
      onClickAction({ identity, type: TypeAction });
    }
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
      onOpen={onOpen}
      onClose={onClose}
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
              disableRipple
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
