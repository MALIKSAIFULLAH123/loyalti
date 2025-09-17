import { Link } from '@metafox/framework';
import {
  Flag,
  ImageRatio,
  ItemShape,
  Statistic,
  TruncateText
} from '@metafox/ui';
import { Box, Theme, useTheme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';
import FeedEmbedCardBlock from '../components/FeedEmbedCardBlock';

type Props = {
  title?: string;
  description?: string;
  link?: string;
  host?: string;
  image?: string;
  widthImage?: string;
  mediaRatio?: ImageRatio;
  displayStatistic?: string;
  maxLinesTitle?: 1 | 2 | 3;
  maxLinesDescription?: 1 | 2 | 3;
  variant?: 'grid' | 'list';
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
          color: theme.palette.text.primary
        }
      },
      description: {
        color: theme.palette.text.secondary,
        '& p': {
          margin: 0
        }
      },
      hostLink: {
        color: theme.palette.text.secondary
      },
      subInfo: {},
      itemInner: {
        flex: 1,
        minWidth: 0,
        padding: theme.spacing(3),
        display: 'flex',
        flexDirection: 'column'
      },
      wrapperInfoFlag: {
        marginTop: 'auto'
      },
      flagWrapper: {
        marginLeft: 'auto'
      }
    }),
  { name: 'MuiFeedArticleTemplate' }
);

const FeedArticleTemplate = (props: Props) => {
  const {
    image,
    title,
    maxLinesTitle = 2,
    description,
    maxLinesDescription = 3,
    link,
    host,
    widthImage = '200px',
    mediaRatio = '11',
    statistic,
    displayStatistic = 'total_view',
    is_featured,
    variant = 'list'
  } = props;

  const classes = useStyles({ mediaRatio });
  const theme = useTheme();

  return (
    <FeedEmbedCardBlock image={image} variant={variant} widthImage={widthImage}>
      <div className={classes.itemInner}>
        {link ? (
          <Box mb={1} fontWeight={600} className={classes.title}>
            <Link to={link}>
              <TruncateText variant="h4" lines={maxLinesTitle}>
                {title}
              </TruncateText>
            </Link>
          </Box>
        ) : (
          <Box className={classes.title} mb={1} fontWeight={600}>
            <TruncateText variant="h4" lines={2}>
              {title}
            </TruncateText>
          </Box>
        )}
        <Box className={classes.description} mb={2}>
          <TruncateText variant={'body1'} lines={maxLinesDescription}>
            <div dangerouslySetInnerHTML={{ __html: description }} />
          </TruncateText>
        </Box>
        <Box
          className={classes.wrapperInfoFlag}
          display="flex"
          justifyContent="space-between"
          alignItems="flex-end"
        >
          <div>
            {link ? (
              <Box
                className={classes.subInfo}
                color={theme.palette.text.secondary}
                fontWeight={600}
              >
                <Link to={link} className={classes.hostLink}>
                  {host}
                </Link>
              </Box>
            ) : (
              <Box
                className={classes.subInfo}
                fontSize="h5.fontSize"
                color={theme.palette.text.secondary}
                fontWeight={600}
              >
                {host}
              </Box>
            )}
            <Statistic
              values={statistic}
              display={displayStatistic}
              fontStyle={'minor'}
            />
          </div>
          <div className={classes.flagWrapper}>
            {is_featured && <Flag type={'is_featured'} />}
          </div>
        </Box>
      </div>
    </FeedEmbedCardBlock>
  );
};

export default FeedArticleTemplate;
