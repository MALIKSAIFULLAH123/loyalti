import { RefOf } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { TruncateTextProps } from '@metafox/ui';
import TruncateText from '@metafox/ui/TruncateText';
import { LinkProps } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import React from 'react';

export interface ItemTitleClassName {
  root: string;
}

export interface ItemTitleProps extends TruncateTextProps {
  underline?: LinkProps['underline'];
}

const ItemTitle = styled(TruncateText, {
  name: 'ItemView',
  slot: 'Title'
})<ItemTitleProps>({
  minWidth: 0,
  '& .MuiFlag-root': {
    margin: '0 4px 0 0'
  }
});

const ItemTitleRoot = React.forwardRef(
  (
    { className, underline, ...props }: ItemTitleProps,
    ref: RefOf<HTMLDivElement>
  ) => {
    const { itemProps: { title } = {} } = useBlock();

    return (
      <ItemTitle
        ref={ref}
        className={clsx('ItemView-title', className)}
        data-testid="itemTitle"
        {...title}
        {...props}
      />
    );
  }
);
export default ItemTitleRoot;
