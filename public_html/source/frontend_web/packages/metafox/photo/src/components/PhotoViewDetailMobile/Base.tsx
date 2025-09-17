import { Link, useGlobal, useLocation, GlobalState } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';
import {
  MediaViewDetailProps,
  ACTION_ADD_TAG,
  ACTION_REMOVE_TAG
} from '@metafox/photo';
import {
  CategoryList,
  FromNow,
  LineIcon,
  PrivacyIcon,
  UserAvatar,
  UserName,
  TruncateViewMore
} from '@metafox/ui';
import { Box, Divider, styled } from '@mui/material';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { getTaggedFriendsPhotoSelector } from '@metafox/core/selectors/status';

const name = 'PhotoDetailMobile';
const HeaderItemAlbum = styled('div', { name, slot: 'HeaderItemAlbum' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    padding: theme.spacing(0, 0, 2, 2)
  })
);
const AlbumNameWrapper = styled(Box, { name, slot: 'AlbumNameWrapper' })(
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

const Root = styled('div', { name, slot: 'Root' })(({ theme }) => ({
  padding: 0,
  borderRadius: theme.shape.borderRadius,
  maxWidth: 1200,
  margin: 'auto',
  [theme.breakpoints.down('xs')]: {
    flexFlow: 'column'
  }
}));

const Header = styled('div', { name, slot: 'Header' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  marginBottom: theme.spacing(2),
  padding: theme.spacing(0, 2)
}));

const HeaderAvatarHolder = styled('div', { name, slot: 'HeaderAvatarHolder' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(1.5)
  })
);

const HeaderInfo = styled('div', { name, slot: 'HeaderInfo' })(({ theme }) => ({
  padding: '4px 0',
  flex: 1
}));

const ProfileLink = styled(UserName, { name, slot: 'ProfileLink' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightBold,
    paddingRight: theme.spacing(0.5),
    color: theme.palette.text.primary
  })
);

const PrivacyBlock = styled('div', { name, slot: 'PrivacyBlock' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    color: theme.palette.text.secondary
  })
);

const SeparateSpans = styled('span', { name, slot: 'SeparateSpans' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    '& span + span:before': {
      content: '"·"',
      display: 'inline-block',
      padding: `${theme.spacing(0, 0.5)}`
    }
  })
);

const Info = styled(Box, { name, slot: 'Info' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.primary
}));

const PhotoReaction = styled('div', { name, slot: 'PhotoReaction' })(
  ({ theme }) => ({
    padding: theme.spacing(0, 2, 2, 2)
  })
);

const ImageContainer = styled('div', {
  name,
  slot: 'ImageContainer',
  shouldForwardProp: props => props !== 'fullScreenView'
})<{ fullScreenView?: boolean }>(({ theme, fullScreenView }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative',
  flexGrow: 1,
  ...(fullScreenView && {
    position: 'fixed',
    left: 0,
    right: 0,
    top: 0,
    bottom: 0,
    zIndex: 99
  })
}));

export type Props = MediaViewDetailProps;

function PhotoDetail({
  item,
  actions,
  handleAction,
  state,
  user,
  identity,
  nextUrl,
  prevUrl,
  mediaType,
  shouldPreload,
  fromResource,
  fromResourceId
}: Props) {
  const {
    jsxBackend,
    ItemDetailInteraction,
    ItemActionMenu,
    useGetItem,
    useGetItems,
    i18n,
    dispatch,
    navigate
  } = useGlobal();
  const location = useLocation();
  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');
  const tagged_friends = useSelector((state: GlobalState) =>
    getTaggedFriendsPhotoSelector(state, item)
  );

  React.useEffect(() => {
    if (!shouldPreload) return;

    dispatch({
      type: `photo/${fromResource}/LOAD`,
      payload: {
        identity,
        direction: shouldPreload,
        [fromResource]: fromResourceId
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [shouldPreload]);
  const MediaViewSet = jsxBackend.get('media.ui.viewSet');
  const TaggedFriends = jsxBackend.get('core.ui.taggedFriend');
  const TaggedPlace = jsxBackend.get('core.ui.taggedPlace');
  const {
    categories: categoriesItem,
    album,
    location: locationItem
  } = item || {};
  const itemAlbum = useGetItem(album);
  const categories = useGetItems<{ id: number; name: string }>(categoriesItem);
  const onArrowClick = React.useCallback(
    url => {
      navigate(
        { pathname: url },
        { replace: true, state: { asModal: location?.state?.asModal } }
      );
    },
    [navigate, location?.state?.asModal]
  );

  const onAddPhotoTag = (data: unknown) => {
    dispatch({ type: ACTION_ADD_TAG, payload: { identity, data } });
  };

  const onRemovePhotoTag = (id: unknown) => {
    dispatch({ type: ACTION_REMOVE_TAG, payload: { identity, id } });
  };

  if (!item) return null;

  return (
    <Root>
      {PendingCard && <PendingCard sx={{ borderRadius: 0 }} item={item} />}
      <HeaderItemAlbum>
        {itemAlbum && !itemAlbum?.is_default ? (
          <>
            <AlbumNameWrapper sx={{ pt: 2 }}>
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
            <Box sx={{ pt: 2 }}>
              <Divider />
            </Box>
          </>
        ) : null}
        <CategoryList
          data={categories}
          sx={{ pt: 2, mb: { sm: 1, xs: 0 }, textTransform: 'capitalize' }}
          displayLimit={2}
        />
      </HeaderItemAlbum>
      <Header>
        <HeaderAvatarHolder>
          <UserAvatar user={user} size={48} />
        </HeaderAvatarHolder>
        <HeaderInfo>
          <div>
            <ProfileLink user={user} />
          </div>
          <PrivacyBlock>
            <SeparateSpans>
              <PrivacyIcon value={item.privacy} item={item?.privacy_detail} />
              <FromNow value={item.creation_date} />
            </SeparateSpans>
          </PrivacyBlock>
        </HeaderInfo>
        <ItemActionMenu
          identity={identity}
          state={state}
          handleAction={handleAction}
        />
      </Header>
      <Box px={2}>
        {item.text || item.description ? (
          <Info mb={2}>
            <TruncateViewMore
              truncateProps={{
                variant: 'body1',
                lines: 3
              }}
            >
              <HtmlViewer html={item.text || item.description} />
            </TruncateViewMore>
          </Info>
        ) : null}
        {tagged_friends?.length || locationItem?.address ? (
          <Box
            mb={2}
            sx={{
              '& a': {
                fontWeight: '700',
                color: theme => theme.palette.text.primary
              }
            }}
          >
            {tagged_friends?.length ? (
              <TaggedFriends
                item_type={item.resource_name}
                item_id={item.id}
                total={tagged_friends.length}
                users={tagged_friends}
              />
            ) : null}
            {locationItem?.address ? (
              <Box component="span" ml={0.5}>
                {i18n.formatMessage(
                  {
                    id: 'at_tagged_place'
                  },
                  {
                    name: locationItem.address,
                    bold: () => <TaggedPlace place={locationItem} />
                  }
                )}
              </Box>
            ) : null}
          </Box>
        ) : null}
      </Box>
      <ImageContainer>
        <MediaViewSet
          mediaType={mediaType}
          identity={identity}
          onArrowClick={onArrowClick}
          onAddPhotoTag={onAddPhotoTag}
          onRemovePhotoTag={onRemovePhotoTag}
          nextUrl={nextUrl}
          prevUrl={prevUrl}
          sxWrapper={{ minHeight: '350px' }}
        />
      </ImageContainer>
      <PhotoReaction>
        <ItemDetailInteraction
          identity={identity}
          handleAction={handleAction}
        />
      </PhotoReaction>
    </Root>
  );
}

export default PhotoDetail;
