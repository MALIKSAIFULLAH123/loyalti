/**
 * @type: dialog
 * name: chatplus.dialog.MembersDialog
 */

import {
  useRoomItem,
  useRoomPermission,
  useSessionUser
} from '@metafox/chatplus/hooks';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import Loading from '@metafox/ui/SmartDataGrid/Loading';
import { styled } from '@mui/material';
import isEmpty from 'lodash/isEmpty';
import React from 'react';
import MemberItem from './MemberItem';
import { createStringMatcher } from '@metafox/chatplus/utils';
import { SearchMember } from '@metafox/chatplus/components';

const ContentWrapper = styled('div')(({ theme }) => ({
  padding: theme.spacing(0.25, 0)
}));

type IProps = {
  rid: string;
  users: any[];
};

export function MembersDialog({ rid, users }: IProps) {
  const { useDialog, dispatch, i18n, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();
  const { dialogProps } = useDialog();
  const user = useSessionUser();
  const room = useRoomItem(rid);
  const perms = useRoomPermission(rid);
  const data = users ? Object.values(users) : [];
  const [members, setMembers] = React.useState(data);
  const [reloadData, setReloadData] = React.useState(false);
  const initData = React.useRef(data);

  React.useEffect(() => {
    handleChange();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [room?.usersCount]);

  React.useEffect(() => {
    if (reloadData) {
      handleChange();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reloadData, dispatch]);

  const handleChange = () => {
    dispatch({
      type: 'chatplus/room/getRoomMembers',
      payload: { rid },
      meta: {
        onSuccess: values => {
          setMembers(Object.values(values));
          initData.current = Object.values(values);
          setReloadData(false);
        }
      }
    });
  };

  const handleSearch = query => {
    if (!query) {
      setMembers(initData.current);

      return;
    }

    const match = createStringMatcher(query);
    const data = members.filter(item => match(item?.name));

    setMembers(data);
  };

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle disableClose={false}>
        {i18n.formatMessage({ id: 'members' })}
      </DialogTitle>
      <DialogContent sx={{ p: 0 }}>
        <SearchMember
          placeholder="chatplus_search_for_members"
          onQueryChange={handleSearch}
        />
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={isMobile ? '100%' : 300}
        >
          <ContentWrapper>
            {isEmpty(users) ? (
              <Loading />
            ) : (
              members.map(u => (
                <MemberItem
                  key={u._id}
                  u={u}
                  room={room}
                  user={user}
                  perms={perms}
                  setReloadData={setReloadData}
                />
              ))
            )}
          </ContentWrapper>
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}

export default MembersDialog;
