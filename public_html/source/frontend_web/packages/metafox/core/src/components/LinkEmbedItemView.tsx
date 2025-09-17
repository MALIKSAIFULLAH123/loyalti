/**
 * @type: embedView
 * name: link.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import {
  getItemSelector,
  GlobalState,
  Link,
  useGlobal
} from '@metafox/framework';
import {
  FeedEmbedCard,
  FeedEmbedCardMedia,
  FeedEmbedDescription,
  FeedEmbedHost,
  FeedEmbedTitle,
  ItemShape
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';

type Props = {
  identity: string;
  description: string;
  feed?: any;
  isShared?: boolean;
} & ItemShape;

const name = 'MuiLinkEmbedItemView';
const Inner = styled(Box, {
  name,
  slot: 'inner',
  shouldForwardProp: prop => prop !== 'widthImage' && prop !== 'heightImage'
})<{ widthImage?: string; heightImage?: string }>(
  ({ theme, widthImage, heightImage }) => ({
    flex: 1,
    minWidth: 0,
    display: 'flex',
    flexDirection: 'column'
  })
);

const LinkEmbedItemView = ({
  identity,
  feed,
  isShared: isSharedProp
}: Props) => {
  const { useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const item = useSelector((state: GlobalState) =>
    getItemSelector(state, identity)
  );

  const { title, description, link, image, host, is_preview_hidden } = item;
  const imgSrc = getImageSrc(image, isMobile ? '500' : '200');
  const isShared = feed?.item_type === 'share' || isSharedProp;
  const hideContentLink =
    !isEmpty(feed?.status_background) || is_preview_hidden;

  if (!isShared && hideContentLink) return null;

  return (
    <FeedEmbedCard
      variant="default"
      bottomSpacing="normal"
      item={item}
      feed={feed}
      isShared={isShared}
    >
      {!hideContentLink ? (
        <Link
          to={link}
          target="_blank"
          sx={{ textDecoration: 'none !important' }}
        >
          <Box
            sx={{
              display: 'flex',
              width: '100%',
              flexDirection: isMobile ? 'column' : 'row'
            }}
          >
            {imgSrc ? (
              <FeedEmbedCardMedia
                image={imgSrc}
                widthImage={isMobile ? '100%' : '200px'}
                mediaRatio={isMobile ? '169' : '11'}
              />
            ) : null}
            <Inner p={isMobile ? 2 : 3} data-testid="embedview">
              <FeedEmbedTitle title={title} lines={isMobile ? 1 : 2} />
              <FeedEmbedDescription
                content={description}
                lines={isMobile ? 2 : undefined}
              />
              <FeedEmbedHost host={host} />
            </Inner>
          </Box>
        </Link>
      ) : null}
    </FeedEmbedCard>
  );
};

export default LinkEmbedItemView;
