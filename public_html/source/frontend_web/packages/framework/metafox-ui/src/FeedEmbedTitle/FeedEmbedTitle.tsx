import { Link, LinkProps } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { Box, Theme, TypographyVariant } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        '& a': {
          color: theme.palette.text.primary,
          '& h2': {
            fontWeight: theme.typography.fontWeightBold
          }
        }
      }
    }),
  { name: 'MuiFeedEmbedTitle' }
);

interface Props {
  title: string;
  variant?: TypographyVariant;
  linkProps?: LinkProps;
  lines?: 1 | 2 | 3;
}

export default function FeedEmbedTitle({
  title,
  linkProps,
  variant = 'h4',
  lines = 3
}: Props) {
  const classes = useStyles();

  return (
    <Box mb={1} className={classes.root}>
      <Link {...linkProps}>
        <TruncateText variant={variant} lines={lines}>
          {title}
        </TruncateText>
      </Link>
    </Box>
  );
}
