import { UserShape } from '@metafox/chatplus/types';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';
import MsgAvatar from '@metafox/chatplus/components/Messages/MsgAvatar';

const ItemSuggest = styled('div')(({ theme }) => ({
  padding: theme.spacing(1.5, 0),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  cursor: 'pointer'
}));
const AvatarNameWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));
const ItemSuggestName = styled('span')(({ theme }) => ({
  ...theme.typography.h5,
  marginLeft: theme.spacing(1)
}));

type P = {
  user: UserShape;
  selected: boolean;
  toggleSelect: (user: UserShape) => void;
};

export default function MemberItem({ user, selected, toggleSelect }: P) {
  return (
    <ItemSuggest onClick={() => toggleSelect(user)}>
      <AvatarNameWrapper>
        <MsgAvatar
          name={user.name}
          username={user.username}
          avatarETag={user?.avatarETag}
          size={32}
        />
        <ItemSuggestName>
          {user.name ? user.name : user.username}
        </ItemSuggestName>
      </AvatarNameWrapper>
      <LineIcon
        icon={`${selected ? 'ico ico-check active' : ''}`}
        sx={{ mr: '12px' }}
      />
    </ItemSuggest>
  );
}
