/**
 * @type: ui
 * name: chatplusToolbar.item.dropdown
 * chunkName: chatplusUI
 */
import { useSessionUser } from '@metafox/chatplus/hooks';
import { useGlobal } from '@metafox/framework';
import { ItemActionMenu, LineIcon } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
import clsx from 'clsx';
import React from 'react';

const ButtonStyled = styled('button', {
  shouldForwardProp: props => props !== 'variant'
})<{ variant?: string }>(({ theme, variant }) => ({
  height: '34px',
  lineHeight: '20px',
  fontSize: '17px',
  padding: '0 6px',
  cursor: 'pointer',
  color:
    theme.palette.mode === 'light'
      ? theme.palette.grey['600']
      : theme.palette.text.secondary,
  position: 'relative',
  minWidth: theme.spacing(3),
  display: 'inline-flex',
  textAlign: 'center',
  alignItems: 'center',
  justifyContent: 'center',
  border: 'none',
  outline: 'none',
  backgroundColor: 'transparent',
  '&:hover': {
    color: theme.palette.text.secondary
  },
  ...(variant === 'pageMessage' && {
    color:
      theme.palette.mode === 'light'
        ? theme.palette.grey['600']
        : theme.palette.text.primary,
    fontSize: theme.spacing(2.25),
    minWidth: theme.spacing(6.25),
    width: '34px'
  })
}));

export default function ToolbarItem({
  item: { icon, active, label, items, disablePortal = true },
  handleAction,
  classes,
  variant,
  popperOptions,
  popperStyles: popperStyleProps = {},
  menuStyles,
  ...rest
}: any) {
  const { i18n, useTheme } = useGlobal();
  const userSession = useSessionUser();
  const theme = useTheme();

  const popperStyles = {
    zIndex: theme.zIndex.snackbar,
    minWidth: 'max-content',
    width: 'auto',
    maxHeight: '70vh',
    ...popperStyleProps
  };

  const subitems = items.map(item => {
    if (item.name === 'status_user' && userSession?.status === item.item_name) {
      return { ...item, active: true };
    }

    return item;
  });

  return (
    <ItemActionMenu
      placement="bottom-end"
      items={subitems}
      disablePortal={disablePortal}
      handleAction={handleAction}
      popperOptions={popperOptions}
      popperStyles={popperStyles}
      menuStyles={menuStyles}
      {...rest}
      control={
        <div className={clsx(classes.item, active && classes.itemActive)}>
          <Tooltip
            title={label ? i18n.formatMessage({ id: label }) : ''}
            placement="top"
          >
            <ButtonStyled
              variant={variant}
              className={clsx(classes.btn, active && classes.btnActive)}
            >
              {icon ? <LineIcon icon={icon} /> : null}
            </ButtonStyled>
          </Tooltip>
        </div>
      }
    />
  );
}
