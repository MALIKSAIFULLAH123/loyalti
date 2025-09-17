import * as React from 'react';
import { Box, Typography } from '@mui/material';

const DropDownInnerItem = props => {
  const { icon, label } = props;

  return (
    <Box sx={{ display: 'flex' }}>
      {icon}
      <Typography sx={{ flex: 1, minWidth: 0, display: 'inline-flex' }}>
        {label}
      </Typography>
    </Box>
  );
};

export default DropDownInnerItem;
