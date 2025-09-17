import { Statistic, StatisticProps } from '@metafox/ui';
import { Box, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {}
    }),
  { name: 'MuiFeedEmbedStats' }
);

export default function FeedEmbedStats(props: StatisticProps) {
  const classes = useStyles();

  return (
    <Box
      className={classes.root}
      display="flex"
      justifyContent="space-between"
      alignItems="flex-end"
    >
      <Statistic {...props} />
    </Box>
  );
}
