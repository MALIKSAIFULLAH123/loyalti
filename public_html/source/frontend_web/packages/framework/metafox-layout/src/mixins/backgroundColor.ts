import { Theme } from '@mui/material';
import { CSSProperties } from 'react';

export default function backgroundColor(theme: Theme) {
  return (
    backgroundColor: 'paper' | 'default'
  ): CSSProperties['backgroundColor'] => {
    return theme.palette.background[backgroundColor];
  };
}
