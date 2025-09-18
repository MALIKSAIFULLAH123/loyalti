/**
 * @type: ui
 * name: story.itemView.userSugestionAvatarCard
 * chunkName: story
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText, UserAvatar, UserName } from '@metafox/ui';
import { Box, IconButton, Tooltip, styled } from '@mui/material';
import * as React from 'react';
import { alpha } from '@mui/system/colorManipulator';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_REQUEST_SENT,
  SIZE_AVATAR_TYPE
} from '@metafox/story/constants';
import { UserItemShape } from '@metafox/user';

const name = 'UserSugestionAvatarCard';

const ItemViewStyled = styled(Box, { name, slot: 'itemview' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-start',
  marginRight: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  '&:hover': {
    cursor: 'pointer'
  },
  '&:last-child': {
    marginRight: 0
  }
}));

const ItemMediaStyled = styled(Box, { name })(({ theme }) => ({
  marginBottom: theme.spacing(1)
}));
const ItemTitleStyled = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  textAlign: 'center'
}));

const WrapperContent = styled(Box, { name, slot: 'WrapperContent' })(
  ({ theme }) => ({
    position: 'relative',
    display: 'flex',
    justifyContent: 'center',
    '&:hover': {
      '#button-remove': {
        visibility: 'visible'
      }
    }
  })
);

const ButtonActionStyled = styled(IconButton, {
  name,
  slot: 'ButtonAction',
  shouldForwardProp: props => props !== 'isTablet'
})<{ isTablet?: boolean }>(({ theme, isTablet }) => ({
  position: 'absolute',
  bottom: 0,
  padding: 0,
  borderRadius: theme.spacing(2),
  width: 42,
  height: 28,
  backgroundColor: alpha(theme.palette.background.paper, 1),
  color: theme.palette.text.primary,
  '&:hover': {
    backgroundColor: alpha(theme.palette.background.paper, 1)
  },
  ...(isTablet && {
    backgroundColor:
      theme.palette.mode === 'light'
        ? theme.palette.grey['700']
        : theme.palette.grey['100'],
    '&:hover': {
      backgroundColor:
        theme.palette.mode === 'light'
          ? theme.palette.grey['700']
          : theme.palette.grey['100']
    },
    color: theme.palette.mode === 'light' ? '#fff' : '#000'
  })
}));

const CloseIconStyled = styled(LineIcon, {
  name,
  slot: 'CloseIcon',
  shouldForwardProp: props => props !== 'isTablet'
})<{ isTablet?: boolean }>(({ theme, isTablet }) => ({
  position: 'absolute',
  right: 0,
  top: 4,
  zIndex: 3,
  fontSize: theme.mixins.pxToRem(14),
  backgroundColor: theme.palette.background.paper,
  borderRadius: '50%',
  padding: theme.spacing(0.5),
  '&:hover': {
    cursor: 'pointer'
  },
  ...(!isTablet && {
    visibility: 'hidden'
  }),
  ...(isTablet && {
    backgroundColor:
      theme.palette.mode === 'light'
        ? theme.palette.grey['700']
        : theme.palette.grey['100'],
    color: theme.palette.mode === 'light' ? '#fff' : '#000'
  })
}));

interface IProps {
  item: UserItemShape;
  onRemove?: any;
}

const actionButtonList = [
  {
    id: FRIENDSHIP_CAN_ADD_FRIEND,
    text: 'add_friend',
    icon: 'ico-user3-plus-o',
    value: 'user/addFriend'
  },
  {
    id: FRIENDSHIP_REQUEST_SENT,
    text: 'cancel_request',
    icon: 'ico-user3-minus-o',
    value: 'user/cancelRequest'
  }
];

export default function UserSugestionAvatarCard({
  item: user,
  onRemove = () => {}
}: IProps) {
  const { dispatch, useGetItem, useTheme, i18n, useIsMobile } = useGlobal();
  const theme = useTheme();
  const isTablet = useIsMobile(true);

  const item = useGetItem(user?._identity);

  if (!item) return null;

  const { friendship, extra, _identity: identity } = item || {};

  const actionButton =
    friendship === FRIENDSHIP_CAN_ADD_FRIEND && !extra?.can_add_friend
      ? undefined
      : actionButtonList.find(button => button.id === item.friendship);

  return (
    <ItemViewStyled
      data-testid="user-sugestion-card"
      maxWidth={SIZE_AVATAR_TYPE}
    >
      <WrapperContent>
        <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
          <CloseIconStyled
            id="button-remove"
            icon={'ico-close'}
            onClick={onRemove}
            isTablet={isTablet}
          />
        </Tooltip>
        <ItemMediaStyled>
          <UserAvatar
            user={item}
            size={SIZE_AVATAR_TYPE}
            hoverCard={`/user/${item?.id}`}
          />
        </ItemMediaStyled>
        {actionButton && (
          <Tooltip
            title={i18n.formatMessage({ id: actionButton?.text || 'add' })}
            placement="bottom"
          >
            <ButtonActionStyled
              isTablet={isTablet}
              onClick={() =>
                dispatch({
                  type: actionButton.value,
                  payload: { identity }
                })
              }
            >
              <LineIcon
                style={{ fontSize: 14 }}
                icon={actionButton.icon || 'ico-user-man-plus'}
              />
            </ButtonActionStyled>
          </Tooltip>
        )}
      </WrapperContent>
      <ItemTitleStyled>
        <TruncateText
          lines={1}
          variant="body1"
          color="text.primary"
          style={{ fontSize: theme.mixins.pxToRem(13) }}
        >
          <UserName
            to={item?.link}
            user={item}
            underline="none"
            hoverCard={`/user/${item?.id}`}
          />
        </TruncateText>
      </ItemTitleStyled>
    </ItemViewStyled>
  );
}

UserSugestionAvatarCard.displayName = 'UserSugestionAvatarCard';
