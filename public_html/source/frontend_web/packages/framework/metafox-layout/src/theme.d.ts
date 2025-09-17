/* tslint:disable:unified-signatures */
import '@metafox/framework/types';
import '@mui/material/Button';
import '@mui/material/IconButton';
import '@mui/material/IconButton/IconButton';
import '@mui/material/Skeleton';
import '@mui/material/styles/createPalette';
import '@mui/material/styles/createTheme';
import { CSSProperties } from 'react';
import { ThemeLayoutSlotOptions, ThemeId } from './types';

declare module '@mui/material/Skeleton/Skeleton' {
  interface SkeletonPropsVariantOverrides {
    avatar: true;
    button: true;
    'icon-button': true;
  }
}

declare module '@mui/material/IconButton/IconButton' {
  interface IconButtonProps {
    variant?: 'outlined' | 'contained' | 'text';
    square?: boolean;
  }

  interface IconButtonPropsSizeOverrides {
    smaller: true;
    smallest: true;
  }
}

declare module '@mui/material/Button' {
  interface ButtonPropsVariantOverrides {}
  interface ButtonPropsColorOverrides {
    default: true;
  }
  interface ButtonPropsSizeOverrides {
    smaller: true;
    smallest: true;
  }
}
declare module '@metafox/framework/types' {
  interface UserPreferenceConfig {
    themeType?: ThemeType;
    themeId?: ThemeId;
  }
}

declare module '@mui/material/styles/createTheme' {
  interface ThemeOptions {
    themeId?: ThemeId;
    layoutSlot?: ThemeLayoutSlotOptions;
    fontFamily: CSSProperties['fontFamily'];
    MUIRichTextEditor: Record<string, CssProperties>;
    popoverWidth: { appbar: number };
  }

  interface Theme {
    themeId?: ThemeId;
    layoutSlot?: ThemeLayoutSlotOptions;
    fontFamily: CSSProperties['fontFamily'];
    blockDivider?: Record<string, any>;
    MUIRichTextEditor: Record<string, CssProperties>;
    popoverWidth: { appbar: number };
    gutter: number;
    appBarHeight?: { normal: number; fixed?: number };
    appBarMobileConfig?: { nav: number; brand: number; bottom: number };
  }
}

declare module '@mui/material/styles/createPalette' {
  interface TypeText {
    disabled?: CSSProperties['color'];
    hint?: CSSProperties['color'];
  }

  interface TypeBackground {
    auto: string;
    secondary: string;
  }

  interface PaletteOptions {
    default?: PaletteColorOptions;
  }

  interface PaletteColorOptions {
    lighter?: CSSProperties['color'];
  }

  interface Palette {
    border?: {
      primary: CSSProperties['color'];
      secondary: CSSProperties['color'];
    };
    // text?: {};
    // danger?: PaletteColor;
  }

  interface SimplePaletteColorOptions {
    lighter?: string;
    darker?: string;
  }

  interface PaletteOptions {
    border?: {
      primary: CSSProperties['color'];
      secondary: CSSProperties['color'];
      outlined: CSSProperties['color'];
    };
    danger?: PaletteColor;
  }
}

declare module '@mui/material/styles/createTypography' {
  interface Typography {
    fontWeightSemiBold: number;
  }
  interface TypographyOptions {
    fontWeightSemiBold: number;
  }
}
