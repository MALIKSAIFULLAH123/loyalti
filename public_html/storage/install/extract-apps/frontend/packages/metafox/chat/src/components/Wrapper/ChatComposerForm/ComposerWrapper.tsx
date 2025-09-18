import { styled } from '@mui/material';
import React from 'react';
import { ComposerWrapperProps } from '../type';

const name = 'ComposerWrapper';

const RootStyled = styled('form', {
  name,
  slot: 'RootStyled'
})(({ theme }) => ({
  maxHeight: '200px',
  overflowY: 'auto',
  flex: 1,
  flexBasis: 'auto',
  minWidth: 0,
  display: 'flex'
}));

export default function ComposerWrapper({
  children,
  onClick
}: ComposerWrapperProps) {
  return (
    <RootStyled onClick={onClick} data-testid="editor">
      {children}
    </RootStyled>
  );
}
