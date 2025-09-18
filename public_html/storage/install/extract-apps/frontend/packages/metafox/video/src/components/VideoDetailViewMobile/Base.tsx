import { Link, useGlobal } from '@metafox/framework';
import {
  CategoryList,
  LineIcon,
  TruncateViewMore,
  AuthorInfo,
  ItemAction,
  HtmlViewerWrapper
} from '@metafox/ui';
import { Box, Divider, styled } from '@mui/material';
import * as React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { VideoItemProps } from '@metafox/video/types';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';

const name = 'videoView';

const VideoContainer = styled('div', {
  name,
  slot: 'VideoContainer',
  shouldForwardProp: props => props !== 'fullScreenView'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  backgroundColor: '#000',
  justifyContent: 'center',
  position: 'relative',
  flexGrow: 1,
  borderTopLeftRadius: theme.shape.borderRadius,
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderRadius: '0',
  margin: theme.spacing(0, -2)
}));

const Root = styled(Box, {
  name,
  slot: 'root'
})<{}>(({ theme }) => ({
  display: 'block'
}));

const HeaderItemAlbum = styled(Box, { name, slot: 'HeaderItemAlbum' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column'
  })
);
const AlbumNameWrapper = styled('div', { name, slot: 'AlbumNameWrapper' })(
  ({ theme }) => ({
    '& .ico.ico-photos-o': {
      fontSize: theme.mixins.pxToRem(18),
      marginRight: theme.spacing(1)
    },
    display: 'flex',
    alignItems: 'center'
  })
);
const AlbumName = styled('div', { name, slot: 'AlbumName' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15)
}));

const Info = styled(Box, { name, slot: 'Info' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.primary
}));

function VideoViewMobile({
  identity,
  handleAction,
  state,
  error
}: VideoItemProps & { error: any }) {
  const {
    ItemDetailInteraction,
    i18n,
    jsxBackend,
    useGetItem,
    useGetItems,
    ItemActionMenu
  } = useGlobal();
  const item = useGetItem(identity);

  const itemAlbum = useGetItem(item?.album);

  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );

  if (!item) return null;

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  return (
    <ErrorBoundary error={error}>
      <Root>
        {PendingCard && <PendingCard item={item} />}
        <HeaderItemAlbum>
          {itemAlbum && !itemAlbum?.is_default ? (
            <>
              <AlbumNameWrapper>
                <LineIcon icon=" ico-photos-o" />
                <AlbumName>
                  {i18n.formatMessage(
                    { id: 'from_album_name' },
                    {
                      name: <Link to={itemAlbum?.link}>{itemAlbum?.name}</Link>
                    }
                  )}
                </AlbumName>
              </AlbumNameWrapper>
              <Box sx={{ py: 2 }}>
                <Divider />
              </Box>
            </>
          ) : null}
          <CategoryList data={categories} displayLimit={2} />
        </HeaderItemAlbum>
        <Box sx={{ position: 'relative' }}>
          <AuthorInfo
            sx={{ margin: 0, padding: theme => theme.spacing(2, 3, 2, 0) }}
            item={item}
          />
          <ItemAction sx={{ position: 'absolute', top: 8, right: 0 }}>
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              menuName="detailActionMenu"
              handleAction={handleAction}
              size="smaller"
            />
          </ItemAction>
        </Box>
        {item.description ? (
          <Info mb={2}>
            <TruncateViewMore
              truncateProps={{
                variant: 'body1',
                lines: 3
              }}
            >
              <HtmlViewerWrapper mt={0}>
                <HtmlViewer html={item.text || item.description} />
              </HtmlViewerWrapper>
            </TruncateViewMore>
          </Info>
        ) : null}
        <VideoContainer>
          {jsxBackend.render({
            component: 'video.itemView.modalCard',
            props: {
              item
            }
          })}
        </VideoContainer>
        <ItemDetailInteraction
          identity={identity}
          handleAction={handleAction}
        />
      </Root>
    </ErrorBoundary>
  );
}

export default VideoViewMobile;
