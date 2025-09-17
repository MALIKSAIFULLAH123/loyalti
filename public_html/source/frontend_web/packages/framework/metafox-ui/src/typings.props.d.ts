import '@mui/material/styles/props';
import { TruncateTextProps } from '.';
import { MuiItemViewProps } from './ItemView';

declare module '@mui/material/styles/props' {
  interface ComponentsPropsList {
    MuiItemView: MuiItemViewProps;
    MuiTruncateText: TruncateTextProps;
  }
}
