import { Link, useGlobal } from '@metafox/framework';
import { GroupItemShape } from '@metafox/group';
import { Flag, Image, LineIcon, Statistic, TruncateText } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, Button, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import clsx from 'clsx';
import * as React from 'react';

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
        overflow: 'hidden',
        padding: theme.spacing(2)
      },
      grid: {
        '& $itemOuter': {
          flexDirection: 'column',
          '$ $media': {
            width: '100%'
          }
        }
      },
      list: {
        '& $itemOuter': {
          flexDirection: 'row'
        },
        '& $wrapperInfoFlag': {
          marginTop: 'auto'
        },
        '& $media': {
          marginRight: theme.spacing(2)
        }
      },
      media: {
        width: '120px'
      },
      title: {
        '& a': {
          color: theme.palette.text.primary
        }
      },
      statistic: {
        display: 'flex',
        flexFlow: 'wrap',
        color: theme.palette.text.secondary
      },
      itemInner: {
        flex: 1,
        minWidth: 0,
        display: 'flex',
        flexDirection: 'column'
      },
      category: {
        '&:before': {
          content: '"."',
          margin: '0 4px'
        }
      },
      price: {},
      flagWrapper: {
        marginLeft: 'auto'
      },
      highlightSubInfo: {},
      btn: {
        marginRight: theme.spacing(1)
      },
      wrapperInfoFlag: {
        display: 'block'
      }
    }),
  { name: 'MuiFeedEmbedPageListt' }
);

const EmbedGroup = ({ item }: { item: GroupItemShape }) => {
  const classes = useStyles();
  const { i18n } = useGlobal();

  if (!item) {
    return null;
  }

  const image = getImageSrc(item.image);

  return (
    <div className={clsx(classes.item, classes.grid)}>
      <div className={classes.itemOuter}>
        {image && (
          <div className={classes.media}>
            <Image
              shape={'circle'}
              link={item.link}
              src={image}
              aspectRatio="fixed"
            />
          </div>
        )}
        <div className={classes.itemInner}>
          <Box mb={1} fontWeight="bold" className={classes.title}>
            <TruncateText lines={1} variant="h4">
              <Link color="inherit" to={item.link}>
                {item.title}
              </Link>
            </TruncateText>
          </Box>
          <Box className={classes.statistic}>
            <Statistic values={item.statistic} display="total_view" />
            {item.summary && (
              <span className={classes.category}>{item.summary}</span>
            )}
          </Box>
          <Box
            className={classes.wrapperInfoFlag}
            display="flex"
            justifyContent="space-between"
            alignItems="flex-end"
            mt={2.5}
          >
            <Box display="flex" alignItems="center">
              <Button
                size="small"
                variant="outlined"
                color="primary"
                startIcon={<LineIcon icon={'ico-thumbup-o'} />}
              >
                {i18n.formatMessage({ id: 'like' })}
              </Button>
            </Box>
            <div className={classes.flagWrapper}>
              {item.is_featured ? (
                <Flag
                  data-testid="featured"
                  type={'is_featured'}
                  showTitleMobile={false}
                />
              ) : null}
            </div>
          </Box>
        </div>
      </div>
    </div>
  );
};

export default EmbedGroup;

EmbedGroup.LoadingSkeleton = () => null;

EmbedGroup.displayName = 'GroupItem_EmbedCard';
