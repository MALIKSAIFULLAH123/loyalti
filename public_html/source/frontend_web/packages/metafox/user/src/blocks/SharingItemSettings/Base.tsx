import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Loading } from '@metafox/ui';
import { List, Typography } from '@mui/material';
import * as React from 'react';
import { AppState } from '../../types';
import ItemComponent from './ItemComponent';

export type Props = BlockViewProps & AppState['sharingItemPrivacy'];

export default function GeneralSettings({ data, title, blockProps }: Props) {
  const classes = {};
  const { dispatch, useSession, i18n } = useGlobal();
  const { user } = useSession();

  React.useEffect(() => {
    dispatch({
      type: 'setting/sharingItemPrivacy/FETCH',
      payload: { id: user.id }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dispatch]);

  const onChanged = (value: number, var_name: string) => {
    dispatch({
      type: 'setting/sharingItemPrivacy/UPDATE',
      payload: { var_name, value }
    });
  };

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <Typography variant="body1" paragraph>
          {i18n.formatMessage({ id: 'app_sharing_items_note' })}
        </Typography>
        <List disablePadding>
          {data ? (
            data.map((item, index) => (
              <ItemComponent
                classes={classes}
                onChanged={onChanged}
                item={item}
                index={index}
                key={item.var_name}
              />
            ))
          ) : (
            <Loading related center />
          )}
        </List>
      </BlockContent>
    </Block>
  );
}
