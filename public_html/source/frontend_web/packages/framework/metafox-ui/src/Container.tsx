import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import { Breakpoint } from '@mui/system';

export interface StyledContainerProps {
  maxWidth?: Breakpoint;
  gutter?: boolean;
}
const Container = styled(Box, {
  name: 'StyledContainer',
  shouldForwardProp: prop => prop !== 'maxWidth' && prop !== 'gutter'
})<StyledContainerProps>(({ theme, maxWidth, gutter }) => ({
  position: 'relative',
  width: '100%',
  marginLeft: 'auto',
  boxSizing: 'border-box',
  marginRight: 'auto',
  display: 'block', // Fix IE11 layout when used with main.
  ...(maxWidth &&
    (maxWidth !== 'xs'
      ? {
          [theme.breakpoints.up(maxWidth)]: {
            ...(gutter
              ? {
                  maxWidth:
                    theme.breakpoints.values[maxWidth] +
                    (theme.gutter || 0) * 2,
                  paddingLeft: theme.gutter,
                  paddingRight: theme.gutter
                }
              : {
                  maxWidth: theme.breakpoints.values[maxWidth]
                })
          }
        }
      : {
          [theme.breakpoints.up('xs')]: {
            maxWidth: Math.max(theme.breakpoints.values.xs, 444)
          }
        }))
}));

export default Container;

export * from '@mui/material/Container';
