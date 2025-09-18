import { Link, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import {
  FeaturedFlag,
  Image,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  PendingFlag,
  SponsorFlag,
  Statistic
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { VideoItemProps } from '@metafox/video/types';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';

const OverlayStyled = styled(Box, {
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  position: 'absolute',
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  backgroundColor: 'rgba(0,0,0,0.4)',
  opacity: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer',

  '& .iconPlay': {
    color: '#fff',
    fontSize: 48,
    position: 'relative'
  },
  '&:hover': {
    opacity: 1
  },
  ...(isMobile && {
    opacity: 1
  })
}));

const MediaStyled = styled(Box)(({ theme }) => ({
  backgroundColor: 'rgba(0,0,0,0.4)',
  aspectRatio: '16 / 9',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: theme.mixins.pxToRem(16),
  padding: theme.spacing(1)
}));

const VideoItemMainCard = ({
  item,
  user,
  identity,
  itemProps,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: VideoItemProps) => {
  const classes = useStyles();
  const { ItemActionMenu, assetUrl, useIsMobile, i18n } = useGlobal();
  const { itemProps: { media = {} } = {} } = useBlock();
  const isMobile = useIsMobile();

  if (!item) return null;

  const to = `/video/play/${item.id}`;

  const noImage = item?.is_processing
    ? assetUrl('video.video_in_processing_image')
    : assetUrl('video.no_image');
  const cover = getImageSrc(item.image, '500', noImage);

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <div className={classes.itemFlag}>
        <FeaturedFlag variant="itemView" value={item.is_featured} />
        <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
        <PendingFlag variant="itemView" value={item.is_pending} />
      </div>
      {item?.is_failed ? (
        <MediaStyled>
          <LineIcon icon="ico-warning" sx={{ fontSize: '24px' }} />
          <Box mt={1}>
            {i18n.formatMessage({ id: 'video_has_been_processed_failed' })}
          </Box>
        </MediaStyled>
      ) : (
        <ItemMedia src={cover} backgroundImage>
          <Box sx={{ position: 'relative' }}>
            {item?.is_processing ? (
              <Link to={to} asModal>
                <MediaStyled>
                  <LineIcon icon="ico-loading-icon" sx={{ fontSize: '24px' }} />
                  <Box mt={1}>
                    {i18n.formatMessage({ id: 'video_is_being_processed' })}
                  </Box>
                </MediaStyled>
              </Link>
            ) : (
              <Image
                src={cover}
                {...media}
                identity={identity}
                link={to}
                linkParams={{ asModal: true, identityTracking: identity }}
              />
            )}
            {item?.mature ? null : (
              <Link asModal to={to} identityTracking={identity}>
                <OverlayStyled isMobile={isMobile}>
                  <LineIcon className="iconPlay" icon="ico-play-circle-o" />
                </OverlayStyled>
              </Link>
            )}
          </Box>
        </ItemMedia>
      )}
      <ItemText>
        <ItemTitle className={classes.itemTitle}>
          <Link to={to} asModal identityTracking={identity}>
            {item.title}
          </Link>
        </ItemTitle>
        <Box>
          <Statistic values={item.statistic} display={'total_view'} />
          {itemProps.showActionMenu ? (
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              handleAction={handleAction}
              className={classes.actionMenu}
            />
          ) : null}
        </Box>
      </ItemText>
    </ItemView>
  );
};

export default VideoItemMainCard;
