import Avatar from '@metafox/chatplus/components/Avatar';
import {
  RoomItemShape,
  RoomType,
  SessionUserShape
} from '@metafox/chatplus/types';
import { Link, RouteLink, useGlobal } from '@metafox/framework';
import { ItemActionMenu, TruncateText } from '@metafox/ui';
import { filterShowWhen } from '@metafox/utils';
import { Box, Divider, styled } from '@mui/material';
import React from 'react';

const name = 'MemberItem';

const UIChatBuddyItem = styled('div', { name, slot: 'UIChatBuddyItem' })(
  ({ theme }) => ({
    padding: theme.spacing(0, 2),
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderBottom: '1px solid',
    borderColor: theme.palette.border?.secondary,
    '&:hover': {
      background:
        theme.palette.mode === 'dark'
          ? theme.palette.grey['700']
          : theme.palette.grey['50']
    }
  })
);
const UIChatBuddyItemWrapper = styled('div', {
  name,
  slot: 'UIChatBuddyItemWrapper'
})(({ theme }) => ({
  padding: theme.spacing(2, 0),
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  flex: 1
}));

const UIChatBuddyItemInner = styled('div')(({ theme }) => ({
  marginLeft: theme.spacing(1.5),
  display: 'flex',
  alignItems: 'flex-start',
  flexDirection: 'column',
  flex: 1
}));

const MenuAction = styled('div')(({ theme }) => ({
  color: theme.palette.grey['600'],
  fontSize: theme.spacing(2),
  padding: theme.spacing(0, 1)
}));

const TitleName = styled(TruncateText)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  ...(theme.palette.mode === 'dark' && { color: theme.palette.grey['400'] })
}));
const UIChatMemberRoleText = styled('div')(({ theme }) => ({
  ...theme.typography.body1,
  ...(theme.palette.mode === 'dark' && { color: theme.palette.grey['500'] }),
  display: 'flex',
  flexWrap: 'wrap',
  '& > *:not(:last-child):after': {
    content: "'·'",
    margin: theme.spacing(0, 0.5)
  }
}));

type IProps = {
  u: any;
  room: RoomItemShape;
  perms: any;
  user: SessionUserShape;
  setReloadData?: any;
};

export default function MemberItem({
  u,
  user,
  room,
  perms = {},
  setReloadData
}: IProps) {
  const { i18n, useActionControl, chatplus } = useGlobal();

  const roles = {
    moderator: 0,
    leader: 0,
    owner: 0
  };

  if (u && u.roles && Array.isArray(u.roles)) {
    Object.keys(roles).forEach(x => {
      if (u.roles.includes(x)) roles[x] = 1;
    });
  }

  const isOwner = u?._id === user._id;
  const isMuted = room?.muted && room?.muted.includes(u?.username);

  const userId = u?._id;

  const [handleAction] = useActionControl<{}, unknown>(room?._id, {});

  const handleActionLocalFunc = (
    type: string,
    payload?: unknown,
    meta?: unknown
  ) => {
    handleAction(type, payload, meta);
    setReloadData && setReloadData(true);
  };

  const itemAction = [
    {
      label: 'unmute_room_user',
      icon: 'ico-unlock-o',
      value: 'closeMenu, chatplus/room/unmuteUserInRoom',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.mute-user'],
        ['falsy', 'isOwner'],
        ['truthy', 'isMuted']
      ]
    },
    {
      label: 'mute',
      icon: 'ico-lock',
      value: 'closeMenu, chatplus/room/muteUserInRoom',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.mute-user'],
        ['falsy', 'isOwner'],
        ['falsy', 'isMuted']
      ]
    },
    {
      label: 'set_room_owner',
      icon: 'ico-businessman-plus',
      value: 'closeMenu, chatplus/room/addRoomOwner',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.set-owner'],
        ['falsy', 'roles.owner'],
        ['falsy', 'isOwner']
      ]
    },
    {
      label: 'remove_room_owner',
      icon: 'ico-businessman-del',
      value: 'closeMenu, chatplus/room/removeRoomOwner',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.set-owner'],
        ['truthy', 'roles.owner']
      ]
    },
    {
      label: 'set_room_moderator',
      icon: 'ico-businessman-plus',
      value: 'closeMenu, chatplus/room/addRoomModerator',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.set-moderator'],
        ['falsy', 'roles.moderator'],
        ['falsy', 'isOwner']
      ]
    },
    {
      label: 'remove_room_moderator',
      icon: 'ico-businessman-del',
      value: 'closeMenu, chatplus/room/removeRoomModerator',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.set-moderator'],
        ['truthy', 'roles.moderator']
      ]
    },
    {
      label: 'set_room_leader',
      icon: 'ico-businessman-plus',
      value: 'closeMenu, chatplus/room/addRoomLeader',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.set-leader'],
        ['falsy', 'roles.leader'],
        ['falsy', 'isOwner']
      ]
    },
    {
      label: 'remove_room_leader',
      icon: 'ico-businessman-del',
      value: 'closeMenu, chatplus/room/removeRoomLeader',
      params: { userId },
      showWhen: [
        'and',
        ['truthy', 'perms.set-leader'],
        ['truthy', 'roles.leader']
      ]
    },
    {
      label: 'remove_room_user',
      icon: 'ico-trash-o',
      value: 'closeMenu, chatplus/room/removeUserFromRoom',
      params: { userId },
      showWhen: ['and', ['truthy', 'perms.remove-user'], ['falsy', 'isOwner']]
    }
  ];
  const settingMenuItems = React.useMemo(
    () =>
      filterShowWhen(itemAction, {
        isOwner,
        roles,
        perms,
        isMuted
      }),
    [isOwner, roles, perms, isMuted]
  );

  if (!u || !room) {
    return null;
  }

  return (
    <UIChatBuddyItem>
      <UIChatBuddyItemWrapper
        onClick={() => chatplus.openDirectMessageByUserId(u?._id)}
      >
        <Avatar
          username={u.username}
          name={u.name}
          size={32}
          status={u.status}
          room={room}
          roomType={RoomType.Direct}
          avatarETag={u?.avatarETag}
          hoverCard={`/user/${u?.metafoxUserId}`}
          component={RouteLink}
        />
        <UIChatBuddyItemInner>
          <TitleName lines={2} variant="h5">
            <Link
              children={u.name}
              hoverCard={`/user/${u?.metafoxUserId}`}
              underline="none"
              sx={{
                color: theme => theme.palette.text.primary
              }}
            />
          </TitleName>
          <UIChatMemberRoleText>
            {u.roles
              ? u.roles.map(x => (
                  <Box key={x}>{i18n.formatMessage({ id: x })}</Box>
                ))
              : null}

            {u?.inviter ? (
              <Box>
                {i18n.formatMessage(
                  { id: 'added_by_name' },
                  {
                    name: user?._id === u.inviter?._id ? 'you' : u.inviter?.name
                  }
                )}
              </Box>
            ) : null}
          </UIChatMemberRoleText>
        </UIChatBuddyItemInner>
      </UIChatBuddyItemWrapper>
      {settingMenuItems.length ? (
        <MenuAction>
          <ItemActionMenu
            identity={room?._id}
            icon={'ico-dottedmore-vertical-o'}
            items={settingMenuItems}
            menuName="detailActionMenu"
            handleAction={handleActionLocalFunc}
            size="smaller"
            tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
          />
        </MenuAction>
      ) : null}
      <Divider />
    </UIChatBuddyItem>
  );
}
