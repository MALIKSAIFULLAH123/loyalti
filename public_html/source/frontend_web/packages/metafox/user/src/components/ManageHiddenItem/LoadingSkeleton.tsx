/**
 * @type: skeleton
 * name: user.itemView.hiddenUser.skeleton
 */

import {
  ItemAction,
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
        <Skeleton variant="avatar" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="40%" />
        </ItemTitle>
      </ItemText>
      <ItemAction>
        <Skeleton variant="text" width={68} height={30} animation="wave" />
      </ItemAction>
    </ItemView>
  );
}
