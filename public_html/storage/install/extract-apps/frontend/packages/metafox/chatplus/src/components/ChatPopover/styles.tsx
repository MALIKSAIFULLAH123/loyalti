import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      itemRoot: {
        paddingLeft: '16px',
        paddingRight: '16px',
        cursor: 'pointer',
        transition: 'background-color 300ms ease'
      },
      itemRootUnseen: {
        background: '#ddecf9'
      },
      itemWrapper: {
        display: 'flex',
        padding: '8px 0',
        color: '#555555',
        fontSize: '14px',
        cursor: 'pointer',
        borderBottom: '1px solid #dcdcdc'
      },
      itemMedia: {
        marginRight: theme.spacing(1)
      },
      itemInner: { flex: 1, minWidth: '0' },
      temRowTitleWrapper: {
        display: 'flex',
        justifyContent: 'space-between',
        marginBottom: '4px'
      },
      itemTitle: {
        display: 'flex',
        alignItems: 'center',
        fontWeight: 'bold',
        fontSize: '14px',
        lineHeight: '18px',
        flex: 1,
        minWidth: 0,
        maxWidth: '100%',
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis',
        overflow: 'hidden'
      },
      itemSubtitle: { color: theme.palette.text.secondary },
      itemMsgText: {
        color: theme.palette.text.secondary,
        maxWidth: '100%',
        display: 'block',
        overflow: 'hidden',
        padding: 0,
        fontSize: '12px',
        textOverflow: 'ellipsis',
        lineHeight: '16px',
        wordBreak: 'break-word',
        wordWrap: 'break-word',
        height: '32px',
        whiteSpace: 'normal'
      },
      uiChatCircleStatus: {
        width: '8px',
        height: '8px',
        display: 'inline-block',
        border: '1px solid #a2a2a2',
        borderRadius: '100%',
        margin: '0 8px'
      },
      unread: {
        backgroundColor: '#2681d5',
        borderColor: '#2681d5'
      },
      buddyHeader: {
        paddingLeft: '16px',
        paddingRight: '16px',
        paddingTop: '16px',
        marginBottom: '4px',
        fontWeight: 'bold'
      },
      root: {},
      popper: {
        boxShadow: theme.shadows[20],
        zIndex: theme.zIndex.snackbar,
        width: '300px',
        backgroundColor: theme.palette.background.paper,
        borderRadius: theme.shape.borderRadius,
        maxHeight: '70vh',
        marginTop: '14px',
        overflow: 'hidden'
      },
      header: {
        padding: theme.spacing(1.5, 2),
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      },
      title: {
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(18)
      },
      actionMenu: {
        '& .ico': {
          fontSize: theme.mixins.pxToRem(16)
        }
      }
    }),
  { name: 'ChatPopover' }
);
