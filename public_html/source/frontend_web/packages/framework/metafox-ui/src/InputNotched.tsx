import { styled } from '@mui/material/styles';
import React from 'react';

const NotchedOutlined = styled('fieldset', {
  name: 'NotchedOutlined',
  shouldForwardProp(prop: string) {
    return !/haveError|hoverState/.test(prop);
  }
})<{
  variant: string;
  haveError?: boolean;
  hoverState?: boolean;
}>(({ theme, variant, haveError, hoverState }) => ({
  ...(variant === 'outlined' && {
    textAlign: 'left',
    position: 'absolute',
    bottom: 0,
    right: 0,
    top: -5,
    left: 0,
    margin: 0,
    padding: '0 8px',
    pointerEvents: 'none',
    borderRadius: 4,
    borderStyle: 'solid',
    borderWidth: '1px',
    overflow: 'hidden',
    minWidth: '0%',
    borderColor:
      theme.palette.mode === 'light'
        ? '#0000003b'
        : 'rgba(255, 255, 255, 0.23)',
    '& span': {
      visibility: 'hidden'
    }
  }),
  ...(hoverState && {
    borderColor: theme.palette.mode === 'light' ? '#000' : '#fff'
  }),
  ...(haveError && {
    borderColor: theme.palette.error.main
  })
}));

const Legend = styled('legend', {
  shouldForwardProp: prop => prop !== 'haveChildren' && prop !== 'hoverState'
})<{
  haveChildren?: boolean;
}>(({ haveChildren }) => ({
  ...(!haveChildren && {
    padding: 0
  })
}));

export type InputNotchedProps = {
  children: any;
  variant: any;
  haveError?: boolean;
  hoverState?: boolean;
};

export default function InputNotched({
  children,
  variant,
  haveError,
  hoverState
}: InputNotchedProps) {
  return (
    <NotchedOutlined
      variant={variant}
      haveError={haveError}
      hoverState={hoverState}
    >
      <Legend haveChildren={!!children}>
        <span>{children}</span>
      </Legend>
    </NotchedOutlined>
  );
}
