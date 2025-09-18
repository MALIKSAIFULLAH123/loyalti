/**
 * @type: skeleton
 * name: activitypoint.itemView.packageItem.skeleton
 */
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemView,
  ItemTitle
} from '@metafox/ui';
import { Skeleton, styled } from '@mui/material';
import * as React from 'react';

const ItemViewInner = styled(ItemView, { name: 'ItemViewInner' })(
  ({ theme }) => ({
    padding: theme.spacing(4)
  })
);

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemViewInner wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="rounded" width={100} height={100} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width={160} />
        </ItemTitle>
        <ItemSummary>
          <Skeleton width={200} />
        </ItemSummary>
      </ItemText>
    </ItemViewInner>
  );
}
