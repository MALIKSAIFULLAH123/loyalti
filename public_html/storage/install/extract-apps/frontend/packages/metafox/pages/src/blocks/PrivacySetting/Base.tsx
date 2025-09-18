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
import { useFetchDetail } from '@metafox/rest-client';
import { APP_PAGE, RESOURCE_PAGE } from '@metafox/pages';

export default function PrivacySetting({ title }: BlockViewProps) {
  const { loggedIn } = useSession();
  const pageParams = usePageParams();
  const { dispatch } = useGlobal();
  const dataSource = useResourceAction(
    APP_PAGE,
    RESOURCE_PAGE,
    'getPagePermission'
  );
  const [data, , error] = useFetchDetail({
    dataSource,
    pageParams
  });

  const handleChange = (value: number, var_name: string) => {
    dispatch({
      type: 'page/privacySetting/UPDATE',
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
