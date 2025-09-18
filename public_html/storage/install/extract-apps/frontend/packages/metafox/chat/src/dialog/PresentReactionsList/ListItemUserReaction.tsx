import MsgAvatar from '@metafox/chat/components/Messages/MsgAvatar';
import { useGlobal } from '@metafox/framework';
import { Box, Button, styled } from '@mui/material';
import React from 'react';

const name = 'ChatListItemUserReaction';

const Root = styled(Box, { name, slot: 'Root' })(({ theme }) => ({
  position: 'relative',
  textDecoration: 'none',
  display: 'block',
  borderBottom: theme.mixins.border('secondary'),
  '& .MuiAvatar-root': {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

const ItemOuter = styled(Box, { name, slot: 'ItemOuter' })(({ theme }) => ({
  height: '100%'
}));

const ItemSmallInner = styled(Box, { name, slot: 'ItemSmallInner' })(
  ({ theme }) => ({
    display: 'flex',
    padding: theme.spacing(2),
    justifyContent: 'space-between',
    alignItems: 'center',
    borderRadius: theme.shape.borderRadius
  })
);

const ItemMainContent = styled(Box, { name, slot: 'ItemMainContent' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center'
  })
);

const ItemSmallMedia = styled(Box, { name, slot: 'ItemSmallMedia' })(
  ({ theme }) => ({
    transition: 'all 300ms ease',
    borderRadius: '100%',
    position: 'relative'
  })
);

const UserSmallInfo = styled(Box, { name, slot: 'UserSmallInfo' })(
  ({ theme }) => ({
    marginLeft: theme.spacing(1.5),
    flex: 1,
    overflow: 'hidden',
    paddingRight: theme.spacing(1)
  })
);

const UserSmallTitle = styled(Box, { name, slot: 'UserSmallTitle' })(
  ({ theme }) => ({
    color: theme.palette.text.primary,
    fontSize: theme.mixins.pxToRem(15),
    whiteSpace: 'nowrap',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    fontWeight: theme.typography.fontWeightBold
  })
);

const ActionContent = styled(Button, { name, slot: 'ActionContent' })(
  ({ theme }) => ({
    fontWeight: `${theme.typography.fontWeightMedium} !important`
  })
);

const ImgSmallWrapper = styled(Box, { name, slot: 'ImgSmallWrapper' })(
  ({ theme }) => ({
    borderRadius: '100%'
  })
);

const ItemReactSmall = styled(Box, { name, slot: 'ItemReactSmall' })(
  ({ theme }) => ({
    position: 'absolute',
    width: '15px',
    height: '15px',
    right: 0,
    bottom: 0
  })
);

const ImgSmallReactionIcon = styled('img', {
  name,
  slot: 'ImgSmallReactionIcon'
})(({ theme }) => ({
  width: '100%'
}));

const ListItemUserReaction = ({ data, unsetReaction }: any) => {
  const { useDialog, i18n, useSession } = useGlobal();
  const { closeDialog } = useDialog();

  const { user } = useSession();

  const me = user.user_name;

  if (!data) return null;

  return data.usernames.map((user, key) => (
    <Root key={key}>
      <ItemOuter>
        <ItemSmallInner>
          <ItemMainContent>
            <ItemSmallMedia>
              <ImgSmallWrapper>
                <MsgAvatar user={user.user} size={40} />
              </ImgSmallWrapper>
              <ItemReactSmall>
                <ImgSmallReactionIcon src={user.icon} alt="reaction" />
              </ItemReactSmall>
            </ItemSmallMedia>
            <UserSmallInfo>
              <UserSmallTitle onClick={closeDialog}>
                {user.user?.full_name || user.username}
              </UserSmallTitle>
            </UserSmallInfo>
          </ItemMainContent>
          {me === user.username && (
            <ActionContent
              variant="outlined"
              color="primary"
              onClick={() => unsetReaction(user.id)}
            >
              {i18n.formatMessage({ id: 'remove' })}
            </ActionContent>
          )}
        </ItemSmallInner>
      </ItemOuter>
    </Root>
  ));
};

export default ListItemUserReaction;
