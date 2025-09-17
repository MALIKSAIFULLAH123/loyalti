import { useGlobal, useResourceAction } from '@metafox/framework';
import { RemoteFormBuilder } from '@metafox/form';
import { Box, styled } from '@mui/material';
import React from 'react';
import LoadingComponent from './LoadingComponent';

const Panel = styled(Box, {
  name: 'Tab',
  slot: 'panel'
})<{ active?: boolean }>(({ theme, active }) => ({
  display: active ? 'block' : 'none'
}));

type TabContentProps = {
  actionName: string;
  isActive: boolean;
};

export default function TabContent({ actionName, isActive }: TabContentProps) {
  const { usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const { appName, resourceName } = pageParams;
  const dataSource = useResourceAction(appName, resourceName, actionName);

  if (!dataSource) return null;

  return (
    <Panel active={isActive}>
      <RemoteFormBuilder
        noHeader
        pageParams={{ ...pageParams, id: pageParams?.slug }}
        dataSource={dataSource}
        loadingComponent={LoadingComponent as any}
      />
    </Panel>
  );
}
