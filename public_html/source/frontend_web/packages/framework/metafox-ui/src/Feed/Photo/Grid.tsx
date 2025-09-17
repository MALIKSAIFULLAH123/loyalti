/**
 * @type: embedView
 * name: feedPhotoGrid.embedItem.insideFeedItem
 * chunkName: feed_embed
 */
import { useGlobal } from '@metafox/framework';
import { PhotoItemShape } from '@metafox/photo';
import { Flag, Image, FeedEmbedCard, LineIcon } from '@metafox/ui';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import { camelCase } from 'lodash';
import * as React from 'react';
type Photo = Record<string, any>;

type Props = {
  total_photo?: number;
  total_item?: number;
  photos?: Array<Photo>;
  items?: Array<Photo>;
  photo_set?: string;
  photo_album?: string;
  ownerPhotoIdentity?: string;
  'data-testid': string;
  item: PhotoItemShape;
};

const ImageWrapper = styled(Image, {
  name: 'ImageWrapper',
  slot: 'image'
})(({ theme }) => ({
  display: 'flex'
}));

const Root = styled('div', {
  name: 'FeedItem',
  slot: 'FeedPhotoEmbed',
  shouldForwardProp: prop => prop !== 'itemType',
  overridesResolver(props, styles) {
    return [
      styles.feedPhotoEmbed,
      props.itemType && styles[camelCase(`type_${props.itemType}`)]
    ];
  }
})(({ theme }) => ({
  display: 'block',
  marginLeft: theme.spacing(-2),
  marginRight: theme.spacing(-2),
  marginTop: '-1px',
  marginBottom: '-1px',
  overflow: 'hidden'
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

const Listing = styled('div', { name: 'Listing' })(({ theme }) => ({
  display: 'flex',
  flexFlow: 'wrap',
  margin: theme.spacing(-0.25)
}));

const ItemWrapper = styled('div', {
  name: 'FeedItem',
  slot: 'feedPhotoItemEmbed',
  shouldForwardProp: prop => prop !== 'listingGrid' && prop !== 'itemIndex',
  overridesResolver(props, styles) {
    return [styles.feedPhotoItemEmbed];
  }
})<{
  listingGrid?: string;
  itemIndex?: string;
}>(({ theme, listingGrid, itemIndex }) => ({
  position: 'relative',
  display: 'block',
  padding: theme.spacing(0.25),
  ...(listingGrid === 'listing1' && {
    width: '100%'
  }),
  ...(listingGrid === 'listing2' && {
    width: '50%'
  }),
  ...(listingGrid === 'listing3' && {
    width: '50%'
  }),
  ...(listingGrid === 'listing3' &&
    itemIndex === 'item1' && {
      width: '100%'
    }),
  ...(listingGrid === 'listing4' && {
    width: '50%'
  })
}));

const Item = styled(Box, {
  name: 'FeedItem',
  slot: 'feedPhotoItemEmbed',
  shouldForwardProp: prop => prop !== 'listingGrid' && prop !== 'itemIndex',
  overridesResolver(props, styles) {
    return [styles.feedPhotoItemEmbed];
  }
})<{
  listingGrid?: string;
  itemIndex?: string;
}>(({ theme, listingGrid, itemIndex }) => ({
  display: 'block',
  padding: theme.spacing(0.25),
  '& > div': {
    '&:before': {
      content: '""',
      position: 'absolute',
      left: 0,
      right: 0,
      top: 0,
      bottom: 0,
      border: '1px solid rgba(0,0,0,0.1)',
      pointerEvents: 'none',
      zIndex: 3
    }
  },

  ...(listingGrid === 'listing1' && {
    width: '100%',
    '&:nth-of-type(1)': {
      '& > div': {
        '&:before': {
          borderLeft: 0,
          borderRight: 0
        }
      }
    }
  }),
  ...(listingGrid === 'listing2' && {
    width: '50%',
    '&:nth-child(odd)': {
      '& > div': {
        '&:before': {
          borderLeft: 0
        }
      }
    },
    '&:nth-child(even)': {
      '& > div': {
        '&:before': {
          borderRight: 0
        }
      }
    }
  }),
  ...(listingGrid === 'listing3' && {
    width: '50%',
    '&:nth-of-type(1)': {
      width: '100%',
      '& > div': {
        '&:before': {
          borderLeft: 0,
          borderRight: 0
        }
      }
    },
    '&:nth-of-type(2)': {
      '& > div': {
        '&:before': {
          borderLeft: 0
        }
      }
    },
    '&:nth-of-type(3)': {
      '& > div': {
        '&:before': {
          borderRight: 0
        }
      }
    }
  }),
  ...(listingGrid === 'listing4' && {
    width: '50%',
    '&:nth-child(odd)': {
      '& > div': {
        '&:before': {
          borderLeft: 0
        }
      }
    },
    '&:nth-child(even)': {
      '& > div': {
        '&:before': {
          borderRight: 0
        }
      }
    }
  })
}));

const FlagWrapper = styled('div', { name: 'FlagWrapper' })(({ theme }) => ({
  position: 'absolute',
  right: theme.spacing(2.5),
  bottom: theme.spacing(2.5)
}));

const RemainBackdrop = styled('div', { name: 'RemainBackdrop' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    right: 0,
    top: 0,
    bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.3)',
    pointerEvents: 'none',
    zIndex: 3,
    '&:hover': {
      backgroundColor: 'rgba(0,0,0,0.1)'
    }
  })
);

const RemainText = styled('div', { name: 'RemainText' })(({ theme }) => ({
  color: 'white',
  position: 'absolute',
  left: '50%',
  top: '50%',
  fontSize: '2rem',
  transform: 'translate(-50%,-50%)'
}));

const ItemErrorStyled = styled(Box, { name: 'ItemError' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  border: theme.mixins.border('secondary'),
  backgroundColor:
    theme.palette.mode === 'light'
      ? theme.palette.background.default
      : theme.palette.action.hover,
  fontSize: theme.mixins.pxToRem(18),
  color: theme.palette.text.secondary,
  '&:before': {
    content: '""',
    display: 'block',
    paddingBottom: '56.25%'
  }
}));

export default function FeedPhotoGrid({
  total_photo,
  total_item,
  photo_set,
  photo_album,
  photos,
  items,
  item,
  feed,
  isShared,
  'data-testid': testid
}: Props) {
  const { i18n } = useGlobal();

  let listPhotos = [];

  if (photos && photos.length > 0) listPhotos = photos;

  if (items && items.length > 0) listPhotos = items;

  const total = total_photo ?? total_item;

  const gridType = Math.min(listPhotos.length, 4) % 5;
  const remain = total - gridType;

  let path = '';

  if (photo_set) path = `/media/${photo_set}`;

  if (photo_album) path = `/media/album/${photo_album}`;

  const smallLayout = listPhotos.length < 4 && listPhotos.length !== 2;
  const countPhotos = listPhotos.length;

  return (
    <FeedEmbedCard
      bottomSpacing="normal"
      variant="default"
      item={item}
      feed={feed}
      isShared={isShared}
      sxOuter={{ overflow: 'visible', border: '0 !important' }}
      sx={{ paddingTop: 0 }}
    >
      <Root itemType={feed?.item_type} data-testid={testid}>
        <Listing>
          {listPhotos.slice(0, gridType).map((photo, index) => {
            if (photo.error) {
              return (
                <ItemWrapper
                  key={`i${photo?.id}`}
                  listingGrid={`listing${gridType}`}
                  itemIndex={`item${index + 1}`}
                >
                  <ItemErrorStyled>
                    <Box>
                      {photo?.image
                        ? i18n.formatMessage({ id: 'content_is_not_available' })
                        : photo?.title}
                    </Box>
                  </ItemErrorStyled>
                </ItemWrapper>
              );
            }

            if (photo.resource_name === 'video' || photo.type === 'video') {
              return photo.is_processing ? (
                <ItemWrapper
                  key={`i${photo?.id}`}
                  listingGrid={`listing${gridType}`}
                  itemIndex={`item${index + 1}`}
                >
                  <MediaStyled>
                    <LineIcon
                      icon="ico-loading-icon"
                      sx={{ fontSize: '24px' }}
                    />
                    <Box mt={1}>
                      {i18n.formatMessage({
                        id: 'video_is_being_processed'
                      })}
                    </Box>
                  </MediaStyled>
                  {0 < remain && gridType === index + 1 && (
                    <RemainBackdrop>
                      <RemainText>{`+ ${remain}`}</RemainText>
                    </RemainBackdrop>
                  )}
                </ItemWrapper>
              ) : (
                <ItemWrapper
                  key={`i${photo?.id}`}
                  listingGrid={`listing${gridType}`}
                  itemIndex={`item${index + 1}`}
                >
                  <VideoPlayer
                    isThumbnail={!photo.resource_name}
                    src={photo.video_url || photo.destination}
                    thumb_url={photo.image || photo.thumbnail?.image}
                    autoplayIntersection={total === 1}
                    detailLink={photo.link}
                    modalUrl={
                      total !== 1 && photo.resource_name
                        ? `${path}/${photo.resource_name}/${photo.id}${
                            photo?.slug ? `/${photo.slug}` : ''
                          }`
                        : ''
                    }
                    isCountVideo
                    id={photo.id}
                    identity={photo._identity}
                  />
                  {0 < remain && gridType === index + 1 && (
                    <RemainBackdrop>
                      <RemainText>{`+ ${remain}`}</RemainText>
                    </RemainBackdrop>
                  )}
                </ItemWrapper>
              );
            }

            return (
              <Item
                role="link"
                listingGrid={`listing${gridType}`}
                itemIndex={`item${index + 1}`}
                key={index.toString()}
                data-testid="embeditem"
              >
                <Box sx={{ position: 'relative' }}>
                  <ImageWrapper
                    src={getImageSrc(
                      photo.image || photo.avatar,
                      index === 0 && smallLayout ? '1024' : '500'
                    )}
                    aspectRatio={countPhotos < 2 ? 'auto' : '169'}
                    imageFit={'cover'}
                    identity={`photo.entities.photo.${photo.id}`}
                    link={
                      photo.resource_name
                        ? `${path}/${photo.resource_name}/${photo.id}${
                            photo?.slug ? `/${photo.slug}` : ''
                          }`
                        : undefined
                    }
                    linkParams={{
                      asModal: true,
                      identityTracking: feed?._identity
                    }}
                  />
                  {!!photo.is_featured && (
                    <FlagWrapper>
                      <Flag
                        type={'is_featured'}
                        color={'white'}
                        data-testid="featured"
                      />
                    </FlagWrapper>
                  )}
                  {0 < remain && gridType === index + 1 && (
                    <RemainBackdrop>
                      <RemainText>{`+ ${remain}`}</RemainText>
                    </RemainBackdrop>
                  )}
                </Box>
              </Item>
            );
          })}
        </Listing>
      </Root>
    </FeedEmbedCard>
  );
}
