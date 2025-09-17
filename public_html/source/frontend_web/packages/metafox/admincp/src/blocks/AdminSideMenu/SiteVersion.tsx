import { Box, styled } from '@mui/material';
import React from 'react';
import { getAdminSiteStatus } from '@metafox/admincp/actions';
import { useGlobal, GlobalState } from '@metafox/framework';
import { useSelector } from 'react-redux';

const name = 'AdminCpSideMenu';

const SiteVersion = styled(Box, { name, slot: 'SiteVersion' })(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  color: 'rgba(238,238,238,0.7)',
  marginTop: 'auto'
}));

export default function AdminSideMenu() {
  const { dispatch, i18n } = useGlobal();
  const siteStatus = useSelector<GlobalState>(state => state.admincp.status);
  const version = siteStatus?.data?.version;

  React.useEffect(() => {
    dispatch(getAdminSiteStatus(false));
  }, []);

  if (!version) return null;

  return (
    <SiteVersion>
      {`${i18n.formatMessage({
        id: 'site_version'
      })}: ${version}`}
    </SiteVersion>
  );
}
