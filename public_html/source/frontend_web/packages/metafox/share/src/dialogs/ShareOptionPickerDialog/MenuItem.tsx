import { LineIcon, MenuItemShape } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';

export type MenuItemProps = {
  item: MenuItemShape;
  onClick: () => void;
};

const name = 'MenuItem';

const ItemRoot = styled('div', { name, slot: 'itemRoot' })(({ theme }) => ({
  fontSize: '1rem',
  padding: theme.spacing(1, 2),
  cursor: 'pointer',
  '&:hover': {
    backgroundColor: theme.palette.action.selected
  }
}));
const ItemIcon = styled(LineIcon, { name, slot: 'itemIcon' })(({ theme }) => ({
  width: theme.spacing(4),
  display: 'inline-block'
}));

export default function MenuItem({ item, onClick }: MenuItemProps) {
  return (
    <ItemRoot onClick={onClick}>
      <ItemIcon icon={item.icon} />
      <span>{item.label}</span>
    </ItemRoot>
  );
}
