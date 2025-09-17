import { useBlock } from '@metafox/layout';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import { BoxProps } from '@mui/system';
import React from 'react';

const BlockTitle = styled(Box, {
  name: 'GeneralBlock',
  slot: 'title'
})<BoxProps>(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  margin: 0,
  flex: 1
}));

const BlockTitleRoot = React.forwardRef<HTMLDivElement, BoxProps>(
  ({ children }, ref) => {
    const { blockProps: { titleStyle } = {} } = useBlock();

    return (
      <BlockTitle data-testid="blockTitle" ref={ref} {...titleStyle}>
        {children}
      </BlockTitle>
    );
  }
);

export default BlockTitleRoot;
