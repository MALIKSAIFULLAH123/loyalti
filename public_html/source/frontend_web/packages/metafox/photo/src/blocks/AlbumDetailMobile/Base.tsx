/* eslint-disable max-len */
import ItemDetailInteraction from '@metafox/core/containers/ItemDetailInteraction';
import { Link, useGlobal, useResourceAction } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import { Block, BlockContent } from '@metafox/layout';
import { APP_PHOTO, RESOURCE_ALBUM } from '@metafox/photo/constant';
import { AlbumDetailProps } from '@metafox/photo/types';
import { capitalizeWord } from '@metafox/photo/utils';
import {
  FeaturedFlag,
  SponsorFlag,
  LineIcon,
  TruncateViewMore,
  HtmlViewerWrapper,
  AuthorInfo
} from '@metafox/ui';
import * as React from 'react';
import { Box, styled } from '@mui/material';

export type Props = AlbumDetailProps;

const name = 'AlbumDetailMobile';

const Root = styled(Box, { name })(({ theme }) => ({
  maxWidth: 720,
  margin: 'auto',
  backgroundColor: theme.palette.background.paper
}));

const ActionsDropdown = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: theme.spacing(1),
  right: theme.spacing(1),
  '& > button': {
    padding: theme.spacing(1),
    width: 30,
    height: 30,
    textAlign: 'center'
  }
}));

const LinkCategory = styled(Link, { name })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.primary.main,
  marginBottom: theme.spacing(1.5),
  display: 'inline-block'
}));

const AlbumTitle = styled(Box, { name })(({ theme }) => ({
  marginBottom: theme.spacing(0.5),
  overflow: 'hidden'
}));

const Owner = styled(Box, { name })(({ theme }) => ({
  overflow: 'hidden',
  padding: theme.spacing(1.5, 0),
  display: 'flex',
  alignItems: 'center',
  width: '100%'
}));

const Info = styled(Box, { name })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(15),
  padding: theme.spacing(1, 0)
}));

const TitleLink = styled(Link, { name })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(24),
  fontWeight: theme.typography.fontWeightBold
}));

const AlbumContent = styled(Box, { name })(({ theme }) => ({
  padding: theme.spacing(2),
  position: 'relative'
}));

function PhotoAlbumDetail({
  item,
  user,
  identity,
  handleAction,
  state,
  blockProps
}: Props) {
  const { jsxBackend, ItemActionMenu, useGetItem, i18n, usePageParams } =
    useGlobal();
  const PhotoAlbumView = jsxBackend.get('photo.block.pinView');
  const resourceAction = useResourceAction(
    APP_PHOTO,
    RESOURCE_ALBUM,
    'getAlbumItems'
  );
  const ownerItem = useGetItem(item?.owner);
  const { comment_id } = usePageParams();

  if (!item) return null;

  const { apiUrl, apiMethod } = resourceAction || {};
  const dataSource = {
    apiUrl,
    apiMethod,
    apiParams: 'sort=latest'
  };
  const contentType = 'photo_album';
  const pagingId = `photo-album/${item.id}`;

  const { is_featured, is_sponsor, name, text_parsed, id, extra } =
    item;
  const to = `/photo/album/${id}`;
  let toAllAlbums = '/photo/albums';
  let labelLinkAlbum = i18n.formatMessage({ id: 'all_albums' });

  if (ownerItem && ownerItem?.resource_name !== 'user') {
    toAllAlbums = `${ownerItem?.link}/photo?stab=albums`;
    labelLinkAlbum = i18n.formatMessage(
      { id: 'all_albums_from_name' },
      { name: capitalizeWord(ownerItem.resource_name) }
    );
  }

  return (
    <Block blockProps={blockProps} testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Root>
          <AlbumContent>
            <ActionsDropdown>
              <ItemActionMenu
                identity={identity}
                state={state}
                handleAction={handleAction}
              >
                <LineIcon
                  icon={'ico-dottedmore-vertical-o'}
                  sx={{ fontSize: '13px' }}
                />
              </ItemActionMenu>
            </ActionsDropdown>
            <Box>
              <Box sx={{ mb: 1 }}>
                <LinkCategory
                  to={toAllAlbums}
                  color="primary"
                  children={labelLinkAlbum}
                />
              </Box>
              <AlbumTitle>
                {is_featured || is_sponsor ? (
                  <Box sx={{ display: 'inline-flex', mr: 1 }}>
                    <FeaturedFlag variant="itemView" value={is_featured} />
                    <SponsorFlag
                      value={is_sponsor}
                      variant="itemView"
                      item={item}
                    />
                  </Box>
                ) : null}
                <TitleLink to={to} children={name} variant={'h4'} />
              </AlbumTitle>
              <Owner>
                <AuthorInfo item={item} sx={{ mt: 0 }} />
              </Owner>
              {text_parsed ? (
                <Info>
                  <TruncateViewMore
                    truncateProps={{
                      variant: 'body1',
                      lines: 5
                    }}
                  >
                    <HtmlViewerWrapper>
                      <HtmlViewer html={text_parsed} />
                    </HtmlViewerWrapper>
                  </TruncateViewMore>
                </Info>
              ) : null}
            </Box>
            <ItemDetailInteraction
              identity={identity}
              handleAction={handleAction}
              borderBottom
              hideListComment={!comment_id}
            />
          </AlbumContent>
          <PhotoAlbumView
            title=""
            numColumns={2}
            pagingId={pagingId}
            dataSource={dataSource}
            contentType={contentType}
            gridContainerProps={{ spacing: 1 }}
            emptyPage="photo.block.EmptyPhotoAlbum"
            emptyPageProps={{
              isVisible: extra?.can_upload_media
            }}
          />
        </Root>
      </BlockContent>
    </Block>
  );
}

export default PhotoAlbumDetail;
