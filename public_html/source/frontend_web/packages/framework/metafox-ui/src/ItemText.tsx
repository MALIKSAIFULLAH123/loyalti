import { ItemConfigShape, RefOf } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

export type ItemTextProps = ItemConfigShape['text'];

export interface ItemTextClassName {
  title: string;
}

const ItemText = styled(Box, {
  name: 'ItemView',
  slot: 'Text'
})(({ theme }) => ({
  position: 'relative',
  flex: 1,
  minWidth: 0,
  display: 'flex',
  flexDirection: 'column'
}));

export interface Props {
  children?: React.ReactNode;
}

export default React.forwardRef<HTMLDivElement, Props>(
  (props: Props, ref: RefOf<HTMLDivElement>) => {
    const { itemProps: { text } = {} } = useBlock();

    return (
      <ItemText
        data-testid="itemText"
        className="ItemView-text"
        ref={ref}
        {...text}
        {...props}
      />
    );
  }
);
