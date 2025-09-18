import { useRoomPermission, useSessionUser } from '@metafox/chatplus/hooks';
import { RoomItemShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import Loading from '@metafox/ui/SmartDataGrid/Loading';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import MemberItem from './MemberItem';

const Root = styled('div')(({ theme }) => ({}));

interface Props {
  room: RoomItemShape;
  setMemberCount?: any;
}

function MemberList({ room, setMemberCount }: Props) {
  const { dispatch } = useGlobal();
  const scrollRef = React.useRef();

  const rid = room?.id;
  const user = useSessionUser();
  const perms = useRoomPermission(rid);

  const [data, setData] = React.useState<any[]>([]);
  const [reloadData, setReloadData] = React.useState(false);

  React.useEffect(() => {
    handleChange();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid, room?.usersCount]);

  React.useEffect(() => {
    if (reloadData) {
      handleChange();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reloadData, dispatch]);

  const onSuccess = data => {
    setMemberCount(data?.count || 0);

    if (Array.isArray(data.users)) setData(data.users);
  };

  const handleChange = () => {
    dispatch({
      type: 'chatplus/room/presentMembers',
      payload: {
        rid: room?.id
      },
      meta: {
        onSuccess: values => {
          onSuccess(values);
          setReloadData(false);
        }
      }
    });
  };

  if (isEmpty(data)) {
    <Loading />;
  }

  return (
    <Root>
      <ScrollContainer ref={scrollRef}>
        {data.map((u, idx) => (
          <MemberItem
            key={u._id}
            u={u}
            room={room}
            user={user}
            perms={perms}
            setReloadData={setReloadData}
          />
        ))}
      </ScrollContainer>
    </Root>
  );
}

export default MemberList;
