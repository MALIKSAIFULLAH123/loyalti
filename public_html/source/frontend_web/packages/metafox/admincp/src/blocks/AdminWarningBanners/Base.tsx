import { useGlobal, useResourceAction } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import React from 'react';
import { uniq } from 'lodash';
import { Alert } from '@metafox/ui';

const Root = styled(Box, {
  name: 'AdminWarningBanners',
  slot: 'Content'
})(({ theme }) => ({
  display: 'block'
}));

export default function AdminWarningBanners() {
  const { useFetchItems, localStore, i18n } = useGlobal();
  const [filterIds, setFilterIds] = React.useState<Array<string | number>>(
    localStore.get('dismissAlert')
      ? JSON.parse(localStore.get('dismissAlert'))
      : []
  );
  const config = useResourceAction(
    'health-check',
    'health-check',
    'viewNotices'
  );
  const [dataAlerts, loading] = useFetchItems({
    dataSource: config,
    normalize: false
  });

  const handleClose = (id: number) => {
    localStore.set('dismissAlert', JSON.stringify(uniq([...filterIds, id])));
    setFilterIds(prev => uniq([...prev, id]));
  };

  const data = dataAlerts?.filter(x => !filterIds.includes(x.id));

  if (loading || !data?.length || !config) return null;

  return (
    <Root px={2} pt={1}>
      {data.map(item => (
        <Box mt={1} key={`k${item.id}`}>
          <Alert
            severity={item.severity}
            title={item.title || i18n.formatMessage({ id: 'warning' })}
            message={item.message}
            onClose={
              item.severity !== 'error' ? () => handleClose(item.id) : undefined
            }
            actions={item.actions}
          />
        </Box>
      ))}
    </Root>
  );
}
