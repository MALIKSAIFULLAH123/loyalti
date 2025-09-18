import { RefOf } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

interface Props {
  children: React.ReactNode;
  variant?: 'buddyPanel' | 'roomPanel';
  collapsed?: boolean;
}

const name = 'ChatDockPanel';

const Root = styled('div', {
  name,
  slot: 'Root'
})<{ collapsed?: boolean }>(({ theme }) => ({
  display: 'flex',
  alignItems: 'flex-end',
  marginLeft: theme.spacing(2),
  width: '328px',
  zIndex: 999
}));

const PanelStyled = styled('div', {
  name,
  slot: 'PanelStyled'
})<{ collapsed?: boolean }>(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  zIndex: 10,
  backgroundColor: theme.mixins.backgroundColor('paper'),
  borderTopLeftRadius: theme.shape.borderRadius / 2,
  borderTopRightRadius: theme.shape.borderRadius / 2,
  boxShadow: '0 0 4px 0 rgba(0,0,0,0.2)',
  wordBreak: 'break-word',
  wordWrap: 'break-word',
  borderStartStartRadius: theme.shape.borderRadius,
  borderStartEndRadius: theme.shape.borderRadius,
  height: '455px',
  maxHeight: 'calc(100vh - 60px)'
}));

const StageStyled = styled('div', {
  name,
  slot: 'StageStyled'
})(({ theme }) => ({
  width: '100%'
}));

function ChatDockPanel({ children }: Props, ref: RefOf<HTMLDivElement>) {
  return (
    <Root ref={ref}>
      <StageStyled>
        <PanelStyled>{children}</PanelStyled>
      </StageStyled>
    </Root>
  );
}

export default React.forwardRef<HTMLDivElement, Props>(ChatDockPanel);
