/**
 * @type: skeleton
 * name: reaction.itemView.reactedUser.skeleton
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid={''}>
      <ItemMedia>
        <Skeleton variant="avatar" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width={'100%'} />
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
}
