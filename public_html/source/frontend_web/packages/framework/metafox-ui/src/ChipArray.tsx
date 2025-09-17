import { Box, BoxProps } from '@mui/material';
import { styled } from '@mui/material/styles';

const ChipArray = styled(Box, {
  name: 'MuiChipArray',
  slot: 'Root'
})<BoxProps>({
  display: 'flex',
  flexDirection: 'row',
  flexWrap: 'wrap',
  '& .MuiChip-root': {
    marginRight: 8,
    marginTop: 8
  }
});

export default ChipArray;
