/**
 * @type: dialog
 * name: chatplus.dialog.MultipleConversationPicker
 */
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { Button, Divider } from '@mui/material';
import Checkbox from '@mui/material/Checkbox';
import FormControlLabel from '@mui/material/FormControlLabel';
import FormGroup from '@mui/material/FormGroup';
import React from 'react';
import SearchUsers from '@metafox/chatplus/components/SearchUsers';
import { useSessionUser } from '../hooks';

function getTitleConversation(
  isNewConversation: any,
  users: any,
  isAddMember: any
) {
  let result = 'new_group_conversation';

  switch (isNewConversation) {
    case true:
      if (users.length > 1) {
        result = 'new_group_conversation';
      } else {
        result = 'new_conversation';
      }

      break;
    case false:
      if (isAddMember) {
        result = 'add_members';
      }

      break;

    default:
      break;
  }

  return result;
}

function checkDisableBtnCreate(
  isNewConversation: any,
  users: any,
  members?: any,
  isAddMember?: any
) {
  let disable = false;

  switch (isNewConversation) {
    case true:
      disable = false;

      break;

    case false:
      if (
        isAddMember &&
        users.length >= 1 &&
        members &&
        users.length !== members.length
      ) {
        disable = false;

        break;
      }

      if (users.length < 2 || (members && users.length === members.length)) {
        disable = true;
      } else {
        disable = false;
      }

      break;

    default:
      break;
  }

  return disable;
}

function checkShowMoreOption(
  isNewConversation: any,
  users: any,
  isAddMember: any
) {
  let result = true;

  switch (isNewConversation) {
    case true:
      if (users.length > 1) {
        result = true;
      } else {
        result = false;
      }

      break;
    case false:
      if (isAddMember) {
        result = false;
      }

      break;

    default:
      break;
  }

  return result;
}

export function CheckboxLabels({
  setIsPublic,
  setReadonly,
  readonly = false,
  isPublic = false,
  allowEditing = true
}: any) {
  const { i18n } = useGlobal();

  return (
    <FormGroup>
      <FormControlLabel
        control={
          <Checkbox
            disabled={!allowEditing}
            defaultChecked
            checked={readonly}
            onChange={e => setReadonly(e.target.checked)}
          />
        }
        label={i18n.formatMessage({ id: 'read_only_chat' })}
      />
      <FormControlLabel
        control={
          <Checkbox
            disabled={!allowEditing}
            defaultChecked
            checked={isPublic}
            onChange={e => setIsPublic(e.target.checked)}
          />
        }
        label={i18n.formatMessage({ id: 'public_group_chat' })}
      />
    </FormGroup>
  );
}

interface Props {
  users?: any[];
  isNewConversation?: any;
  readonly?: any;
  isPublic?: any;
  isAddMember?: any;
  roomId?: any;
}

function MultipleConversationPicker(props: Props) {
  const { i18n, useDialog, dispatch } = useGlobal();

  const { dialogProps, closeDialog } = useDialog();

  const userSession = useSessionUser();

  const members = (props?.users || [])
    .filter(user => user.username !== userSession.username)
    .map(x => ({
      value: x.username,
      label: x.name,
      _id: x._id
    }));

  const roomId = props?.roomId;
  const isNewConversation = props?.isNewConversation || false;
  const isReadOnlyProp = props?.readonly || false;
  const isPublicProp = props?.isPublic || false;
  const isAddMember = props?.isAddMember || false;
  const [users, setUsers] = React.useState<any[]>(members ? members : []);
  const [readonly, setReadonly] = React.useState<boolean>(isReadOnlyProp);
  const [isPublic, setIsPublic] = React.useState<boolean>(isPublicProp);
  const nameGroup = '';

  const disable = checkDisableBtnCreate(
    isNewConversation,
    users,
    members,
    isAddMember
  );
  const isShowMoreOption = checkShowMoreOption(
    isNewConversation,
    users,
    isAddMember
  );
  const title = getTitleConversation(isNewConversation, users, isAddMember);
  const textBtnSubmit = isAddMember ? 'add' : 'create_conversation';

  const isSelfChat = React.useMemo(
    () => users.find(user => user.value === userSession?.username),
    [users, userSession]
  );

  React.useEffect(() => {
    if (isSelfChat) {
      dispatch({
        type: 'chatplus/chatRoom/newConversation',
        payload: { users }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isSelfChat, users]);

  const onSubmit = React.useCallback(() => {
    const memberCount = users.length;

    if (memberCount) {
      if (isAddMember && roomId) {
        const userPayload = users.map(u => u.value);

        dispatch({
          type: 'chatplus/room/addUsers',
          payload: { rid: roomId, users: userPayload, isAddMember },
          meta: { onSuccess: closeDialog }
        });
      } else {
        dispatch({
          type: 'chatplus/chatRoom/newConversation',
          payload: { users, readonly, isPublic, nameGroup }
        });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch, users, readonly, isPublic, nameGroup, roomId, isAddMember]);

  return (
    <Dialog maxWidth="sm" fullWidth {...dialogProps}>
      <DialogTitle disableClose={false}>
        {i18n.formatMessage({ id: title })}
      </DialogTitle>
      <DialogContent sx={{ p: 0 }}>
        <SearchUsers
          members={members}
          users={users}
          setUsers={setUsers}
          placeholder={i18n.formatMessage({ id: 'search_people' })}
          variant={isNewConversation && users.length === 0 ? 'direct' : 'group'}
          isShowButtonSubmit={false}
          height={260}
        />
      </DialogContent>
      {isShowMoreOption ? (
        <div style={{ padding: '8px 16px' }}>
          <CheckboxLabels
            readonly={readonly}
            isPublic={isPublic}
            setReadonly={setReadonly}
            setIsPublic={setIsPublic}
          />
        </div>
      ) : null}
      <Divider />
      <DialogActions>
        <Button
          disabled={disable}
          onClick={onSubmit}
          color="primary"
          autoFocus
          variant="contained"
          children={i18n.formatMessage({ id: textBtnSubmit })}
        />
      </DialogActions>
    </Dialog>
  );
}

export default MultipleConversationPicker;
