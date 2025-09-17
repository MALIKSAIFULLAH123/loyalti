import '@mui/material/styles/overrides';
import { ItemViewClassName } from './ItemView';

declare module '@mui/material/styles/overrides' {
  interface ComponentNameToClassKey {
    MuiItemView: keyof ItemViewClassName;
  }
}
