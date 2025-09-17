import { Grid, GridProps } from '@mui/material';
import React from 'react';

export default function GridItem(props: GridProps) {
  return <Grid item {...props} />;
}
