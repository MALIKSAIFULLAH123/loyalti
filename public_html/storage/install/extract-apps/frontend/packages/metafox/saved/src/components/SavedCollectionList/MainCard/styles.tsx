import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        paddingBottom: theme.spacing(2),
        position: 'relative',
        '&:after': {
          content: '""',
          position: 'absolute',
          left: theme.spacing(-2),
          right: theme.spacing(-2),
          bottom: 0,
          height: 1,
          display: 'block',
          borderBottomStyle: 'solid',
          borderBottomWidth: 1,
          borderBottomColor: theme.palette.text.disabled
        }
      },
      actionMenu: {
        position: 'absolute !important',
        top: 0,
        right: 0
      },
      itemOuter: {},
      itemInner: {
        display: 'flex'
      },
      media: {
        display: 'block',
        width: 112,
        minWidth: 112,
        height: 112,
        minHeight: 112,
        objectFit: 'cover',
        marginRight: theme.spacing(2)
      },
      content: {
        flexGrow: 1,
        minWidth: 0,
        paddingRight: theme.spacing(5)
      },
      title: {
        fontSize: theme.mixins.pxToRem(18),
        lineHeight: 1.33,
        fontWeight: theme.typography.fontWeightBold,
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis',
        overflow: 'hidden',
        '& a': {
          color: theme.palette.text.primary
        }
      },
      total: {
        marginTop: theme.spacing(1),
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        lineHeight: 1.33,
        display: 'flex'
      }
    }),
  {
    name: 'SavedCollectionItemMainCard'
  }
);
