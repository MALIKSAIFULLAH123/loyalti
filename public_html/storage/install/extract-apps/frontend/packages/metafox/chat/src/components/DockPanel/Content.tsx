import { RefOf } from '@metafox/framework';
import { styled } from '@mui/material';
import React, { ReactNode } from 'react';

const Wrapper = styled('div', {
  name: 'ChatDockContent',
  slot: 'root',
  shouldForwardProp: prop => prop !== 'searching' && prop !== 'collapsed'
})<{ searching?: boolean; collapsed?: boolean }>(
  ({ theme, searching, collapsed }) => ({
    flex: 1,
    maxHeight: '100%',
    position: 'relative',
    paddingBottom: theme.spacing(0.75),
    ...(searching && { borderTop: 'none' }),
    ...(collapsed && { display: 'none' }),
    '&::-webkit-scrollbar': {
      height: '5px',
      width: '5px',
      background: 'gray',
      borderRadius: theme.spacing(0.5),
      transition: 'opacity 200ms'
    },

    /* Track */
    '&::-webkit-scrollbar-track': {
      borderRadius: theme.spacing(0.5),
      background:
        theme.palette.mode === 'light' ? 'white' : theme.palette.grey['300']
    },

    /* Handle */
    '&::-webkit-scrollbar-thumb': {
      background: theme.palette.grey['600'],
      borderRadius: theme.spacing(0.5)
    },

    /* Handle on hover */
    '&::-webkit-scrollbar-thumb:hover': {
      background: theme.palette.grey['700']
    },

    '&::-webkit-scrollbar-thumb:horizontal': {
      background: '#000',
      borderRadius: '10px'
    }
  })
);
interface Props {
  children: ReactNode;
  searching?: boolean;
  collapsed?: boolean;
}

function ChatDockContent(
  { children, searching, collapsed }: Props,
  ref: RefOf<HTMLDivElement>
) {
  return (
    <Wrapper ref={ref} searching={searching} collapsed={collapsed}>
      {children}
    </Wrapper>
  );
}

export default React.forwardRef<HTMLDivElement, Props>(ChatDockContent);
