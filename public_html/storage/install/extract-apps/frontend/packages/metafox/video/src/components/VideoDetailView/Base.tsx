import { Link, useGlobal, useLocation } from '@metafox/framework';
import { CategoryList, LineIcon } from '@metafox/ui';
import { Box, Divider, styled } from '@mui/material';
import * as React from 'react';
import { VideoItemShapeDialogProps } from '../../types';
import ErrorBoundary from '@metafox/core/pages/ErrorPage/Page';
import qs from 'query-string';

const name = 'videoView';

const VideoMain = styled('div', { name, slot: 'dialogVideo' })(({ theme }) => ({
  position: 'relative',
  backgroundColor: '#000',
  width: '100%',
  overflow: 'hidden',
  flex: 1,
  minWidth: 0,
  '& iframe': {
    width: '100%',
    height: '100%'
  },
  [theme.breakpoints.down('md')]: {
    width: '100%',
    height: 'auto',
    borderRadius: 0,
    overflow: 'initial'
  }
}));

const VideoStatistic = styled('div', {
  name,
  slot: 'videoStatistic',
  shouldForwardProp: prop => prop !== 'isExpand'
})<{
  isExpand: boolean;
}>(({ theme, isExpand }) => ({
  height: '100%',
  width: isExpand ? 0 : '480px',
  [theme.breakpoints.down('md')]: {
    width: '100%'
  },
  [theme.breakpoints.down('xs')]: {
    width: '100%',
    height: '400px'
  }
}));

const Root = styled(Box, {
  name,
  slot: 'root'
})<{}>(({ theme }) => ({
  padding: '0 !important',
  height: '100%',
  display: 'flex',
  overflowX: 'hidden',
  background: theme.palette.background.paper,
  width: '100%',
  [theme.breakpoints.down('md')]: {
    height: 'auto',
    flexFlow: 'column'
  }
}));

const StyledHeaderItemAlbum = styled('div', { name, slot: 'HeaderAlbum' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    padding: theme.spacing(0, 2)
  })
);
const StyledAlbumNameWrapper = styled('div', {
  name,
  slot: 'AlbumNameWrapper'
})(({ theme }) => ({
  '& .ico.ico-photos-o': {
    fontSize: theme.mixins.pxToRem(18),
    marginRight: theme.spacing(1)
  },
  display: 'flex',
  alignItems: 'center'
}));
const AlbumName = styled('div', { name, slot: 'AlbumName' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15)
}));

const StyledWrapperStatistic = styled(Box, { name, slot: 'WrapperStatistic' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    height: '100%'
  })
);

function VideoViewDialog({
  identity,
  error,
  searchParams: searchParamsProp
}: VideoItemShapeDialogProps) {
  const {
    ItemDetailInteractionInModal,
    i18n,
    jsxBackend,
    useGetItem,
    useGetItems
  } = useGlobal();
  const item = useGetItem(identity);
  const [isExpand, setExpand] = React.useState<boolean>(false);
  const location = useLocation();
  const searchParams =
    searchParamsProp ?? location?.search
      ? qs.parse(location.search.replace(/^\?/, ''))
      : {};
  const itemAlbum = useGetItem(item?.album);

  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );

  if (!item) return null;

  const onMinimizePhoto = (minimize: boolean) => {
    setExpand(minimize);
  };

  return (
    <ErrorBoundary error={error}>
      <Root>
        <VideoMain>
          {jsxBackend.render({
            component: 'video.itemView.modalCard',
            props: {
              item,
              onMinimizePhoto
            }
          })}
        </VideoMain>
        <VideoStatistic isExpand={isExpand}>
          <StyledWrapperStatistic
            sx={{
              display: isExpand ? 'none' : 'flex',
              flexDirection: 'column'
            }}
          >
            <StyledHeaderItemAlbum>
              {itemAlbum && !itemAlbum?.is_default ? (
                <Box sx={{ pt: 2 }}>
                  <StyledAlbumNameWrapper>
                    <LineIcon icon=" ico-photos-o" />
                    <AlbumName>
                      {i18n.formatMessage(
                        { id: 'from_album_name' },
                        {
                          name: (
                            <Link to={itemAlbum?.link}>{itemAlbum?.name}</Link>
                          )
                        }
                      )}
                    </AlbumName>
                  </StyledAlbumNameWrapper>
                  <Box sx={{ pt: 2 }}>
                    <Divider />
                  </Box>
                </Box>
              ) : null}
              <CategoryList
                data={categories}
                sx={{
                  pt: 2,
                  mb: { sm: 1, xs: 0 },
                  textTransform: 'capitalize'
                }}
                displayLimit={2}
              />
            </StyledHeaderItemAlbum>

            <Box sx={{ flex: 1, minHeight: 0 }}>
              <ItemDetailInteractionInModal
                identity={identity}
                menuName="detailActionMenu"
                searchParams={searchParams}
                statisticDisplay={'total_view'}
              />
            </Box>
          </StyledWrapperStatistic>
        </VideoStatistic>
      </Root>
    </ErrorBoundary>
  );
}

export default VideoViewDialog;
