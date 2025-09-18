/**
 * @type: dialog
 * name: dialog.DialogSelectRoomMembers
 */
import { useSessionUser } from '@metafox/chatplus/hooks';
import { UserShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  styled
} from '@mui/material';
import React, { useState } from 'react';
import MemberItem from './MemberItem';
import SearchMember from '../../SearchMember';
import { createStringMatcher } from '@metafox/chatplus/utils';

const ActionWrapper = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center'
}));
const ButtonCancel = styled(Button)(({ theme }) => ({
  marginRight: theme.spacing(1)
}));
const ContentWrapper = styled('div')(({ theme }) => ({}));
const UISelectAllBar = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center'
}));
const SelectSelect = styled(Button)(({ theme }) => ({
  ...theme.typography.body1,
  cursor: 'pointer',
  padding: theme.spacing(1, 0, 1, 2)
}));

function DialogSelectRoomMembers(props) {
  const {
    rid,
    limit,
    okLabel = 'continue',
    cancelLabel = 'cancel',
    title,
    alertMsg,
    onSuccess
  } = props;
  const { useDialog, i18n, dialogBackend, dispatch } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const user = useSessionUser();

  const [selectedUsernames, setSelectedUsernames] = useState([]);
  const [isValid, setIsValid] = useState(false);
  const [users, setUsers] = useState<UserShape[]>([]);
  const initData = React.useRef([]);

  React.useEffect(() => {
    handleChange();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rid]);

  const handleChange = () => {
    dispatch({
      type: 'chatplus/room/presentMembers',
      payload: {
        rid
      },
      meta: { onSuccess: handleSetUsers }
    });
  };

  const handleSetUsers = data => {
    if (Array.isArray(data.users)) {
      setUsers(data.users);

      initData.current = data.users;
    }
  };

  const alertReachLimit = () => {
    dialogBackend.alert({
      title: 'Oops!',
      message: i18n.formatMessage({ id: alertMsg }, { value: limit })
    });
  };

  const toggleSelect = ({ username }: UserShape) => {
    let data = [...selectedUsernames];

    if (selectedUsernames.includes(username)) {
      data = selectedUsernames.filter(x => x !== username);
    } else if (selectedUsernames.length < limit) {
      data.push(username);
    } else {
      alertReachLimit();
    }

    setSelectedUsernames(data);
    setIsValid(data.length > 0);
  };

  const selectAll = () => {
    const { username } = user;
    const data = users
      .map(({ username }) => username)
      .filter(x => x !== username);

    setSelectedUsernames(data);
    setIsValid(true);
  };

  const deselectAll = () => {
    setSelectedUsernames([]);
    setIsValid(false);
  };

  const handleStartCall = () => {
    if (onSuccess) {
      onSuccess(selectedUsernames);
    }

    closeDialog();
  };

  const handleSearch = query => {
    if (!query) {
      setUsers(initData.current);

      return;
    }

    const match = createStringMatcher(query);
    const data = users.filter(item => match(item?.name));

    setUsers(data);
  };

  const total = users.length;
  const canSelectAll =
    total > 0 && total < limit + 2 && selectedUsernames.length < limit;
  const numberSelected = selectedUsernames.length;
  const canDeSelectAll = numberSelected > 0;

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: title })}</DialogTitle>
      <DialogContent>
        <SearchMember
          placeholder="chatplus_search_for_members"
          onQueryChange={handleSearch}
          sx={{ margin: 0, marginBottom: theme => theme.spacing(1) }}
        />
        <div>
          <UISelectAllBar className="uiSelectAllBar">
            {canDeSelectAll ? (
              <SelectSelect onClick={deselectAll}>
                {i18n.formatMessage(
                  { id: 'deselect_value' },
                  { value: numberSelected }
                )}
              </SelectSelect>
            ) : null}
            {canSelectAll ? (
              <SelectSelect onClick={selectAll}>
                {i18n.formatMessage({ id: 'select_all' })}
              </SelectSelect>
            ) : null}
          </UISelectAllBar>
          <ContentWrapper>
            <ScrollContainer autoHide autoHeight>
              {users
                .filter(u => u.username !== user.username)
                .map((user, idx) => (
                  <MemberItem
                    key={`member-${idx}`}
                    user={user}
                    selected={selectedUsernames.includes(user.username)}
                    toggleSelect={toggleSelect}
                  />
                ))}
            </ScrollContainer>
          </ContentWrapper>
        </div>
      </DialogContent>
      <DialogActions>
        <ActionWrapper>
          <ButtonCancel variant="outlined" onClick={closeDialog} size="small">
            {i18n.formatMessage({ id: cancelLabel })}
          </ButtonCancel>
          <Button
            variant="contained"
            onClick={handleStartCall}
            size="small"
            color="primary"
            disabled={!isValid}
          >
            {i18n.formatMessage({ id: okLabel })}
          </Button>
        </ActionWrapper>
      </DialogActions>
    </Dialog>
  );
}

export default DialogSelectRoomMembers;
