import { useGlobal } from '@metafox/framework';
import {
  FRIENDSHIP_CAN_ADD_FRIEND,
  FRIENDSHIP_CONFIRM_AWAIT,
  FRIENDSHIP_IS_FRIEND,
  FRIENDSHIP_REQUEST_SENT
} from '@metafox/friend/constant';
import { ButtonList, LineIcon } from '@metafox/ui';
import { Button } from '@mui/material';
import * as React from 'react';

export default function ProfilePopup({
  friendship,
  loggedIn,
  actions,
  identity,
  state,
  handleAction
}) {
  const { i18n, ItemActionMenu, useIsMobile } = useGlobal();
  const isMobile = useIsMobile();

  if (!loggedIn) return null;

  if (friendship === FRIENDSHIP_IS_FRIEND) {
    return (
      <ButtonList variant="fillFirst" spacing="medium">
        <Button
          data-testid="itemActionMessage"
          size="medium"
          variant="contained"
          color="primary"
          startIcon={<LineIcon icon={'ico-comment-o'} />}
          onClick={() => actions.chatWithFriend(isMobile)}
        >
          {i18n.formatMessage({ id: 'message' })}
        </Button>
        <ItemActionMenu
          menuName="profilePopoverMenu"
          identity={identity}
          state={state}
          handleAction={handleAction}
          size="medium"
          variant="outlined-square"
          color="primary"
          icon="ico-dottedmore-o"
          tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
        />
      </ButtonList>
    );
  }

  if (friendship === FRIENDSHIP_REQUEST_SENT) {
    return (
      <ButtonList variant="fillFirst" spacing="medium">
        <Button
          data-testid="itemActionMessage"
          size="medium"
          variant="contained"
          color="primary"
          startIcon={<LineIcon icon={'ico-comment-o'} />}
          onClick={() => actions.chatWithFriend(isMobile)}
        >
          {i18n.formatMessage({ id: 'message' })}
        </Button>
        <Button disabled size="medium" color="primary" variant="outlined">
          {i18n.formatMessage({ id: 'request_sent' })}
        </Button>
        <ItemActionMenu
          menuName="profilePopoverMenu"
          identity={identity}
          state={state}
          handleAction={handleAction}
          size="medium"
          variant="outlined-square"
          color="primary"
          icon="ico-dottedmore-o"
          tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
        />
      </ButtonList>
    );
  }

  if (friendship === FRIENDSHIP_CAN_ADD_FRIEND) {
    return (
      <ButtonList variant="fillFirst" spacing="medium">
        <Button
          color="primary"
          variant="outlined"
          size="medium"
          startIcon={<LineIcon icon={'ico-plus'} />}
          onClick={actions.addFriend}
        >
          {i18n.formatMessage({ id: 'add_friend' })}
        </Button>
        <ItemActionMenu
          menuName="profilePopoverMenu"
          identity={identity}
          state={state}
          handleAction={handleAction}
          size="medium"
          variant="outlined-square"
          color="primary"
          icon="ico-dottedmore-o"
          tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
        />
      </ButtonList>
    );
  }

  if (friendship === FRIENDSHIP_CONFIRM_AWAIT) {
    return (
      <ButtonList variant="fillFirst" spacing="medium">
        <Button
          variant="contained"
          color="primary"
          size="medium"
          type="submit"
          onClick={actions.acceptFriend}
        >
          {i18n.formatMessage({ id: 'confirm' })}
        </Button>
        <Button
          variant="outlined"
          color="primary"
          size="medium"
          type="submit"
          onClick={actions.denyFriend}
        >
          {i18n.formatMessage({ id: 'decline' })}
        </Button>
        <ItemActionMenu
          menuName="profilePopoverMenu"
          identity={identity}
          state={state}
          handleAction={handleAction}
          size="medium"
          variant="outlined-square"
          color="primary"
          icon="ico-dottedmore-o"
          tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
        />
      </ButtonList>
    );
  }
}

ProfilePopup.displayName = 'UserItem_ProfilePopup';
