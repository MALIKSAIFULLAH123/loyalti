import { Box, SxProps } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const StyledSeparator = styled(Box, {
  name: 'DotSeparator',
  shouldForwardProp: (prop: string) =>
    !/isTruncateOneLine|placement|separator/.test(prop)
})<{
  placement?: 'start' | 'end';
  isTruncateOneLine?: boolean;
  separator: string;
}>(({ placement, separator, isTruncateOneLine }) => ({
  // use when CategoryList wants to have 1 line and truncate the category
  ...(isTruncateOneLine && {
    display: 'block !important',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    maxWidth: '100%'
  }),
  ...(placement === 'end' && {
    '&>span:not(:last-child):after': {
      textDecoration: 'none',
      content: separator ?? '"·"',
      paddingLeft: '0.4em',
      paddingRight: '0.4em',
      fontWeight: 'normal'
    }
  }),
  ...(placement === 'start' && {
    '&>span:not(:first-of-type):before': {
      textDecoration: 'none',
      content: separator ?? '"·"',
      paddingLeft: '0.4em',
      paddingRight: '0.4em',
      fontWeight: 'normal'
    }
  })
}));

export default function DotSeparator({
  placement = 'end',
  isTruncateOneLine = false,
  children,
  separator = '"·"',
  ...props
}: {
  className?: string;
  placement?: 'end' | 'start';
  isTruncateOneLine?: boolean;
  separator?: string;
  sx?: SxProps;
  children?: React.ReactNode;
}) {
  return (
    <StyledSeparator
      separator={separator}
      isTruncateOneLine={isTruncateOneLine}
      placement={placement}
      {...props}
    >
      {React.Children.toArray(children).map((node, index) => (
        <span key={index.toString()} children={node} />
      ))}
    </StyledSeparator>
  );
}
