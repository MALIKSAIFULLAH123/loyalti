/**
 * @type: skeleton
 * name: pages.itemView.smallCard.skeleton
 */
import { ItemMedia, ItemSummary, ItemText, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="avatar" height={72} width={72} />
      </ItemMedia>
      <ItemText>
        <ItemSummary>
          <Skeleton variant="text" width="50%" />
          <Skeleton variant="text" width="50%" />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
