import { useGlobal } from '@metafox/framework';
import { Box, styled, Typography, Link } from '@mui/material';
import React from 'react';
import { ProductContext } from './AdminAppStoreShowDetail';

const VersionWrapper = styled(Box, { name: 'versionWrapper' })(({ theme }) => ({
  border: 'solid 1px rgba(85, 85, 85, 0.1)',
  width: '100%',
  maxWidth: '100%',
  padding: theme.spacing(3)
}));

const VersionInfo = styled(Box, { name: 'versionInfo' })(({ theme }) => ({
  alignItems: 'center',
  justifyContent: 'space-between',
  height: 'fit-content',
  display: 'flex',
  '&:not(:first-of-type)': {
    paddingTop: theme.spacing(1)
  }
}));

const Summary = () => {
  const { i18n } = useGlobal();
  const item = React.useContext(ProductContext);

  return (
    <VersionWrapper>
      <VersionInfo>
        <Typography variant="subtitle2" color="text.secondary">
          {i18n.formatMessage({ id: 'installable_version' })}
        </Typography>
        <Typography color="text.secondary" variant="body2">
          {item?.version}
        </Typography>
      </VersionInfo>
      <VersionInfo>
        <Typography variant="subtitle2" color="text.secondary">
          {i18n.formatMessage({ id: 'latest_version' })}
        </Typography>
        <Typography color="text.secondary" variant="body2">
          {item?.latest_version}
        </Typography>
      </VersionInfo>
      <VersionInfo>
        <Typography variant="subtitle2" color="text.secondary">
          {i18n.formatMessage({ id: 'last_updated' })}
        </Typography>
        <Typography color="text.secondary" variant="body2">
          {new Date(item?.updated_at).toDateString()}
        </Typography>
      </VersionInfo>
      <VersionInfo>
        <Typography variant="subtitle2" color="text.secondary">
          {i18n.formatMessage({ id: 'app_compatible' })}
        </Typography>
        <Typography color="text.secondary" variant="body2">
          {item?.compatible}
        </Typography>
      </VersionInfo>
      {item?.mobile_compatible && (
        <VersionInfo>
          <Typography variant="subtitle2" color="text.secondary">
            {i18n.formatMessage({ id: 'app_mobile_compatible' })}
          </Typography>
          <Typography color="text.secondary" variant="body2">
            {item?.mobile_compatible}
          </Typography>
        </VersionInfo>
      )}
      {item?.term_url && (
        <VersionInfo>
          <Typography variant="subtitle2" color="text.secondary">
            {i18n.formatMessage({ id: 'terms_of_service' })}
          </Typography>
          <Link href={item.term_url} target="_blank" color="primary">
            {i18n.formatMessage({ id: 'view' })}
          </Link>
        </VersionInfo>
      )}
      {item?.demo_url && (
        <VersionInfo>
          <Typography variant="subtitle2" color="text.secondary">
            {i18n.formatMessage({ id: 'live_demo' })}
          </Typography>
          <Link href={item?.demo_url} target="_blank" color="primary">
            {i18n.formatMessage({ id: 'view' })}
          </Link>
        </VersionInfo>
      )}
    </VersionWrapper>
  );
};

export default Summary;
