/**
 * @type: ui
 * name: dataGrid.additionalSection.Basic
 */

import HtmlViewer from '@metafox/html-viewer';
import { Box, Typography } from '@mui/material';
import React from 'react';
import { ItemProps } from './type';

function DataGridAdditionalSection({ config, sx }: ItemProps) {
  const { title, description, titleProps, descriptionProps } = config;

  return (
    <Box sx={sx}>
      {title ? <Typography {...titleProps}>{title}</Typography> : null}
      {description ? (
        <Box {...descriptionProps}>
          <HtmlViewer disableNl2br html={description} />
        </Box>
      ) : null}
    </Box>
  );
}

export default DataGridAdditionalSection;
