import { useRoomFiles } from '@metafox/chatplus/hooks';
import useInfiniteScroll from '@metafox/chatplus/hooks/useInfiniteScroll';
import { RoomItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import Loading from '@metafox/ui/SmartDataGrid/Loading';
import { Box, CircularProgress, styled } from '@mui/material';
import React from 'react';
import MediaItem from './MediaItem';

const name = 'FileList';

const NoContent = styled('div', { name, slot: 'no-content' })(({ theme }) => ({
  ...theme.typography.body1,
  padding: theme.spacing(2),
  textAlign: 'center',
  flex: 1,
  minHeight: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  flexDirection: 'column',
  height: '100%',
  fontSize: theme.mixins.pxToRem(16)
}));

const NoContentDescription = styled('div', { name, slot: 'description' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(14)
  })
);

const Root = styled('div', { name, slot: 'root' })(({ theme }) => ({
  padding: theme.spacing(2)
}));

const Content = styled('div', { name, slot: 'content' })(({ theme }) => ({
  display: 'flex',
  flexFlow: 'wrap'
}));

const WrapperLoadmore = styled(Box, { name, slot: 'loadmore' })(
  ({ theme }) => ({
    textAlign: 'center'
  })
);

interface Props {
  room?: RoomItemShape;
  [key: string]: any;
}

function MediaList(props: Props) {
  const { dispatch, chatplus } = useGlobal();

  const { room, type, scrollRefList } = props;
  const result = useRoomFiles(room.id);

  const items: any[] = result?.media?.files ?? [];
  const total = result?.media?.total ?? 0;
  const count = result?.media?.count ?? 0;
  const [firstLoaded, setFirstLoaded] = React.useState(false);
  const refIsLoadMore = React.useRef(true);
  const [isFetching, setIsFetching] = useInfiniteScroll(
    scrollRefList,
    fetchMoreListItems
  );

  React.useEffect(() => {
    if (items.length) {
      setFirstLoaded(true);
    } else {
      const payload = {
        rid: room?.id,
        queryParam: props?.query,
        sort: undefined,
        count: props?.countLoad ?? 18,
        offset: 0,
        type: props?.type,
        callback: () => {
          setFirstLoaded(true);
        }
      };
      dispatch({ type: 'chatplus/room/getRoomFiles', payload });
    }
  }, [type]);

  const handleLoadMore = callback => {
    const offset = count;
    setIsFetching(true);

    if (total > count) {
      const payload = {
        rid: room?.id,
        queryParam: props?.query,
        sort: undefined,
        count: props?.countLoad ?? 18,
        offset,
        type: props?.type,
        callback
      };
      dispatch({ type: 'chatplus/room/getRoomFiles', payload });
    } else {
      setIsFetching(false);
      refIsLoadMore.current = false;
    }
  };

  function fetchMoreListItems() {
    setTimeout(() => {
      handleLoadMore(() => {
        setIsFetching(false);
      });
    }, 500);
  }

  const listImages = React.useMemo(() => {
    if (Array.isArray(items) && !items.length) return [];

    return items.map((item, index) => {
      return {
        id: index,
        src: chatplus.sanitizeRemoteFileUrl(item.url),
        video_type:
          item?.type && item.type.match('video/*') ? !!item.type : false,
        video_thumb_url: item?.video_thumb_url
          ? chatplus.sanitizeRemoteFileUrl(item?.video_thumb_url)
          : null
      };
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [items]);

  if (!firstLoaded) {
    return <Loading size={32} />;
  }

  if (Array.isArray(items) && !items.length) {
    return (
      <NoContent data-testid="noResultFound">
        {props?.emptyPhrase}
        {props?.emptyPhraseSub ? (
          <NoContentDescription>{props?.emptyPhraseSub}</NoContentDescription>
        ) : null}
      </NoContent>
    );
  }

  return (
    <Root>
      <Content>
        {items.map((media, idx) => (
          <MediaItem
            key={idx}
            listImages={listImages}
            keyIndex={idx}
            {...media}
          />
        ))}
      </Content>
      {isFetching && refIsLoadMore.current ? (
        <WrapperLoadmore>
          <CircularProgress
            data-testid="loadingIndicator"
            variant="indeterminate"
            size={28}
          />
        </WrapperLoadmore>
      ) : null}
    </Root>
  );
}

export default MediaList;
