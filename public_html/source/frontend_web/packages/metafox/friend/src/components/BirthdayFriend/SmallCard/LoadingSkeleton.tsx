/**
 * @type: skeleton
 * name: friend.itemView.birthdaySmallCard.skeleton
 */
import { useBlock } from '@metafox/layout';
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const { itemProps: { media } = {} } = useBlock();
  const size = Number.parseInt(media?.width);

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="circular" width={size} height={size} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" />
        </ItemTitle>
        <ItemSummary>
          <Skeleton variant="text" width="120px" />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
