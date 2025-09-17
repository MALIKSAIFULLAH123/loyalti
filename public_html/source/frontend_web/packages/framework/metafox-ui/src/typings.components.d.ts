import {
  ComponentsOverrides,
  ComponentsProps,
  ComponentsVariants
} from '@mui/material/styles';
import '@mui/material/styles/components';

declare module '@mui/material/styles/components' {
  interface Components {
    MuiTruncateText?: {
      defaultProps?: ComponentsProps['MuiTruncateText'];
    };
    MuiItemView?: {
      defaultProps?: ComponentsProps['MuiItemView'];
      styleOverrides?: ComponentsOverrides['MuiItemView'];
      variants?: ComponentsVariants['MuiItemView'];
    };
  }
}
