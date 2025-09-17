/**
 * @type: skeleton
 * name: friend_pendingRequest.itemView.smallCard.skeleton
 */
import {
  ButtonList,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="avatar" width={56} height={56} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="80%" height={18} />
        </ItemTitle>
        <ButtonList variant="fillWidth" spacing="medium">
          <Skeleton variant="button" width="50%" height={32} sx={{ mr: 1 }} />
          <Skeleton variant="button" width="50%" height={32} />
        </ButtonList>
      </ItemText>
    </ItemView>
  );
}
