import { useGlobal } from '@metafox/framework';
import { UserAvatar, UserName } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';
import Content from './Content';

const name = 'PreFetchComment';

const ItemOuter = styled('div', { name, slot: 'itemOuter' })(({ theme }) => ({
  display: 'flex',
  opacity: 0.6,
  pointerEvents: 'none',
  '&:hover $ItemActionMenu': {
    visibility: 'visible',
    marginLeft: theme.spacing(0.5)
  }
}));
const AvatarWrapper = styled('div', { name, slot: 'avatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1)
  })
);
const ItemInner = styled('div', { name, slot: 'itemInner' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  minWidth: 0,
  wordBreak: 'break-word'
}));
const ItemName = styled('div', { name, slot: 'itemName' })(({ theme }) => ({
  display: 'flex',
  fontSize: theme.mixins.pxToRem(13),
  marginBottom: theme.spacing(0.5)
}));
const UserNameStyled = styled(UserName, { name, slot: 'userName' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    maxWidth: '100%',
    fontWeight: 'bold'
  })
);
const Actions = styled('div', { name, slot: 'actions' })(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  height: theme.spacing(4)
}));

export default function PreFetchComment({ text }) {
  const { useSession, ReactionActButton, ReplyActButton } = useGlobal();

  const { user } = useSession();

  return (
    <div data-testid="comment">
      <Box pt={1}>
        <ItemOuter>
          <AvatarWrapper>
            <UserAvatar user={user as any} size={32} />
          </AvatarWrapper>
          <ItemInner>
            <Box p={0} borderRadius={2}>
              <ItemName>
                <UserNameStyled to={`/user/${user.id}`} user={user} />
              </ItemName>
              <Content text={text} />
            </Box>
            <Actions className={'dotSeparators'}>
              {ReactionActButton && <ReactionActButton minimize />}
              {ReplyActButton && <ReplyActButton minimize />}
            </Actions>
          </ItemInner>
        </ItemOuter>
      </Box>
    </div>
  );
}
