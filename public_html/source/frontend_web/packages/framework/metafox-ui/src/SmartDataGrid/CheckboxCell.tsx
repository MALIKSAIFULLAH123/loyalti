import { TableCell } from '@mui/material';
import { withStyles } from '@mui/styles';

export default withStyles(
  {
    head: {
      paddingLeft: '4px',
      paddingRight: '8px',
      borderBottom: '1px solid rgb(240, 240, 240)'
    },
    body: {
      paddingLeft: '4px',
      paddingRight: '8px',
      borderBottom: '1px solid rgb(240, 240, 240)'
    },
    footer: {
      paddingLeft: '4px',
      paddingRight: '8px'
    }
  },
  {
    name: 'CheckboxCell'
  }
)(TableCell);
