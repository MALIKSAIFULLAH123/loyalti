import { useGlobal } from '@metafox/framework';
import { Box, FormControlLabel, Radio } from '@mui/material';
import * as React from 'react';
import LayoutSection from '../LayoutSection/LayoutSection';

export default function LayoutPreview({
  pageName,
  pageSize,
  selected,
  onChange
}) {
  const { layoutBackend } = useGlobal();
  const { header, footer, content } = layoutBackend.getTemplatePreviewConfig(
    pageName,
    pageSize
  );

  return (
    <div>
      <Box sx={{ textAlign: 'center', padding: 8 }}>
        <FormControlLabel
          checked={Boolean(selected)}
          onChange={onChange}
          value={pageName}
          control={<Radio />}
          label={pageName}
        />
      </Box>
      <Box sx={{ color: '#888' }}>
        <LayoutSection {...header} />
        <LayoutSection {...content} />
        <LayoutSection {...footer} />
      </Box>
    </div>
  );
}
