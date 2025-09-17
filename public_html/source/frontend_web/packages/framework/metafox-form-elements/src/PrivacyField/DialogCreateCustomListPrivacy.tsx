/**
 * @type: dialog
 * name: dialog.DialogCreateCustomListPrivacy
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@mui/material';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import produce from 'immer';
import React from 'react';
import useStyles from './DialogCreateCustomListPrivacy.styles';
import FriendSuggestionEntry from './FriendSuggestionEntry';

export default function DialogCreateCustomListPrivacy(props) {
  const { customList } = props;
  const classes = useStyles();
  const { useDialog, i18n, apiClient } = useGlobal();
  const { dialogProps, closeDialog, setDialogValue } = useDialog();
  const [listName, setListName] = React.useState('');
  const [friends, setFriends] = React.useState([]);
  const [displayFriends, setDisplayFriends] = React.useState([]);
  const [error, setError] = React.useState({ listName: false, friends: false });
  const [openSuggestions, setOpenSuggestions] = React.useState<boolean>(false);
  const [suggestions, setSuggestions] = React.useState([]);
  const addFriendsRef = React.useRef();

  const onSearchChange = React.useCallback(
    ({ value }: { value: string }) => {
      apiClient
        .request({
          method: 'get',
          url: '/friend',
          params: { q: value, limit: 6 }
        })
        .then(res => (res.data?.data?.length ? res.data?.data : []))
        .then(items => {
          return items.map(item => ({
            avatar: getImageSrc(item.avatar, '240'),
            name: item.full_name,
            link: `/${item.resource_name}/${item.id}`
          }));
        })
        .then(items => setSuggestions(items));
    },
    [apiClient]
  );

  const handleCreate = () => {
    let err = false;

    if (checkDuplicateName(listName) || '' === listName) {
      setError(prev => ({ ...prev, listName: true }));
      err = true;
    }

    if (0 === displayFriends.length) {
      setError(prev => ({ ...prev, friends: true }));
      err = true;
    }

    if (!err) {
      closeDialog();
      setDialogValue(listName);
      // @todo:
      // handle add new list: listName, friends
    }
  };

  const handleChangeListName = (event: any) => {
    setError(prev => ({ ...prev, listName: false }));
    const name = event.target.value;
    setListName(name);

    if (checkDuplicateName(name)) {
      setError(prev => ({ ...prev, listName: true }));
    }
  };

  const handleChangeFriends = (event: any) => {
    setError(prev => ({ ...prev, friends: false }));
    const keySearch = event.target.value;
    setFriends(keySearch);
    onSearchChange({ value: keySearch });
    setOpenSuggestions(true);
  };

  const checkDuplicateName = name => {
    return customList.includes(name);
  };

  const onSelectedUser = user => {
    setDisplayFriends(prev => [...prev, user]);
    setFriends([]);
    setOpenSuggestions(false);

    if (addFriendsRef) {
      addFriendsRef?.current?.focus();
    }

    setError(prev => ({ ...prev, friends: false }));
  };

  const removeFriend = index => {
    setDisplayFriends(
      produce(displayFriends, draft => {
        draft.splice(index, 1);
      })
    );

    if (0 === displayFriends.length) {
      setError(prev => ({ ...prev, friends: true }));
    }
  };

  return (
    <Dialog {...dialogProps}>
      <DialogTitle>
        {i18n.formatMessage({ id: 'create_friends_list' })}
      </DialogTitle>
      <DialogContent>
        <TextField
          error={error.listName}
          placeholder={i18n.formatMessage({ id: 'list_name' })}
          label={i18n.formatMessage({ id: 'list_name' })}
          variant="outlined"
          fullWidth
          margin="normal"
          size="small"
          value={listName}
          onChange={e => handleChangeListName(e)}
          autoFocus
        />
        {displayFriends && 0 < displayFriends.length ? (
          <div className={classes.userWrapper}>
            {displayFriends.map((user, index) => (
              <div key={index.toString()} className={classes.userOuter}>
                <span className={classes.name}>{user.name}</span>
                <LineIcon
                  icon="ico-close"
                  className={classes.removeBtn}
                  onClick={() => removeFriend(index)}
                />
              </div>
            ))}
          </div>
        ) : null}
        <TextField
          error={error.friends}
          placeholder={i18n.formatMessage({ id: 'add_friend' })}
          label={i18n.formatMessage({ id: 'add_friend' })}
          variant="outlined"
          fullWidth
          margin="normal"
          size="small"
          multiline
          value={friends}
          onChange={e => handleChangeFriends(e)}
          inputRef={addFriendsRef}
        />
        {openSuggestions && (
          <div className={classes.suggestionsWrapper}>
            {suggestions
              ? suggestions.map((user, index) => (
                  <FriendSuggestionEntry
                    key={index.toString()}
                    user={user}
                    onClick={() => onSelectedUser(user)}
                  />
                ))
              : null}
          </div>
        )}
      </DialogContent>
      <DialogActions>
        <div className={classes.actionWrapper}>
          <Button variant="outlined" onClick={closeDialog} size="small">
            {i18n.formatMessage({ id: 'back' })}
          </Button>
          <Button
            variant="contained"
            onClick={handleCreate}
            size="small"
            color="primary"
          >
            {i18n.formatMessage({ id: 'create' })}
          </Button>
        </div>
      </DialogActions>
    </Dialog>
  );
}
