import { styled } from '@mui/material';
import React from 'react';
import { ComposerFormProps } from '../type';

const name = 'ChatComposerForm';

const RootStyled = styled('form', {
  name,
  slot: 'RootStyled'
})(({ theme }) => ({
  width: '100%'
}));

const ComposeOuter = styled('div', {
  name,
  slot: 'composeOuter',
  shouldForwardProp: props => props !== 'margin'
})<{ margin?: string }>(({ theme, margin }) => ({
  display: 'flex',
  padding: theme.spacing(0.5, 0.5, 0.5, 2),
  minHeight: theme.spacing(4.25),
  alignItems: 'center',
  overflowY: 'auto',
  height: '100%',
  ...(margin === 'dense' && {
    padding: theme.spacing(0.5)
  })
}));

const ComposeInner = styled('div', {
  name,
  slot: 'composeInner'
})(({ theme }) => ({
  flex: 1,
  minWidth: 0
}));

const ComposeInputWrapper = styled('div', {
  name,
  slot: 'composeInputWrapper'
})(({ theme }) => ({
  width: '100%',
  flexFlow: 'wrap',
  display: 'flex',
  alignItems: 'center'
}));

export default function ChatComposerForm({
  children,
  className,
  margin = 'normal'
}: ComposerFormProps) {
  return (
    <RootStyled
      className={className}
      role="presentation"
      data-testid="chatComposerForm"
    >
      <ComposeOuter margin={margin}>
        <ComposeInner>
          <ComposeInputWrapper>{children}</ComposeInputWrapper>
        </ComposeInner>
      </ComposeOuter>
    </RootStyled>
  );
}
