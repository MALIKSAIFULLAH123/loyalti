/**
 * @type: ui
 * name: story.itemView.userSugestionCard
 * chunkName: story
 */

import { Link, useGlobal } from '@metafox/framework';
import { LineIcon, TruncateText, UserAvatar, UserName } from '@metafox/ui';
import { Box, Button, IconButton, styled } from '@mui/material';
import * as React from 'react';
import { alpha } from '@mui/system/colorManipulator';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/story/constants';
import { UserItemShape } from '@metafox/user';

const name = 'UserSugestionCard';

const ItemViewStyled = styled(Box, { name, slot: 'itemview' })(({ theme }) => ({
  flexBasis: '140px',
  flexShrink: 0,
  width: '140px',
  height: '250px',
  marginRight: theme.spacing(1),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  background: theme.palette.background.paper,
  '&:last-child': {
    marginRight: 0
  },
  display: 'flex',
  justifyContent: 'center',
  flexDirection: 'column',
  padding: theme.spacing(1),
  border: theme.mixins.border('secondary'),
  '&:hover': {
    '#button-remove': {
      visibility: 'visible'
    }
  }
}));

const WrapperContent = styled(Box, { name, slot: 'WrapperContent' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'column',
    flex: 1
  })
);

const ItemMediaStyled = styled(Box, { name })(({ theme }) => ({}));
const ItemTitleStyled = styled(Box, { name, slot: 'ItemTitle' })(
  ({ theme }) => ({
    marginTop: theme.spacing(1),
    textAlign: 'center',
    wordBreak: 'break-word'
  })
);

const ButtonActionStyled = styled(Button, { name, slot: 'ButtonAction' })(
  ({ theme }) => ({
    backgroundColor: alpha(theme.palette.primary.light, 0.1),
    color: theme.palette.primary.main,
    '&:hover': {
      backgroundColor: alpha(theme.palette.primary.light, 0.3)
    }
  })
);

const CloseIconStyled = styled(LineIcon, { name, slot: 'CloseIcon' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(15)
  })
);
const BtnCloseIconStyled = styled(IconButton, {
  name,
  slot: 'BtnCloseIcon',
  shouldForwardProp: props => props !== 'isTablet'
})<{ isTablet?: boolean }>(({ theme, isTablet }) => ({
  position: 'absolute',
  right: 12,
  top: 12,
  zIndex: 3,
  backgroundColor: theme.palette.action.hover,
  '&:hover': {
    cursor: 'pointer',
    backgroundColor: theme.palette.action.hover
  },
  ...(!isTablet && {
    visibility: 'hidden'
  })
}));

interface IProps {
  item: UserItemShape;
  onRemove?: any;
}

const actionButtonList = [
  {
    id: FRIENDSHIP_CAN_ADD_FRIEND,
    text: 'add',
    icon: 'ico-user3-plus',
    value: 'user/addFriend'
  },
  {
    id: FRIENDSHIP_REQUEST_SENT,
    text: 'cancel',
    icon: 'ico-user3-del',
    value: 'user/cancelRequest'
  }
];

export default function UserSugestionCard({
  item: user,
  onRemove = () => {}
}: IProps) {
  const { dispatch, i18n, useGetItem, useIsMobile } = useGlobal();
  const isTablet = useIsMobile(true);

  const item = useGetItem(user?._identity);

  if (!item) return null;

  const { friendship, extra, _identity: identity } = item || {};

  const actionButton =
    friendship === FRIENDSHIP_CAN_ADD_FRIEND && !extra?.can_add_friend
      ? undefined
      : actionButtonList.find(button => button.id === item.friendship);

  return (
    <ItemViewStyled data-testid="user-sugestion-card">
      <BtnCloseIconStyled
        size="small"
        color="default"
        id="button-remove"
        onClick={onRemove}
        isTablet={isTablet}
      >
        <CloseIconStyled icon={'ico-close'} />
      </BtnCloseIconStyled>
      <WrapperContent
        component={Link}
        to={`/user/${item?.id}`}
        underline="none"
      >
        <ItemMediaStyled>
          <UserAvatar user={item} size={62} hoverCard={`/user/${item?.id}`} />
        </ItemMediaStyled>
        <ItemTitleStyled>
          <TruncateText
            lines={2}
            variant="body1"
            fontSize={16}
            fontWeight={500}
            color="text.primary"
          >
            <UserName
              to={item?.link}
              user={item}
              underline="none"
              hoverCard={`/user/${item?.id}`}
            />
          </TruncateText>
        </ItemTitleStyled>
      </WrapperContent>
      {actionButton && (
        <ButtonActionStyled
          color="primary"
          variant="contained"
          size="medium"
          startIcon={
            <LineIcon
              style={{ fontSize: 18 }}
              icon={actionButton.icon || 'ico-user-man-plus'}
            />
          }
          onClick={() =>
            dispatch({
              type: actionButton.value,
              payload: { identity }
            })
          }
        >
          {i18n.formatMessage({ id: actionButton.text })}
        </ButtonActionStyled>
      )}
    </ItemViewStyled>
  );
}

UserSugestionCard.displayName = 'UserSugestionCard';
