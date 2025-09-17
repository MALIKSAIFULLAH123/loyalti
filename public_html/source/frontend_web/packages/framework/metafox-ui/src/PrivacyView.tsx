import { styled, Tooltip } from '@mui/material';
import React from 'react';
import LineIcon from './LineIcon';
import { useGlobal } from '@metafox/framework';

const Root = styled('span')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  backgroundColor:
    theme.palette.mode === 'light'
      ? theme.palette.grey['100']
      : theme.palette.action.disabledBackground,
  padding: theme.spacing(0.5, 1),
  borderRadius: theme.spacing(0.5),
  cursor: 'not-allowed'
}));
const Title = styled('span')(({ theme }) => ({
  paddingLeft: theme.spacing(0.5)
}));
export type PrivacyIconProps = {
  item?: Record<string, any>;
};

export default function PrivacyView({ item }: PrivacyIconProps) {
  const { getSetting } = useGlobal();

  if (!item) return null;

  const icon = getSetting(`platform.privacy_icon.${item?.privacy_icon}`);

  return (
    <Tooltip disableHoverListener={!item?.tooltip} title={item?.tooltip}>
      <Root>
        <LineIcon
          icon={icon}
          aria-label={item?.tooltip}
          role="img"
          data-testid="iconPrivacy"
        />
        <Title>{item?.label || ''}</Title>
      </Root>
    </Tooltip>
  );
}
