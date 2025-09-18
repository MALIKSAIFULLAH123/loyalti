import { Link, useGlobal } from '@metafox/framework';
import { PagesItemProps, PagesItemShape } from '@metafox/pages';
import { mappingRelationship } from '@metafox/pages/utils';
import {
  FeedEmbedCardProps,
  ItemMedia,
  LineIcon,
  Statistic,
  TruncateText,
  UserAvatar,
  SponsorFlag,
  PendingFlag,
  FeaturedIcon
} from '@metafox/ui';
import { Box, Button, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import clsx from 'clsx';
import React from 'react';

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
        width: 120
      },
      title: {
        display: 'flex',
        alignItems: 'center',
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
        flexDirection: 'column',
        marginLeft: theme.spacing(2),
        justifyContent: 'center'
      },
      category: {
        '&:before': {
          content: '"•"',
          margin: theme.spacing(0, 0.5)
        }
      },
      wrapperInfoFlag: {
        display: 'block'
      }
    }),
  { name: 'MuiFeedEmbedPageList' }
);

type EmbedPageItemProps = { item: PagesItemShape } & FeedEmbedCardProps &
  PagesItemProps;

export default function EmbedPageItem({
  item,
  variant,
  actions,
  // isShare in composer feed
  isShared
}: EmbedPageItemProps) {
  const classes = useStyles();
  const { i18n, useLoggedIn } = useGlobal();
  const isLogged = useLoggedIn();

  if (!item) return null;

  const { title, statistic, link, summary, extra, is_liked, is_owner } = item;

  const reactButton = mappingRelationship(
    is_owner,
    is_liked,
    extra?.can_unlike,
    extra?.can_like,
    actions,
    isShared
  );

  return (
    <div className={clsx(classes.item, classes[variant])}>
      <div className={classes.itemOuter}>
        <div className={classes.media}>
          <ItemMedia>
            <UserAvatar user={item} size={120} />
          </ItemMedia>
        </div>
        <div className={classes.itemInner}>
          {item.is_sponsor || item.is_pending ? (
            <Box mb={0.5}>
              <SponsorFlag
                value={item.is_sponsor}
                variant="itemView"
                item={item}
              />
              <PendingFlag variant="itemView" value={item.is_pending} />
            </Box>
          ) : null}
          <Box mb={1} fontWeight="bold" className={classes.title}>
            <Link to={link} sx={{ minWidth: 0, maxWidth: '100%' }}>
              <TruncateText variant="h4" lines={1} fontWeight={700}>
                {title}
              </TruncateText>
            </Link>
            <FeaturedIcon icon="ico-check-circle" value={item?.is_featured} />
          </Box>
          <Box className={classes.statistic}>
            <Statistic values={statistic} display="total_like" />
            {summary && <span className={classes.category}>{summary}</span>}
          </Box>
          <Box
            className={classes.wrapperInfoFlag}
            display="flex"
            justifyContent="space-between"
            alignItems="flex-end"
            mt={2.5}
          >
            <Box display="flex" alignItems="center">
              {reactButton && isLogged && (
                <Button
                  size="small"
                  variant="outlined"
                  disabled={reactButton.disabled}
                  color="primary"
                  onClick={reactButton.actions}
                  startIcon={<LineIcon icon={reactButton.icon} />}
                >
                  {i18n.formatMessage({ id: reactButton.textId })}
                </Button>
              )}
            </Box>
          </Box>
        </div>
      </div>
    </div>
  );
}
EmbedPageItem.displayName = 'PageItem_EmbedView';
