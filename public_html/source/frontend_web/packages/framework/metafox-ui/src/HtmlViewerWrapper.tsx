import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'HtmlWrapper';

const Content = styled(Box, {
  name,
  slot: 'htmlWrapper',
  shouldForwardProp: props => props !== 'mt'
})<{ mt?: number }>(({ theme, mt = 2 }) => ({
  marginTop: theme.spacing(mt),
  '& > p': {
    margin: 0
  }
}));

export default function HtmlWrapper({ children, ...rest }) {
  return <Content {...rest}>{children}</Content>;
}
