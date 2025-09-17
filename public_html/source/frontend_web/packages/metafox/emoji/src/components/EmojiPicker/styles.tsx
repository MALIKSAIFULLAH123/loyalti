import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      paperContent: {
        height: 250
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
        width: 24,
        height: 24,
        margin: 2
      },
      listItemText: {
        fontSize: '24px'
      },
      tabRoot: {
        display: 'flex',
        flexDirection: 'row',
        borderTop: theme.mixins.border('secondary')
      },
      tabItem: {
        height: 32,
        fontSize: 16,
        padding: theme.spacing(0, 1, 0, 1),
        alignItems: 'center',
        display: 'flex',
        borderTop: '2px solid',
        borderColor: 'transparent'
      },
      tabItemActive: {
        borderColor: theme.palette.primary.main
      }
    }),
  { name: 'EmojiPicker' }
);
