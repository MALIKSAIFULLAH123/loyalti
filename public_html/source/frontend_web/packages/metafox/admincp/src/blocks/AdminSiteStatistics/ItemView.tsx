import { LineIcon } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'AdminCpItemSiteStatistics';

const Item = styled(Box, { name, slot: 'Item' })(({ theme }) => ({
  height: '91px',
  minWidth: '160px',
  flex: 1
}));

const Icon = styled(LineIcon, {
  name,
  slot: 'Icon',
  shouldForwardProp: prop => prop !== 'indexIcon'
})<{ indexIcon?: number }>(({ theme, indexIcon }) => ({
  fontSize: '40px',
  lineHeight: '40px',
  marginRight: theme.spacing(2),
  ...(indexIcon === 0 && {
    color: '#536dfe'
  }),
  ...(indexIcon === 1 && {
    color: '#a1560f'
  }),
  ...(indexIcon === 2 && {
    color: '#2bbd7e'
  }),
  ...(indexIcon === 3 && {
    color: '#ff6e40'
  })
}));

const Value = styled('span', { name, slot: 'Value' })(({ theme }) => ({
  fontSize: '22px',
  lineHeight: '30px'
}));

const Label = styled('span', { name, slot: 'Label' })(({ theme }) => ({
  maxWidth: '100%',
  display: 'block',
  overflow: 'hidden',
  padding: '0',
  fontSize: '16px',
  textOverflow: 'ellipsis',
  lineHeight: '20px',
  maxHeight: '40px',
  whiteSpace: 'normal',
  color: '#a2a2a2',
  wordBreak: 'break-word',
  wordWrap: 'break-word'
}));

interface Props {
  icon: string;
  value?: number;
  label: string;
  onClick?: () => void;
  style?: React.CSSProperties;
  index: number;
}

export default function ItemStatsItemView({
  icon,
  value,
  label,
  index,
  onClick,
  style
}: Props) {
  return (
    <Item style={style} onClick={onClick}>
      <Icon icon={icon} indexIcon={index} />
      <Value>{value}</Value>
      <Label>{label}</Label>
    </Item>
  );
}
