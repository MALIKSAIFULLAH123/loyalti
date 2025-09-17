import { BlockViewProps, useGlobal } from '@metafox/framework';
import { AppBar, Toolbar, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import AppBarBranch from './AppBarBranch';
import AppBarLoginForm from './AppBarLoginForm';
import AppBarRegisterPage from './AppBarRegisterPage';
import AppBarSearch from './AppBarSearch';
import AppBarSubMenu from './AppBarSubMenu';
import Provider from './Context/Provider';

export type Props = BlockViewProps;

const PrimaryAppBar = styled(Box, {
  name: 'PrimaryAppBar',
  slot: 'Root'
})(({ theme }) => ({
  height: theme.appBarHeight.normal ?? 58,
  minHeight: theme.appBarHeight.normal ?? 58,
  minWidth: 1,
  position: 'relative',
  margin: 0,
  padding: 0,
  flexGrow: 1,
  flexBasis: '100%',
  width: '100%',
  '& form': {
    alignItems: 'end'
  }
}));

export default function PrimaryAppBarRoot(props) {
  const { useLoggedIn, usePageParams, getSetting, useTheme, getAcl } =
    useGlobal();
  const theme = useTheme();
  const loggedIn = useLoggedIn();
  const { menuHeaderGuestLogin } = usePageParams();
  const hasAdminAccess = getAcl('core.admincp.has_admin_access');

  const appbarRight = React.useMemo(() => {
    if (menuHeaderGuestLogin === 'hide') return null;

    return menuHeaderGuestLogin ? <AppBarRegisterPage /> : <AppBarLoginForm />;
  }, [menuHeaderGuestLogin]);
  const offline = getSetting('core.offline');

  if (!hasAdminAccess && offline) {
    return (
      <Provider>
        <PrimaryAppBar>
          <AppBar data-testid="appbar">
            <Box
              sx={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                height: '100%'
              }}
            >
              <AppBarBranch offline />
            </Box>
          </AppBar>
        </PrimaryAppBar>
      </Provider>
    );
  }

  return (
    <Provider>
      <PrimaryAppBar>
        <AppBar data-testid="appbar" sx={{ background: theme.palette?.appBar }}>
          <Toolbar>
            <AppBarBranch />
            {loggedIn ? (
              <>
                <AppBarSearch />
                <AppBarSubMenu />
              </>
            ) : (
              appbarRight
            )}
          </Toolbar>
        </AppBar>
      </PrimaryAppBar>
    </Provider>
  );
}
