import { styled } from '@mui/material';
import React from 'react';
import { AttachIconsWrapperProps } from '../type';

const name = 'AttachIconsWrapper';

const RootStyled = styled('form', {
  name,
  slot: 'RootStyled'
})(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'flex-end',
  padding: '0 2px',
  marginLeft: 'auto',
  '& .ico-smile-o': {
    fontSize: theme.mixins.pxToRem(15)
  }
}));

export default function AttachIconsWrapper({
  children
}: AttachIconsWrapperProps) {
  return <RootStyled>{children}</RootStyled>;
}
