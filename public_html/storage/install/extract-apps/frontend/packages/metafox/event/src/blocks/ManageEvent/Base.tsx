import { APP_EVENT, EventDetailViewProps as Props } from '@metafox/event';
import { RemoteFormBuilder } from '@metafox/form';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { compactUrl } from '@metafox/utils';
import { Alert, Box, CircularProgress, styled } from '@mui/material';
import React from 'react';

const name = 'EventManage';
const BlockContentStyled = styled(BlockContent, { name })(({ theme }) => ({
  padding: theme.spacing(2),
  marginTop: theme.spacing(0.5),
  borderRadius: theme.shape.borderRadius,
  backgroundColor: theme.palette.background.paper
}));

export default function EventManage({ item, ...props }: Props) {
  const { i18n } = useGlobal();
  const config = useResourceAction(APP_EVENT, APP_EVENT, 'settingForm');

  if (!item) return null;

  const LoadingView = (
    <Box
      sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
    >
      <CircularProgress />
    </Box>
  );

  const form = (
    <RemoteFormBuilder
      dataSource={{ apiUrl: compactUrl(config?.apiUrl, { id: item.id }) }}
      loadingComponent={LoadingView}
      navigationConfirmWhenDirty
    />
  );

  const warning = (
    <Alert severity="warning">
      {i18n.formatMessage({ id: 'config_not_found' })}
    </Alert>
  );

  const content = config ? form : warning;

  return (
    <Block testid={`detailview ${item?.resource_name}`} {...props}>
      <BlockContentStyled>{content}</BlockContentStyled>
    </Block>
  );
}
