/**
 * @type: skeleton
 * name: save_playlist.itemView.mainCard.skeleton
 */
import { Skeleton, Box } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <Box sx={{ width: '100%', mx: 2, mt: 1, mb: 2 }}>
      <Skeleton width="60%" height="20" variant="text" />
    </Box>
  );
}
