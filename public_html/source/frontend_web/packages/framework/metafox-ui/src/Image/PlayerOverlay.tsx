import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import { TImagePlayerOverlay } from '../types';

const StyledOverlay = styled('div', {
  name: 'Overlay'
})<{ size: 'xs' | 'sm' | 'md' | 'lg' }>(({ size }) => ({
  position: 'absolute',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  pointerEvents: 'none',
  color: '#fff',
  ...(size === 'lg' && {
    fontSize: '96px'
  }),
  ...(size === 'md' && {
    fontSize: '72px'
  }),
  ...(size === 'sm' && {
    fontSize: '48px'
  }),
  ...(size === 'xs' && {
    fontSize: '24px'
  })
}));

const PlayerOverlay = ({
  icon,
  size = 'md',
  ...props
}: TImagePlayerOverlay) => {
  return (
    <StyledOverlay size={size} {...props}>
      <LineIcon icon={icon} />
    </StyledOverlay>
  );
};

export default PlayerOverlay;
