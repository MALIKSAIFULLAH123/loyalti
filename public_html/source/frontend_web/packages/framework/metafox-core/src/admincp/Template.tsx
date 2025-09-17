/**
 * @type: ui
 * name: layout.masterTemplate
 * bundle: admincp
 */
import { Backdrop, useMediaQuery, useTheme, Box } from '@mui/material';
import React, { useState } from 'react';
import useStyles from './styles';
import { useGlobal, useLocation } from '@metafox/framework';

const STORAGE_KEY = 'sidebar_toggle';

export default function Template({ children }) {
  const classes = useStyles();
  const {
    jsxBackend,
    localStore,
    getAcl,
    BlockLoading,
    useAdmincpSiteLoading
  } = useGlobal();
  const initClose = localStore.get(STORAGE_KEY);
  const theme = useTheme();
  const isDesktop = useMediaQuery(theme.breakpoints.up('md'));

  const hasAdminAccess = getAcl('core.admincp.has_admin_access');
  const loading = useAdmincpSiteLoading();

  const [open, setOpen] = useState<boolean>(Boolean(isDesktop && !initClose));
  const location = useLocation() as any;

  const toggleDrawer = () => {
    setOpen(value => !value);
  };

  React.useEffect(() => {
    if (isDesktop) return;

    setOpen(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location.pathname, isDesktop]);

  React.useEffect(() => {
    localStore.set(STORAGE_KEY, open ? undefined : '1');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  if (!hasAdminAccess) return children;

  return (
    <div className={classes.siteWide}>
      {jsxBackend.render({
        component: 'core.block.AdminAppBar',
        props: { toggleDrawer, drawerVisible: open, isDesktop }
      })}
      {isDesktop ? null : (
        <Backdrop
          className={classes.backdrop}
          open={open}
          onClick={toggleDrawer}
        />
      )}
      <div className={classes.body}>
        {jsxBackend.render({
          component: 'core.block.AdminSideMenu',
          props: { toggleDrawer, drawerVisible: open, isDesktop }
        })}
        {loading ? (
          <Box
            sx={{
              position: 'fixed',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              zIndex: 99,
              background: 'rgba(255, 255, 255, 0.5)'
            }}
          >
            <BlockLoading loading />
          </Box>
        ) : null}
        <div className={classes.content}>
          {jsxBackend.render({
            component: 'core.block.AdminWarningBanners'
          })}
          {children}
        </div>
      </div>
    </div>
  );
}
