import { Box, styled } from '@mui/material';

const GridBody = styled(Box, {
  name: 'SmartDataGrid',
  slot: 'GridBody'
})(({ theme }) => ({
  position: 'relative',
  backgroundColor: theme.palette.background.paper
}));

export default GridBody;
