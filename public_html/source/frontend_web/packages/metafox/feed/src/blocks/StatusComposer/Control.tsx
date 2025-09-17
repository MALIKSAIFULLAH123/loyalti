import { LineIcon } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import React from 'react';

const StyledButton = styled(IconButton, {
  name: 'StatusControl',
  slot: 'Control',
  shouldForwardProp: props => props !== 'canUploadTypes'
})<{ canUploadTypes?: any }>(({ theme }) => ({
  width: 32,
  height: 32,
  fontSize: theme.mixins.pxToRem(20),
  [theme.breakpoints.up('sm')]: {
    width: 44,
    height: 44
  }
}));

export default function Control({ icon, testid, ...rest }) {
  return (
    <StyledButton data-testid={testid} color="primary" {...rest}>
      <LineIcon icon={icon} />
    </StyledButton>
  );
}
