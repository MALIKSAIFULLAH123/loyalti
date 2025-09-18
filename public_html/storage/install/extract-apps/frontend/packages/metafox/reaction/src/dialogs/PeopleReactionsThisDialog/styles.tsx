import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      dialogContent: {
        height: 320,
        padding: theme.spacing(0),
        flex: '1 1 auto',
        overflowY: 'auto'
      },
      reactNumber: {
        marginBottom: '0 !important',
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        fontWeight: theme.typography.fontWeightBold,
        '&:first-of-type': {
          fontWeight: 'normal'
        }
      },
      customTabs: {
        '& .MuiTabs-flexContainer': {
          margin: theme.spacing(1, 1.5, 1, 1.5)
        },
        '& .MuiTab-root': {
          fontSize: theme.typography.pxToRem(13),
          minHeight: 'auto',
          borderRadius: theme.shape.borderRadius / 2,
          marginRight: '0.25em',
          padding: theme.spacing(0.5),
          '&:hover': {
            backgroundColor: theme.palette.action.hover
          }
        },
        '& .Mui-selected': {
          backgroundColor: theme.palette.action.selected
        },
        '& .MuiTab-wrapper': {
          textTransform: 'none',
          flexDirection: 'row',
          padding: theme.spacing(0.25, 1),
          '& img': {
            marginBottom: '0 !important',
            marginRight: theme.spacing(0.75),
            '& + $reactNumber': {
              fontWeight: 'bold'
            }
          }
        },
        '& .MuiTabs-indicator': {
          backgroundColor: 'transparent'
        }
      }
    }),
  { name: 'ReactionDialog' }
);
