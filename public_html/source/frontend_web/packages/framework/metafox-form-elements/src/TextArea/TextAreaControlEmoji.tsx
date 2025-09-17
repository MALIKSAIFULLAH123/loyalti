import { RefOf } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled, Tooltip } from '@mui/material';
import React from 'react';

const IconButtonStyled = styled(IconButton, {
  name: 'TextareaControl',
  slot: 'IconButtonStyled'
})(({ theme }) => ({
  padding: 0,
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: theme.spacing(3.5),
  height: theme.spacing(3.5),
  minWidth: theme.spacing(3.5),
  color: `${theme.palette.text.secondary} !important`
}));
const LineIconStyled = styled(LineIcon, {
  name: 'TextAreaControlEmoji',
  slot: 'LineIconStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(14),
  [theme.breakpoints.down('sm')]: {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

export function TextAreaControlEmoji(
  { title, icon, onClick, testid }: Record<string, any>,
  ref: RefOf<HTMLButtonElement>
) {
  return (
    <Tooltip title={title}>
      <IconButtonStyled
        onClick={onClick}
        size="smaller"
        ref={ref}
        data-testid={testid}
        role="button"
      >
        <LineIconStyled icon={icon} />
      </IconButtonStyled>
    </Tooltip>
  );
}

export default React.forwardRef<HTMLButtonElement>(TextAreaControlEmoji);
