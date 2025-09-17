/**
 * @type: ui
 * name: appbar.item.divider
 * chunkName: appbarAs
 */
import { Divider } from '@mui/material';
import React from 'react';

export default function DividerItem({ variant, light, classes }: any) {
  return (
    <Divider
      orientation="vertical"
      variant="middle"
      light={light}
      className={classes.divider}
    />
  );
}
