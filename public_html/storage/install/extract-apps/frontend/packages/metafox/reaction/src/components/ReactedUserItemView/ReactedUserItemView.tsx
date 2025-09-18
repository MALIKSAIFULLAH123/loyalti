import { GlobalState, useGlobal } from '@metafox/framework';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/friend/constant';
import { FriendRequestItemProps } from '@metafox/friend/types';
import { getReactionItemSelector } from '@metafox/reaction/selectors/geReactionItem';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar,
  UserName
} from '@metafox/ui';
import { Box, Button, styled } from '@mui/material';
import * as React from 'react';
import { useSelector } from 'react-redux';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'ReactedUserItemView';

const ActionContent = styled(Button, {
  name
})(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold
}));

const ItemReactSmall = styled(Box, {
  name
})(({ theme }) => ({
  position: 'absolute',
  width: '15px',
  height: '15px',
  right: 0,
  bottom: 0
}));

const ImgSmallReactionIcon = styled('img', {
  name
})(({ theme }) => ({
  width: '100%'
}));

const UserSmallInfo = styled(Box, {
  name
})(({ theme }) => ({
  flex: 1,
  overflow: 'hidden',
  paddingRight: theme.spacing(1)
}));

const UserSmallTitle = styled(Box, {
  name
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.primary,
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  fontWeight: theme.typography.fontWeightBold
}));

const FriendSmallInfo = styled(Box, {
  name
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary,
  marginTop: theme.spacing(0.5),
  display: 'inline-block'
}));

const MutualFriend = styled('span', {
  name
})(({ theme }) => ({
  marginRight: theme.spacing(0.5)
}));

const actionButtonList = [
  {
    id: FRIENDSHIP_CAN_ADD_FRIEND,
    text: 'add_friend',
    value: 'user/addFriend'
  },
  {
    id: FRIENDSHIP_REQUEST_SENT,
    text: 'cancel_request',
    value: 'user/cancelRequest'
  }
];

const ReactedUserItemView = ({
  item,
  user,
  itemProps,
  wrapAs,
  wrapProps
}: FriendRequestItemProps) => {
  const { identityParent } = itemProps || {};
  const { i18n, useDialog, dispatch, useSession } = useGlobal();
  const { closeDialog } = useDialog();
  const { user: authUser } = useSession();

  const isOwner = authUser?.id === user?.id;

  const reactionItem = useSelector((state: GlobalState) =>
    getReactionItemSelector(state, item.react_id)
  );

  const unsetReaction = React.useCallback(
    () => {
      if (!identityParent) return;

      dispatch({
        type: 'reactionItem',
        payload: { identity: identityParent, reaction_id: item?.react_id }
      });

      closeDialog();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [identityParent, item?.react_id]
  );

  if (!item) return null;

  const { mutual_friends } = item;
  const to = user?.link;

  const actionButton = actionButtonList.find(
    button => button.id === user.friendship
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
    >
      <ItemMedia>
        <UserAvatar user={user} size={48} />
        <ItemReactSmall>
          <ImgSmallReactionIcon src={reactionItem?.icon} alt="reaction" />
        </ItemReactSmall>
      </ItemMedia>
      <ItemText>
        <ItemTitle>{item.full_name}</ItemTitle>
        <UserSmallInfo>
          <UserSmallTitle onClick={closeDialog}>
            <UserName to={to} user={user} color={'inherit'} />
          </UserSmallTitle>
          {mutual_friends?.total ? (
            <FriendSmallInfo role="button">
              <MutualFriend>{mutual_friends?.total}</MutualFriend>
              {i18n.formatMessage(
                { id: 'total_mutual_friend' },
                { value: mutual_friends?.total }
              )}
            </FriendSmallInfo>
          ) : null}
        </UserSmallInfo>
      </ItemText>
      {actionButton && (
        <ActionContent
          variant="outlined"
          color="primary"
          onClick={() =>
            dispatch({
              type: actionButton.value,
              payload: { identity: `user.entities.user.${user.id}` }
            })
          }
        >
          {i18n.formatMessage({ id: actionButton.text })}
        </ActionContent>
      )}
      {isOwner && identityParent && (
        <ActionContent
          color="primary"
          variant="outlined"
          onClick={unsetReaction}
        >
          {i18n.formatMessage({ id: 'remove' })}
        </ActionContent>
      )}
    </ItemView>
  );
};

ReactedUserItemView.LoadingSkeleton = LoadingSkeleton;

export default ReactedUserItemView;
