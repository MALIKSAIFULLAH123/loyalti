import { useGlobal } from '@metafox/framework';
import { Grid, Box } from '@mui/material';
import * as React from 'react';

const CustomItem = props => {
  const { item, isShape } = props;
  const { jsxBackend } = useGlobal();
  const ref = React.useRef();

  const Item = jsxBackend.get(item.component);

  if (!Item) return null;

  if (isShape) {
    return (
      <Grid
        ref={ref}
        item
        key="itemAddPhotoAlbum"
        style={{ width: '100%', paddingTop: 0 }}
      >
        <Box sx={{ width: '125px', height: '125px' }} />
      </Grid>
    );
  }

  return (
    <Grid
      ref={ref}
      item
      key="itemAddPhotoAlbum"
      style={{ width: '100%', paddingTop: 0 }}
    >
      <Item size="large" />
    </Grid>
  );
};

export default CustomItem;
