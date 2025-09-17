import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      itemPayment: {
        marginBottom: theme.spacing(2)
      },
      itemTitle: {
        fontSize: '18px',
        lineHeight: 1.6,
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.text.primary
      },
      itemDescription: {
        fontSize: theme.typography.body1.fontSize,
        lineHeight: 1.6,
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(0.5)
      },
      itemForm: {
        display: 'flex',
        flexWrap: 'wrap',
        marginTop: theme.spacing(1),
        marginLeft: theme.spacing(-1.5),
        marginRight: theme.spacing(-1.5)
      },
      itemInfo: {
        padding: theme.spacing(1.5),
        width: '50%'
      },
      input: {
        width: '100%'
      },
      buttomButton: {
        paddingTop: theme.spacing(2),
        borderTop: '1px solid',
        borderColor: theme.palette.border?.secondary
      },
      link: {
        display: 'block',
        marginTop: theme.spacing(1.5),
        fontSize: theme.typography.body1.fontSize
      }
    }),
  { name: 'PaymentSetting' }
);
