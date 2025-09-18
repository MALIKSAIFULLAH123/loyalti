import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useSession
} from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockHeader,
  usePageParams
} from '@metafox/layout';
import { SelectItem } from '@metafox/core';
import { List } from '@mui/material';
import * as React from 'react';
import { isArray } from 'lodash';
import { APP_GROUP, RESOURCE_GROUP } from '@metafox/group';
import { useFetchDetail } from '@metafox/rest-client';

export default function GroupPrivacy({ title }: BlockViewProps) {
  const { loggedIn } = useSession();
  const pageParams = usePageParams();
  const dataSource = useResourceAction(
    APP_GROUP,
    RESOURCE_GROUP,
    'getGroupPermission'
  );
  const [data, , error] = useFetchDetail({
    dataSource,
    pageParams
  });

  const { dispatch } = useGlobal();

  const handleChange = (value: number, var_name: string) => {
    dispatch({
      type: 'group/privacySetting/UPDATE',
      payload: { [var_name]: value, id: pageParams.id }
    });
  };

  if (!loggedIn || error) {
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
