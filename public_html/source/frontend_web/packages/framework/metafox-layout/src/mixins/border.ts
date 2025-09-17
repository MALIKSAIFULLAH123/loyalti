import { Theme } from '@mui/material';
import { CSSProperties } from 'react';
import { get } from 'lodash';

export default function border(theme: Theme) {
  return (
    borderColor: 'primary' | 'secondary' | 'divider',
    borderWidth: number = 1,
    borderStyle: CSSProperties['borderStyle'] = 'solid'
  ): CSSProperties['border'] => {
    return `${borderWidth}px ${borderStyle} ${
      theme.palette.border[borderColor] || get(theme.palette, borderColor)
    }`;
  };
}
