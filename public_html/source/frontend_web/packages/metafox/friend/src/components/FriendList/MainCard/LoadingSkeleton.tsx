/**
 * @type: skeleton
 * name: friend_list.itemView.mainCard.skeleton
 */
import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <LineIcon icon="ico-list-bullet" color="textPrimary" />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="50%" height="20" />
        </ItemTitle>
        <ItemSummary>
          <Skeleton variant="text" width="30%" height="20" />
        </ItemSummary>
      </ItemText>
    </ItemView>
  );
}
