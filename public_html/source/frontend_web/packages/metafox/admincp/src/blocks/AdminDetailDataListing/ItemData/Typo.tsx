/**
 * @type: ui
 * name: acp.detail.ui.typo
 */

import { Typography } from '@mui/material';
import React from 'react';

const TypoHtml = data => {
  const { value, props } = data;

  return (
    <Typography {...props}>
      <div dangerouslySetInnerHTML={{ __html: value }} />
    </Typography>
  );
};

export default TypoHtml;
