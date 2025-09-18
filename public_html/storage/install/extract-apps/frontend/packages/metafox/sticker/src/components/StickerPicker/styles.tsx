import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      paperRoot: {
        width: 272,
        height: 280,
        display: 'flex',
        flexDirection: 'column',
        zIndex: 9999,
        backgroundColor: theme.palette.background.paper,
        paddingTop: 8
      },
      paperContent: {
        flex: 1,
        overflowY: 'hidden'
      },
      paperFooter: {
        height: 32
      },
      listRoot: {},
      listTitle: {
        padding: theme.spacing(1, 1, 0, 1)
      },
      listContent: {
        listStyle: 'none none outside',
        margin: 4,
        padding: 0
      },
      listItemRoot: {
        display: 'inline-flex',
        margin: 5,
        width: 56,
        height: 56
      },
      tabRoot: {
        display: 'flex',
        flexDirection: 'row',
        borderTop: '1px solid',
        borderColor: theme.palette.border?.secondary
      },
      tabItem: {
        height: 40,
        width: 40,
        fontSize: 16,
        padding: theme.spacing(0, 1, 0, 1),
        alignItems: 'center',
        justifyContent: 'center',
        display: 'flex',
        borderTop: '2px solid',
        borderColor: 'transparent'
      },
      tabItemIcon: {
        fontSize: 18
      },
      tabItemImg: {
        height: 24,
        maxWidth: 32
      },
      tabItemActive: {
        borderColor: theme.palette.primary.main
      },
      listItemImg: {
        maxWidth: 56,
        maxHeight: 56
      }
    }),
  { name: 'EmojiPicker' }
);
