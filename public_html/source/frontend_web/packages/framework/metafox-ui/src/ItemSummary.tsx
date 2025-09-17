import { RefOf } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { TruncateTextProps } from '@metafox/ui';
import TruncateText from '@metafox/ui/TruncateText';
import { styled } from '@mui/material/styles';
import React from 'react';

export interface ItemSummaryClassName {
  root: string;
}

export interface ItemSummaryProps extends TruncateTextProps {}

const ItemSummary = styled(TruncateText, {
  name: 'MuiItemSummary',
  slot: 'Root'
})<ItemSummaryProps>(({ theme }) => ({
  position: 'relative',
  flex: 1,
  minWidth: 0,
  width: 'fit-content',
  display: 'flex',
  flexDirection: 'column'
}));

const ItemSummaryRoot = React.forwardRef(
  (props: ItemSummaryProps, ref: RefOf<HTMLDivElement>) => {
    const { itemProps: { summary } = {} } = useBlock();

    return (
      <ItemSummary
        ref={ref}
        className="ItemView-summary"
        data-testid="itemSummary"
        {...summary}
        {...props}
      />
    );
  }
);

export default ItemSummaryRoot;
