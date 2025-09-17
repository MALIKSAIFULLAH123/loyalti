import { styled, Paper } from '@mui/material';

const name = 'RichtextEditorAction';

export const DropdownWrapper = styled(Paper, {
  name,
  slot: 'DropdownWrapper'
})(({ theme }) => ({
  zIndex: theme.zIndex.tooltip - 1,
  display: 'block',
  position: 'fixed',
  minHeight: '40px'
}));
