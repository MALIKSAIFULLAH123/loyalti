/**
 * @type: ui
 * name: listInfo.as.Tag
 */
import { Box, Chip } from '@mui/material';
import { isArray } from 'lodash';
import React from 'react';

// todo moved this column to base size.
export default function DateBasic(props) {
  const { value } = props;

  if (!value?.length || !isArray(value)) return null;

  return (
    <Box sx={{ display: 'flex', flexFlow: 'wrap' }}>
      {value.map(x => (
        <Box key={x} mr={1}>
          <Chip size="small" label={x} variant="filled" />
        </Box>
      ))}
    </Box>
  );
}
