/**
 * @type: ui
 * name: layout.containerWithLiveView
 * chunkName: boot
 */
import { useGlobal } from '@metafox/framework';
import { Grid } from '@mui/material';
import React from 'react';
import { LayoutContainerProps } from '../types';
import Container from './Container';

function ContainerWithLiveView(props: LayoutContainerProps) {
  const { elements, containerName, rootStyle, sx, wrap = 'nowrap' } = props;

  const { jsxBackend } = useGlobal();

  if (!elements || !elements.length) {
    return null;
  }

  return (
    <Container
      sx={sx}
      {...rootStyle}
      data-testid={`container.${containerName}`}
    >
      <Grid container columnSpacing={0} rowSpacing={0} wrap={wrap}>
        {jsxBackend.render(elements)}
      </Grid>
    </Container>
  );
}

export default ContainerWithLiveView;
