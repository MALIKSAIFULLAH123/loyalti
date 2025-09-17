import { styled } from '@mui/material/styles';

const Wrapper = styled('div', {
  name: 'SmartDataGrid',
  slot: 'Wrapper'
})(({ theme }) => ({
  backgroundColor: theme.palette.background.paper,
  borderRadius: theme.shape.borderRadius,
  maxWidth: '100%',
  overflow: 'auto'
}));

export default Wrapper;
