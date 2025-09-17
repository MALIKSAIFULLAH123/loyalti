import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { AppState } from '@metafox/user/types';
import { Box, Switch, Typography } from '@mui/material';
import * as React from 'react';

export type Props = BlockViewProps & AppState['invisibleSettings'];

export default function GeneralSettings({ data, title, blockProps }: Props) {
  const { dispatch } = useGlobal();

  React.useEffect(() => {
    dispatch({ type: 'setting/invisibleSettings/fetch' });
  }, [dispatch]);

  const onChanged = (value: number) => {
    dispatch({ type: 'setting/invisibleSettings/update', payload: value });
  };

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <Typography variant="body1" paragraph>
          {data.description ?? 'Invisible'}
        </Typography>
        <Box component="div" display="flex" justifyContent="space-between">
          <Typography component="div" variant="body1">
            {data.phrase}
          </Typography>
          <Switch
            size="medium"
            checked={!!data.value}
            color="primary"
            onChange={() => onChanged(data.value ? 0 : 1)}
          />
        </Box>
      </BlockContent>
    </Block>
  );
}
