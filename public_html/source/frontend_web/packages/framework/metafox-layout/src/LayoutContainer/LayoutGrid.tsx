/**
 * @type: ui
 * name: layout.layoutGrid
 * chunkName: boot
 */

import { useGlobal } from '@metafox/framework';
import { Grid } from '@mui/material';
import React from 'react';
import Container from './Container';

export default function LayoutGrid(props) {
  const { elements, sx, rootStyle } = props;

  const { jsxBackend } = useGlobal();

  if (!elements || !elements.length) {
    return null;
  }

  return (
    <Container sx={sx} {...rootStyle}>
      <Grid container columnSpacing={0} rowSpacing={0} wrap="wrap">
        {jsxBackend.render(elements)}
      </Grid>
    </Container>
  );
}
