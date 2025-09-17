// Tell Material-UI what's the font-size on the html element.
// 16px is the default font-size used by browsers.
import { Theme } from '@mui/material';

const htmlFontSize = 16;

export default function pxToRem(theme: Theme) {
  return (size: number): string => {
    return `${
      (size / htmlFontSize) *
      ((theme.typography.fontSize / 14) * (htmlFontSize / theme.typography.htmlFontSize))
    }rem`;
  };
}
