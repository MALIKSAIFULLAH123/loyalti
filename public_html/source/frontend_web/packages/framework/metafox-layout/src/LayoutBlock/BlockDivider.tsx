import clsx from 'clsx';
import React from 'react';
import { styled } from '@mui/material';

const Divider = styled('div', {
  name: 'LayoutBlock',
  slot: 'Divider',
  overridesResolver(props, styles) {
    return [styles.divider];
  }
})(({ theme }) => ({}));

export default function BlockDivider({ variant }: { variant?: string }) {
  if (!variant || variant === 'none') return null;

  return <Divider className={clsx(`blockDivider-${variant}`)} />;
}
