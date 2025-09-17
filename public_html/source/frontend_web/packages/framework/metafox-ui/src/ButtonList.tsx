import { Box, BoxProps } from '@mui/material';
import { styled } from '@mui/material/styles';

type Props = BoxProps & {
  variant?: 'fillFirst' | 'fullWidth' | 'fillWidth';
  spacing?: 'medium' | 'none' | 'small' | 'large';
  fullWidth?: boolean;
};

const ButtonList = styled(
  Box,
  {}
)<Props>(({ theme, spacing, variant, fullWidth }) => ({
  display: 'flex',
  '& button + button': {
    marginLeft: theme.spacing(1)
  },
  ...(variant === 'fillFirst' && {
    flex: 1,
    '& button:nth-of-type(1)': {
      flex: 1
    }
  }),
  ...(variant === 'fullWidth' && {
    flex: 1
  }),
  ...(variant === 'fillWidth' && {
    '& button': {
      flexGrow: 1
    }
  }),
  ...(spacing === 'small' && {
    marginTop: theme.spacing(1)
  }),
  ...(spacing === 'medium' && {
    marginTop: theme.spacing(1.5)
  }),
  ...(spacing === 'large' && {
    margin: theme.spacing(1.5)
  })
}));

export default ButtonList;
