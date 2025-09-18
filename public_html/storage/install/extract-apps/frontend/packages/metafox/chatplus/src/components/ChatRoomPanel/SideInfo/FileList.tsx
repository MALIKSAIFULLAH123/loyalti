import { useRoomFiles } from '@metafox/chatplus/hooks';
import useInfiniteScroll from '@metafox/chatplus/hooks/useInfiniteScroll';
import { RoomItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import Loading from '@metafox/ui/SmartDataGrid/Loading';
import { Box, CircularProgress, styled } from '@mui/material';
import React from 'react';
import FileItem from './FileItem';

const name = 'FileList';

const NoContent = styled('div', { name, slot: 'NoContent' })(({ theme }) => ({
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

const WrapperLoadmore = styled(Box, { name, slot: 'loadmore' })(
  ({ theme }) => ({
    textAlign: 'center'
  })
);

interface Props {
  room?: RoomItemShape;
  [key: string]: any;
}

function FileList(props: Props) {
  const { room, scrollRefList, type } = props;

  const { dispatch } = useGlobal();
  const result = useRoomFiles(room?.id);

  const items = result?.other?.files ?? [];
  const total = result?.other?.total ?? 0;
  const count = result?.other?.count ?? 0;
  const refIsLoadMore = React.useRef(true);
  const [firstLoaded, setFirstLoaded] = React.useState(false);
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

  const handleLoadMore = (callback: any) => {
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
    handleLoadMore(() => {
      setIsFetching(false);
    });
  }

  if (!firstLoaded) {
    return <Loading size={32} />;
  }

  if (Array.isArray(items) && !items.length && firstLoaded) {
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
      {items.map((file, idx) => (
        <FileItem key={idx} {...file} />
      ))}
      {isFetching && refIsLoadMore.current ? (
        <WrapperLoadmore>
          <CircularProgress
            variant="indeterminate"
            size={28}
            data-testid="loadingIndicator"
          />
        </WrapperLoadmore>
      ) : null}
    </Root>
  );
}

export default FileList;
