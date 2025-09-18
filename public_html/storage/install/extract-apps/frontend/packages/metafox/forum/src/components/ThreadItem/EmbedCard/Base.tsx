import { Link, useGlobal } from '@metafox/framework';
import {
  ImageRatio,
  ItemShape,
  TruncateText,
  FeedEmbedCard,
  FeaturedFlag,
  SponsorFlag
} from '@metafox/ui';
import { Box, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';
import HtmlViewer from '@metafox/html-viewer';

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
  price?: string;
  displayStatistic?: string;
  maxLinesTitle?: 1 | 2 | 3;
  maxLinesDescription?: 1 | 2 | 3;
  highlightSubInfo?: string;
  variant?: 'grid' | 'list';
  isComment: boolean;
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
      itemInner: {
        flex: 1,
        minWidth: 0,
        padding: theme.spacing(2),
        display: 'flex',
        flexDirection: 'column'
      },
      totalView: {
        color: theme.palette.text.secondary
      },
      price: {
        fontWeight: theme.typography.fontWeightBold,
        color: theme.palette.warning.main
      },
      wrapperInfoFlag: {
        marginTop: 'auto'
      },
      flagWrapper: {
        marginLeft: 'auto'
      }
    }),
  { name: 'MuiFeedEmbedProductTemplate' }
);

const Base = (props: Props) => {
  const {
    title,
    maxLinesTitle = 2,
    short_description,
    maxLinesDescription = 3,
    link,
    is_featured,
    is_sponsored_feed,
    host,
    statistic,
    isComment,
    feed,
    item,
    isShared
  } = props;

  const classes = useStyles();
  const { i18n } = useGlobal();

  const linkParams = !host
    ? { to: link }
    : {
        to: { pathname: link },
        target: '_blank'
      };

  return (
    <FeedEmbedCard variant="grid" item={item} feed={feed} isShared={isShared}>
      <div className={classes.itemInner} data-testid="embedview">
        <Box className={classes.title} mb={1} fontWeight={500}>
          <Link {...linkParams} to={link} identityTracking={feed?._identity}>
            <TruncateText variant="h4" lines={maxLinesTitle}>
              {title}
            </TruncateText>
          </Link>
        </Box>
        {!isComment ? (
          <>
            <Box className={classes.description} mb={2}>
              <TruncateText variant={'body1'} lines={maxLinesDescription}>
                <HtmlViewer html={short_description || ''} />
              </TruncateText>
            </Box>
            <Box
              className={classes.wrapperInfoFlag}
              display="flex"
              justifyContent="space-between"
              alignItems="flex-end"
            >
              {statistic?.total_comment !== null ? (
                <div className={classes.totalView}>
                  {i18n.formatMessage(
                    { id: 'total_reply' },
                    { value: statistic?.total_comment }
                  )}
                </div>
              ) : null}
              <div className={classes.flagWrapper}>
                <FeaturedFlag value={is_featured} />
                <SponsorFlag value={is_sponsored_feed} item={props} />
              </div>
            </Box>
          </>
        ) : null}
      </div>
    </FeedEmbedCard>
  );
};

export default Base;
