import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      dialogContent: {
        height: 320,
        [theme.breakpoints.down('sm')] : {
          height: 'unset'
        }
      },
      reactNumber: {
        paddingLeft: theme.spacing(1),
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        fontWeight: theme.typography.fontWeightBold
      },
      customTabs: {
        borderBottom: theme.mixins.border('secondary'),
        '& .MuiTabs-scroller': {
          overflow: 'auto !important'
        },
        '& .MuiTabs-flexContainer': {
          margin: theme.spacing(1, 1.5, 1, 1.5),
          display: 'flex',
          flexDirection: 'column'
        },
        '& .MuiTab-root': {
          fontSize: theme.typography.pxToRem(13),
          minHeight: 'auto',
          minWidth: 0,
          borderRadius: theme.shape.borderRadius / 2,
          marginRight: theme.spacing(1),
          padding: theme.spacing(0.5),
          flexDirection: 'row',
          '&:hover': {
            backgroundColor: theme.palette.action.hover
          }
        },
        '& .Mui-selected': {
          backgroundColor: theme.palette.action.selected,
          color: `${theme.palette.text.secondary} !important`
        },
        '& .MuiTabs-indicator': {
          backgroundColor: 'transparent'
        }
      }
    }),
  { name: 'ReactionDialog' }
);
