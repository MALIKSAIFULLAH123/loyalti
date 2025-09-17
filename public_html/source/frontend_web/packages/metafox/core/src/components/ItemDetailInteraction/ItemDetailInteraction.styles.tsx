import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      listingComment: {
        marginTop: theme.spacing(2)
      },
      actionButtonStaticsWrapper: {
        display: 'flex',
        alignItems: 'center',

        [theme.breakpoints.down('sm')]: {
          flexDirection: 'column-reverse'
        }
      },
      actionBtnStaticsBorderWrapper: {
        borderTop: 'solid 1px',
        borderTopColor: theme.palette.border?.secondary,
        borderBottom: 'solid 1px',
        borderBottomColor: theme.palette.border?.secondary
      },
      reactionWrapper: {
        '& .MuiFeedCommentBlock-itemOuter': {
          border: 'none'
        },
        width: '100%'
      },
      feedStatisticWrapper: {
        flex: 1,
        display: 'flex',
        justifyContent: 'flex-end',
        width: '100%',
        fontWeight: theme.typography.fontWeightBold,
        padding: theme.spacing(1, 0),
        borderBottom: 'solid 1px',
        borderBottomColor: theme.palette.border?.secondary
      },
      contentWrapper: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        borderTop: theme.mixins.border('secondary'),
        '& .MuiFeedCommentBlock-root': {
          flexDirection: 'row',
          '& > div': {
            borderTop: 'none'
          }
        }
      },
      divider: {
        backgroundColor: `${theme.palette.background.default} !important`
      }
    }),
  { name: 'ItemDetailInteraction' }
);

export default useStyles;
