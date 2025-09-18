/**
 * @type: skeleton
 * name: sevent.itemView.userCard.skeleton
 */

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
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} style={{ margin: '0 16px 10px', 
      display: 'flex', gap: '10px;', alignItems: 'center' }}>
      <ItemMedia>
        <Skeleton variant="avatar" width={80} height={80} />
      </ItemMedia>
      <ItemText style={{ paddingLeft: '8px' }}>
        <ItemTitle>
          <Skeleton variant="text" width={'100%'} />
        </ItemTitle>
        <ItemTitle>
          <Skeleton variant="text" width={'100%'} />
        </ItemTitle>
      </ItemText>
  </ItemView>
  );
}
