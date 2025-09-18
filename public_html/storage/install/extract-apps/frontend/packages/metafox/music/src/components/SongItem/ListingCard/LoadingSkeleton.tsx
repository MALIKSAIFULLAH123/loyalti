/**
 * @type: skeleton
 * name: music_song.itemView.listingCard.skeleton
 */

import { Skeleton, TableRow, TableCell } from '@mui/material';
import * as React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <TableRow>
      <TableCell width={'50px'}>
        <Skeleton />
      </TableCell>
      <TableCell width={'80%'}>
        <Skeleton />
      </TableCell>
      <TableCell>
        <Skeleton />
      </TableCell>
      <TableCell>
        <Skeleton />
      </TableCell>
    </TableRow>
  );
}
