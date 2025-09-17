import { Box, SxProps } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import { useBlock } from '@metafox/layout';

const StyledItemSubInfo = styled(Box, {
  name: 'ItemSubInfo'
})<{ placement?: 'start' | 'end' }>(({ placement }) => ({
  ...(placement === 'end' && {
    '&>span:not(:last-child):after': {
      textDecoration: 'none',
      content: '"·"',
      paddingLeft: '0.4em',
      paddingRight: '0.4em',
      fontWeight: 'normal'
    }
  }),
  ...(placement === 'start' && {
    '&>span:not(:first-of-type):before': {
      textDecoration: 'none',
      content: '"·"',
      paddingLeft: '0.4em',
      paddingRight: '0.4em',
      fontWeight: 'normal'
    }
  })
}));

export default function ItemSubInfo({
  placement = 'end',
  children,
  ...props
}: {
  className?: string;
  placement?: 'end' | 'start';
  sx?: SxProps;
  children?: React.ReactNode;
}) {
  const { itemProps: { subInfo } = {} } = useBlock();

  return (
    <StyledItemSubInfo
      data-testid="itemSubInfo"
      placement={placement}
      {...subInfo}
      {...props}
    >
      {React.Children.toArray(children).map((node, index) => (
        <span key={index.toString()} children={node} />
      ))}
    </StyledItemSubInfo>
  );
}
