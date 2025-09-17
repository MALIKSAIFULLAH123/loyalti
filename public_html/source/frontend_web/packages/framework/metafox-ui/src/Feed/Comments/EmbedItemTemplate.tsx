import { Link } from '@metafox/framework';
import { ImageRatio, ItemShape, TruncateText } from '@metafox/ui';
import { Box, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';
import FeedEmbedCardBlock from '../components/FeedEmbedCardBlock';

type Props = {
  title?: string;
  description?: string;
  short_description?: string;
  link?: string;
  host?: string;
  image?: string;
  widthImage?: string;
  heightImage?: string;
  mediaRatio?: ImageRatio;
  displayStatistic?: string;
  maxLinesTitle?: 1 | 2 | 3;
  maxLinesDescription?: 1 | 2 | 3;
  highlightSubInfo?: string;
  variant?: 'grid' | 'list';
  appName?: string;
} & ItemShape;

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      item: {
        display: 'block'
      },
      itemOuter: {
        display: 'flex',
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        backgroundColor: theme.mixins.backgroundColor('paper'),
        overflow: 'hidden'
      },
      title: {
        textDecoration: 'none',
        '& a': {
          color: theme.palette.text.primary,
          '& h2': {
            fontWeight: theme.typography.fontWeightBold
          }
        }
      },
      description: {
        color: theme.palette.text.secondary,
        '& p': {
          margin: 0
        },
        '& img': {
          maxWidth: '100%'
        }
      },
      subInfo: {},
      boxLink: {
        flex: 1,
        display: 'flex',
        '&:hover': {
          textDecoration: 'none!important'
        },
        overflow: 'hidden'
      },
      itemInner: {
        flex: 1,
        minWidth: 0,
        padding: theme.spacing(0, 2),
        display: 'flex',
        flexDirection: 'column',
        alignSelf: 'center'
      }
    }),
  { name: 'FeedEmbedComment' }
);

const FeedEmbedProductTemplate = (props: Props) => {
  const {
    title,
    maxLinesTitle = 1,
    short_description,
    description,
    maxLinesDescription = 2,
    link,
    host
  } = props;

  const classes = useStyles();

  const linkParams = !host
    ? { to: link }
    : {
        to: { pathname: link },
        target: '_blank'
      };

  return (
    <FeedEmbedCardBlock {...props}>
      <Link {...linkParams} className={classes.boxLink}>
        <Box className={classes.itemInner} data-testid="embedview">
          <Box className={classes.title} mb={1} fontWeight={600}>
            <TruncateText variant="h4" lines={maxLinesTitle}>
              {title}
            </TruncateText>
          </Box>
          <Box className={classes.description}>
            <TruncateText variant={'body1'} lines={maxLinesDescription}>
              {short_description ?? description}
            </TruncateText>
          </Box>
        </Box>
      </Link>
    </FeedEmbedCardBlock>
  );
};

export default FeedEmbedProductTemplate;
