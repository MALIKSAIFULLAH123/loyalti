/**
 * @type: ui
 * name: dataGrid.cell.UrlPreviewCell
 */

import { styled, Box } from '@mui/material';
import { get } from 'lodash';
import React from 'react';
import { Image, LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';

const Wrapper = styled(Box, {
  name: 'WrapperMedia'
})(({ theme, maxWidth, maxHeight }) => ({
  display: 'block',
  '& img': {
    maxHeight,
    maxWidth
  }
}));

export default function BasicCell({ row, colDef }) {
  const { field, aspectRatio = 'auto', imageFit = 'contain' } = colDef || {};
  const previewData = get(row, field, null);

  if (!previewData) return null;

  const { url, file_type, file_name, image } = previewData || {};
  const isImage = file_type?.match('image/*');

  const imageSrc = getImageSrc(image, colDef?.sizePrefers || 'origin');

  const style = {};
  const ratio = aspectRatio.replace(':', '');

  return (
    <Box sx={{ width: '100%' }} pr={2}>
      <a href={url} target="_blank" rel="noopener noreferrer">
        <Wrapper color={'text.primary'}>
          {isImage ? (
            <Image
              src={imageSrc || url}
              style={style}
              aspectRatio={ratio}
              imageFit={imageFit}
            />
          ) : (
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <LineIcon sx={{ mr: 0.5 }} icon="ico-paperclip-alt" />
              {file_name}
            </Box>
          )}
        </Wrapper>
      </a>
    </Box>
  );
}
