import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      table: {},
      selectedValue: {},
      selectedLabel: {},
      selectedIcon: {},
      optionIcon: { fontSize: '1rem !important' },
      optionButton: { width: 32, height: 32 },
      resizeObserver: {
        position: 'absolute',
        left: 0,
        top: 0,
        bottom: 0,
        width: 0,
        zIndex: -1
      },
      panelHeader: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: '16px 0 8px 16px',
        borderBottom: '1px solid rgb(240, 240, 240)'
      },
      panelFooter: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'flex-end',
        minHeight: '56px'
      },
      'row-root': {
        display: 'flex',
        flexDirection: 'row',
        flexWrap: 'nowrap',
        position: 'relative',
        width: '100%',
        borderBottom: theme.mixins.border('secondary'),
        paddingRight: theme.spacing(0.5),
        paddingLeft: theme.spacing(0.5)
      },
      'row-root-body': {
        '&:hover': {
          background: theme.palette.action.hover
        }
      },
      'row-root-selected': {
        backgroundColor: theme.palette.action.selected
      },
      'row-root-disabled': {
        backgroundColor: theme.palette.action.disabledBackground
      },
      'cell-root': {
        display: 'block',
        overflow: 'hidden',
        whiteSpace: 'nowrap',
        textOverflow: 'ellipsis',
        padding: '0 8px'
      },
      'cell-head-root': {
        fontWeight: 'bold'
      },
      'cell-right': {
        textAlign: 'right'
      },
      'cell-center': {
        textAlign: 'left'
      },
      'cell-dense': {
        padding: '0 4px'
      },
      'cell-multiline': {
        whiteSpace: 'normal'
      }
    }),
  {
    name: 'SmartDataGrid'
  }
);
