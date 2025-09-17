import { Theme } from '@mui/material';
import { CSSProperties } from 'react';

export default function truncateText(theme: Theme) {
  return (
    fz: string,
    lh: number,
    lines: number,
    fixHeight: boolean
  ): CSSProperties => {
    return {
      fontSize: fz,
      lineHeight: lh,
      display: '-webkit-box',
      padding: '0',
      overflow: 'hidden',
      maxWidth: '100%',
      whiteSpace: 'normal',
      textOverflow: 'ellipsis',
      WebkitBoxOrient: 'vertical',
      WebkitLineClamp: lines,
      [`${
        fixHeight ? 'height' : 'maxHeight'
      }`]: `calc(${lh} * ${fz} * ${lines})`
    };
  };
}
