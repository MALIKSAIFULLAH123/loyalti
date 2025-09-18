import { RefOf } from '@metafox/framework';
import { LineIcon, Tooltip } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import React from 'react';
import { CommentComposerPluginControlProps } from '../../types';

const IconButtonStyled = styled(IconButton, {
  name: 'CommentControl',
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
  name: 'CommentControl',
  slot: 'LineIconStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(14),
  [theme.breakpoints.down('sm')]: {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

export function CommentControl(
  { title, icon, onClick, testid }: CommentComposerPluginControlProps,
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

export default React.forwardRef<
  HTMLButtonElement,
  CommentComposerPluginControlProps
>(CommentControl);
