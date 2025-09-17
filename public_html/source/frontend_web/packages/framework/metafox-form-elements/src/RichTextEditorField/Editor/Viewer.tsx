import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'RichTextEditorViewer';

const Content = styled(Box, {
  name,
  slot: 'viewer',
  shouldForwardProp: props => props !== 'mt'
})<{ mt?: number }>(({ theme, mt = 2 }) => ({
  marginTop: theme.spacing(mt),
  // pre-wrap keep Whitespace is preserved by the browser. Text will wrap when necessary, and on line breaks
  whiteSpace: 'pre-wrap',

  p: {
    margin: 0
  }
}));

export default function Viewer({ children, ...rest }) {
  return <Content {...rest}>{children}</Content>;
}
