import '@metafox/framework/Manager';
import '@mui/material/styles/createMixins';
import '@mui/material/styles/createMuiTheme';

declare module '@mui/material/styles/createMixins' {
  interface Mixins {
    truncateText(
      fontSize: string,
      lineHeight: number,
      lines: number,
      fixHeight: boolean
    ): CSSProperties;

    border(
      borderColor: 'primary' | 'secondary',
      borderWidth?: number = 1,
      borderStyle?: CSSProperties['borderStyle'] = 'solid'
    ): CSSProperties['border'];

    backgroundColor(
      color: 'default' | 'paper'
    ): CSSProperties['backgroundColor'];

    pxToRem(size: number): string;
  }
}
