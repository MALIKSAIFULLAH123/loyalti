/**
 * @type: skeleton
 * name: story.itemView.mutedItem.skeleton
 */
import { ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import { styled } from '@mui/material/styles';

const name = 'mutedItemSkeleton';

const WrapperButtonInline = styled('div', {
  name,
  slot: 'wrapperButtonInline',
  overridesResolver(props, styles) {
    return [styles.wrapperButtonInline];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'stretch',
  [theme.breakpoints.down('sm')]: {
    flexFlow: 'row wrap',
    padding: theme.spacing(0.5, 0),
    justifyContent: 'flex-start'
  }
}));

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <Skeleton variant="circular" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <Skeleton variant="text" width={120} />
      </ItemText>
      <WrapperButtonInline>
        <Skeleton variant="button" width={70} height={26} />
      </WrapperButtonInline>
    </ItemView>
  );
}
