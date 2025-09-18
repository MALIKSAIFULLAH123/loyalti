/**
 * @type: dialog
 * name: dialog.chatplus.seenUsersListPopup
 */
import { SeenUserShape } from '@metafox/chatplus/types';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { Link, RouteLink, useGlobal } from '@metafox/framework';
import { TruncateText } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import { convertDateTime } from '../utils';
import { useChatUserItem } from '../hooks';
import Avatar from '../components/Avatar';

const name = 'MemberItem';

const UIChatBuddyItem = styled('div', { name, slot: 'UIChatBuddyItem' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: theme.spacing(1.5, 2, 1.5, 0),
    color: '#555555',
    fontSize: '14px'
  })
);
const UIChatBuddyItemWrapper = styled('div', {
  name,
  slot: 'UIChatBuddyItemWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: 0,
  flex: 1
}));

const UIChatBuddyItemInner = styled('div')(({ theme }) => ({
  marginLeft: theme.spacing(1.5),
  display: 'flex',
  alignItems: 'flex-start',
  flexDirection: 'column',
  flex: 1
}));

const TitleName = styled(TruncateText)(({ theme }) => ({
  ...(theme.palette.mode === 'dark' && { color: theme.palette.grey['400'] })
}));
const UIChatMemberRoleText = styled('div')(({ theme }) => ({
  ...(theme.palette.mode === 'dark' && { color: theme.palette.grey['500'] })
}));

type IProps = { users: SeenUserShape[] };

const ItemView = ({ item }: any) => {
  const userInfo = useChatUserItem(item?._id);

  return (
    <UIChatBuddyItem>
      <UIChatBuddyItemWrapper>
        <Avatar
          name={item?.name}
          username={item?.username}
          noLink={item.type === 'bot'}
          size={32}
          avatarETag={item?.avatarETag || userInfo?.avatarETag}
          hoverCard={
            item?.metafoxUserId ? `/user/${item?.metafoxUserId}` : false
          }
          component={RouteLink}
        />
        <UIChatBuddyItemInner>
          <TitleName lines={1} variant="h5">
            <Link
              to={item.type === 'bot' ? null : item?.username}
              children={item.name}
              hoverCard={
                item?.metafoxUserId ? `/user/${item?.metafoxUserId}` : false
              }
              underline="none"
              color={'text.primary'}
            />
          </TitleName>
          <UIChatMemberRoleText>
            {convertDateTime(item?.seenAt.$date)}
          </UIChatMemberRoleText>
        </UIChatBuddyItemInner>
      </UIChatBuddyItemWrapper>
    </UIChatBuddyItem>
  );
};

function SeenUsersListPopup({ users }: IProps) {
  const { useDialog, i18n } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();

  if (!users || !users.length) {
    closeDialog();

    return null;
  }

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle disableClose={false}>
        {i18n.formatMessage({ id: 'seen_users' })}
      </DialogTitle>
      <DialogContent sx={{ maxHeight: '300px', py: 0.5 }}>
        {users.map((u, index) => (
          <ItemView key={index} item={u} />
        ))}
      </DialogContent>
    </Dialog>
  );
}

export default SeenUsersListPopup;
