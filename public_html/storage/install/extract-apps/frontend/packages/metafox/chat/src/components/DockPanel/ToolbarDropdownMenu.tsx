/**
 * @type: ui
 * name: chatToolbar.item.dropdown
 */
import { useGlobal } from '@metafox/framework';
import { ItemActionMenu, LineIcon } from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import React from 'react';

const name = 'ChatToolBarDropdownMenu';

const ItemActionMenuStyled = styled(ItemActionMenu, {
  name,
  slot: 'ItemActionMenuStyled',
  shouldForwardProp: props => props !== 'variant'
})<{ variant?: string }>(({ theme, variant }) => ({
  ...(variant === 'pageMessage' && {
    width: '190px'
  })
}));

const Item = styled(Box, {
  name,
  slot: 'Item',
  shouldForwardProp: props => props !== 'itemActive'
})<{ itemActive?: boolean }>(({ theme, itemActive }) => ({
  position: 'relative',
  lineHeight: '34px',
  padding: '0',
  margin: '0',
  '.panelHeader &': {
    height: '30px',
    width: '30px',
    marginLeft: theme.spacing(0.5)
  },
  '.panelHeader &:hover button': {
    color: theme.palette.text.secondary,
    backgroundColor: theme.palette.action.selected,
    borderRadius: '50%'
  },
  ...(itemActive && {})
}));

const ButtonStyled = styled('button', {
  name,
  slot: 'ButtonStyled',
  shouldForwardProp: props => props !== 'variant' && props !== 'btnActive'
})<{ variant?: string; btnActive?: boolean }>(
  ({ theme, variant, btnActive }) => ({
    height: '34px',
    lineHeight: '20px',
    fontSize: '17px',
    padding: '0 6px',
    cursor: 'pointer',
    color: theme.palette.text.hint,
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
      minWidth: theme.spacing(6.25)
    }),
    ...(btnActive && {
      color: theme.palette.primary.main
    })
  })
);

export default function ToolbarItem({
  item: { icon, active, label, items, disablePortal = true },
  handleAction,
  variant
}) {
  const { i18n } = useGlobal();

  return (
    <ItemActionMenuStyled
      placement="bottom-end"
      items={items}
      disablePortal={disablePortal}
      handleAction={handleAction}
      variant={variant}
      control={
        <Item itemActive={active}>
          <Tooltip
            title={label ? i18n.formatMessage({ id: label }) : ''}
            placement="top"
          >
            <ButtonStyled
              variant={variant}
              btnActive={active}
            >
              {icon ? <LineIcon icon={icon} /> : null}
            </ButtonStyled>
          </Tooltip>
        </Item>
      }
    />
  );
}
