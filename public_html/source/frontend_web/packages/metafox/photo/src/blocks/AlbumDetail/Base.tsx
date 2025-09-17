/* eslint-disable max-len */
import ItemDetailInteraction from '@metafox/core/containers/ItemDetailInteraction';
import { Link, useGlobal, useResourceAction } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { APP_PHOTO, RESOURCE_ALBUM } from '@metafox/photo/constant';
import { AlbumDetailProps } from '@metafox/photo/types';
import { capitalizeWord } from '@metafox/photo/utils';
import {
  FeaturedFlag,
  ItemTitle,
  LineIcon,
  SponsorFlag,
  TruncateViewMore,
  HtmlViewerWrapper,
  AuthorInfo
} from '@metafox/ui';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';

export type Props = AlbumDetailProps;

const name = 'AlbumDetailPhoto';

const Root = styled(Box, { name })(({ theme }) => ({
  borderRadius: theme.shape.borderRadius,
  margin: theme.spacing('auto', -2),
  backgroundColor: theme.palette.background.paper
}));

const AlbumContent = styled(Box, { name })(({ theme }) => ({
  padding: theme.spacing(2),
  position: 'relative'
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

const Owner = styled(Box, { name })(({ theme }) => ({
  overflow: 'hidden',
  marginTop: theme.spacing(2),
  display: 'flex',
  alignItems: 'center',
  width: '100%'
}));

const Info = styled(Box, { name })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(15)
}));

function PhotoAlbumDetail({
  item,
  user,
  identity,
  handleAction,
  state
}: AlbumDetailProps) {
  const { jsxBackend, i18n, ItemActionMenu, useGetItem, usePageParams } =
    useGlobal();
  const PhotoAlbumView: any = jsxBackend.get('photo.block.pinView');
  const { comment_id } = usePageParams();

  const resourceAction = useResourceAction(
    APP_PHOTO,
    RESOURCE_ALBUM,
    'getAlbumItems'
  );
  const ownerItem = useGetItem(item?.owner);
  const { apiUrl, apiMethod } = resourceAction || {};

  if (!item) return null;

  const dataSource = {
    apiUrl,
    apiMethod,
    apiParams: 'sort=latest'
  };
  const contentType = 'photo_album';
  const pagingId = `photo-album/${item.id}`;
  let toAllAlbums = '/photo/albums';
  let labelLinkAlbum = i18n.formatMessage({ id: 'all_albums' });

  const { is_featured, is_sponsor, name, text_parsed, extra } = item;

  if (ownerItem && ownerItem?.resource_name !== 'user') {
    toAllAlbums = `${ownerItem?.link}/photo?stab=albums`;
    labelLinkAlbum = i18n.formatMessage(
      { id: 'all_albums_from_name' },
      { name: capitalizeWord(ownerItem.resource_name) }
    );
  }

  return (
    <Block testid={`detailview ${item.resource_name}`}>
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
                  sx={{ fontSize: '13px' }}
                  icon={'ico-dottedmore-vertical-o'}
                />
              </ItemActionMenu>
            </ActionsDropdown>
            <Box>
              <LinkCategory
                to={toAllAlbums}
                color="primary"
                children={labelLinkAlbum}
              />
              <ItemTitle variant="h3" component={'div'} showFull>
                <FeaturedFlag variant="itemView" value={is_featured} />
                <SponsorFlag
                  value={is_sponsor}
                  variant="itemView"
                  item={item}
                />
                <Typography
                  component="h1"
                  variant="h3"
                  sx={{
                    display: { sm: 'inline', xs: 'block' },
                    mt: { sm: 0, xs: 1 },
                    verticalAlign: 'middle'
                  }}
                >
                  {name}
                </Typography>
              </ItemTitle>
              <Owner>
                <AuthorInfo item={item} sx={{ mt: 0 }} statisticDisplay={false}/>
              </Owner>
              {text_parsed && (
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
              )}
            </Box>
            <ItemDetailInteraction
              identity={identity}
              handleAction={handleAction}
              borderBottom
              hideListComment={!comment_id}
            />
          </AlbumContent>
          <Box pb={2}>
            <PhotoAlbumView
              title=""
              numColumns={3}
              pagingId={pagingId}
              dataSource={dataSource}
              contentType={contentType}
              gridContainerProps={{ spacing: 1 }}
              emptyPage="photo.block.EmptyPhotoAlbum"
              emptyPageProps={{
                isVisible: extra?.can_upload_media
              }}
            />
          </Box>
        </Root>
      </BlockContent>
    </Block>
  );
}

export default PhotoAlbumDetail;
