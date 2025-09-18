/**
 * @type: skeleton
 * name: saved_collection_list.itemView.mainCard.skeleton
 */
import { ItemMedia, ItemText, ItemView, LineIcon } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="LoadingSkeleton">
      <ItemMedia>
        <LineIcon
          icon="ico-folder-alt-o"
          color="textPrimary"
          sx={{ fontSize: '1rem' }}
        />
      </ItemMedia>
      <ItemText>
        <Skeleton variant="text" width="50%" height="20" />
      </ItemText>
    </ItemView>
  );
}
