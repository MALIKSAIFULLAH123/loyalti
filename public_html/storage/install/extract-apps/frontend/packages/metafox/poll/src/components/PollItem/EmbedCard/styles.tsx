import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        position: 'relative',
        padding: theme.spacing(2),
        width: '100%'
      },
      title: {
        marginBottom: theme.spacing(1)
      },
      description: {
        marginBottom: theme.spacing(1)
      },
      answerWrapper: {
        marginTop: theme.spacing(1)
      },
      actions: {
        marginTop: theme.spacing(2)
      },
      actionButton: {
        marginRight: theme.spacing(1)
      },
      voteStatistic: {
        marginTop: theme.spacing(2),
        color: theme.palette.text.secondary,
        fontSize: theme.mixins.pxToRem(13),
        fontWeight: 600,
        display: 'flex'
      },
      totalVote: {
        marginRight: theme.spacing(2)
      },
      activeTotalVote: {
        marginRight: theme.spacing(2),
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.primary.main,
        fontWeight: 600,
        cursor: 'pointer'
      },
      formContainer: {
        marginTop: theme.spacing(1)
      }
    }),
  { name: 'MuiFeedPollTemplate' }
);
