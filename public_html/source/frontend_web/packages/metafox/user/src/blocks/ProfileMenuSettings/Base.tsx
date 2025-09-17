import { BlockViewProps, useGlobal, useSession } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { SelectItem } from '@metafox/core';
import { List } from '@mui/material';
import { isArray } from 'lodash';
import * as React from 'react';
import { AppState } from '../../types';

export type Props = BlockViewProps & AppState['profileMenu'];

export default function GeneralSettings({ data, title, blockProps }: Props) {
  const { dispatch } = useGlobal();
  const { user, loggedIn } = useSession();

  React.useEffect(() => {
    if (!loggedIn) return;

    dispatch({ type: 'setting/profileMenu/FETCH', payload: { id: user.id } });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch, loggedIn]);

  const handleChange = (value: number, var_name: string) => {
    dispatch({
      type: 'setting/profileMenu/UPDATE',
      payload: { [var_name]: value }
    });
  };

  if (!loggedIn) {
    return null;
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <List disablePadding>
          {isArray(data)
            ? data.map(menu => (
                <SelectItem
                  var_name={menu?.var_name}
                  onChanged={handleChange}
                  item={menu}
                  key={menu?.var_name}
                />
              ))
            : null}
        </List>
      </BlockContent>
    </Block>
  );
}
