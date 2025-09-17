/**
 * @type: ui
 * name: photo.progress.loading
 */

import { CircularProgress } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import { SxProps } from '@mui/system';

const ProgressWrapper = styled('div', {
  name: 'MultipleUploadField',
  slot: 'icon',
  shouldForwardProp: prop => prop !== 'hide'
})(({ theme }) => ({
  background:
    theme.palette.mode === 'light'
      ? 'rgba(255, 255, 255, 0.6) !important'
      : 'rgba(0, 0, 0, 0.3) !important',
  position: 'absolute',
  left: 0,
  right: 0,
  bottom: 0,
  top: 0,
  zIndex: 2,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

type Props = {
  value: number;
  uploading: boolean;
  size?: string;
  sx?: SxProps;
};
export default function PhotoProgressUpload({
  value,
  uploading,
  size = '56px',
  sx
}: Props) {
  if (!uploading) return null;

  return (
    <ProgressWrapper sx={sx}>
      <CircularProgress
        sx={{
          width: `${size} !important`,
          height: `${size} !important`
        }}
        variant="determinate"
        value={value || 0}
      />
    </ProgressWrapper>
  );
}
