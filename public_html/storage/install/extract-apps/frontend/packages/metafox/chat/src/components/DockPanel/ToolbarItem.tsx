/**
 * @type: ui
 * name: chatToolbar.item.button
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const name = 'ChatToolBarDropdownMenu';

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
    '.panelHeader &': {
      height: '30px',
      width: '30px'
    },
    ...(btnActive && {
      color: theme.palette.primary.main
    })
  })
);

export default function ToolbarItem({
  item: { value, icon, active, label },
  handleAction
}) {
  const { i18n } = useGlobal();

  const onClick = (evt: React.SyntheticEvent<HTMLButtonElement>) => {
    if (evt) {
      evt.stopPropagation();
    }

    if (value) handleAction(value);
  };

  return (
    <Item data-testid={camelCase('_chat toolbar item')} itemActive={active}>
      <Tooltip
        title={label ? i18n.formatMessage({ id: label }) : ''}
        placement="top"
      >
        <ButtonStyled btnActive={active} onClick={onClick}>
          {icon ? <LineIcon icon={icon} /> : null}
        </ButtonStyled>
      </Tooltip>
    </Item>
  );
}
