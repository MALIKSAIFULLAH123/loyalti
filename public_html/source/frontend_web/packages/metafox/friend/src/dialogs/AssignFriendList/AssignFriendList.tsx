/**
 * @type: dialog
 * name: friend.dialog.AssignFriendList
 */

import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { useFetchItems } from '@metafox/rest-client';
import { Button } from '@mui/material';
import { isEqual } from 'lodash';
import React from 'react';
import FriendListItem from './FriendListItem';
import NoFriendList from './NoFriendList';
interface Props {
  id: number;
}

export default function AssignFriendList({ id }: Props) {
  const { useDialog, i18n } = useGlobal();

  const [value, setValue] = React.useState<number[]>([]);

  const [newValue, loading1] = useFetchItems<number>({
    dataSource: {
      apiUrl: `/friend/list/assign/${id}`
    },
    data: []
  });

  React.useEffect(() => {
    setValue(newValue);
  }, [newValue]);

  const { dialogProps, setDialogValue } = useDialog();

  const saveChanges = () => {
    setDialogValue(value);
  };

  const handleChange = React.useCallback(
    (checked, id) => {
      setValue(prev => {
        return checked ? [...prev, id] : prev.filter(x => x !== id);
      });
    },
    [setValue]
  );

  const [items, loading] = useFetchItems({
    dataSource: { apiUrl: '/friend/list' },
    data: []
  });

  return (
    <Dialog maxWidth="xs" fullWidth {...dialogProps}>
      <DialogTitle>{i18n.formatMessage({ id: 'friend_list' })}</DialogTitle>
      <DialogContent variant="fitScroll" sx={{ height: '45vh' }}>
        <ScrollContainer>
          {loading || loading1
            ? [0, 5].map(index => (
                <FriendListItem.LoadingSkeleton key={index.toString()} />
              ))
            : null}
          {!loading && !loading1 && !items.length ? (
            <NoFriendList
              icon="ico-list-bullet-o"
              buttonCustom={{
                type: 'friend/addNewList',
                label: 'add_new_listing'
              }}
            />
          ) : null}
          {!loading && !loading1 && items.length
            ? items.map(item => (
                <FriendListItem
                  key={item.id.toString()}
                  item={item}
                  checked={value.includes(item.id)}
                  onChange={handleChange}
                />
              ))
            : null}
        </ScrollContainer>
      </DialogContent>
      <DialogActions>
        <Button
          disabled={isEqual(newValue, value)}
          color="primary"
          variant="contained"
          size="medium"
          onClick={saveChanges}
        >
          {i18n.formatMessage({ id: 'save_changes' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
