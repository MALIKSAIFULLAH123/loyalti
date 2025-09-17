/**
 * @type: skeleton
 * name: friend_sentRequest.itemView.mainCard.skeleton
 */
import {
  ButtonList,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Skeleton variant="avatar" width={80} height={80} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width={160} />
        </ItemTitle>
        <ButtonList>
          <Skeleton variant="button" width="50%" height={32} />
        </ButtonList>
      </ItemText>
    </ItemView>
  );
}
