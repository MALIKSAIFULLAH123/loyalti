import { LineIcon } from '@metafox/ui';
import { IconButton, styled, Tooltip } from '@mui/material';
import React from 'react';

const StyledButton = styled(IconButton, {
  name: 'StatusControl',
  slot: 'Control'
})(({ theme }) => ({
  width: 32,
  height: 32,
  fontSize: theme.mixins.pxToRem(20),
  [theme.breakpoints.up('sm')]: {
    width: 40,
    height: 40
  },
  '&.Mui-disabled': {
    pointerEvents: 'none'
  }
}));

function Control({ title, icon, ...rest }) {
  return (
    <Tooltip title={title}>
      <span>
        <StyledButton color="primary" size="medium" {...rest}>
          <LineIcon icon={icon} />
        </StyledButton>
      </span>
    </Tooltip>
  );
}

export default Control;
