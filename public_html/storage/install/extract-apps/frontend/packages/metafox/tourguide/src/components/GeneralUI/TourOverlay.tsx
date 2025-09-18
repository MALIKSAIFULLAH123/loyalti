import { Box, styled } from '@mui/material';

const TourOverlay = styled(Box, { name: 'TourOverlay' })(({ theme }) => ({
  position: 'fixed',
  width: '100%',
  height: '100%',
  top: 0,
  left: 0,
  background: '#CFCFCF',
  opacity: 0.2,
  zIndex: 1300
}));

export default TourOverlay;
