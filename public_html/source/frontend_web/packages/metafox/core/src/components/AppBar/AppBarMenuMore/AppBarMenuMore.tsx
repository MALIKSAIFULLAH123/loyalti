/**
 * @type: ui
 * name: appbar.menu.more
 * chunkName: appbarAs
 */
import {
  RefOf,
  RouteLink,
  useAppMenu,
  useGlobal,
  useLocation
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon, SearchBox, Popper } from '@metafox/ui';
import {
  Box,
  ListItem,
  ListItemAvatar,
  ListItemText,
  Paper,
  PopperProps,
  Typography
} from '@mui/material';
import { lighten, styled } from '@mui/material/styles';
import { isEmpty } from 'lodash';
import React, { useCallback, useMemo } from 'react';
import { filterShowWhen } from '@metafox/utils';

const StyledAvatar = styled(ListItemAvatar, {
  shouldForwardProp: props => props !== 'small'
})<{ small?: boolean }>(({ theme, small }) => ({
  fontSize: theme.mixins.pxToRem(24),
  width: theme.spacing(6),
  minWidth: `${theme.spacing(6)}`,
  height: theme.spacing(6),
  backgroundColor: '#e0dddd',
  color: theme.palette.mode === 'light' ? '#828080' : '#616161',
  borderRadius: '50%',
  marginRight: theme.spacing(1.25),
  paddingRight: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  ...(small && {
    width: theme.spacing(4),
    minWidth: `${theme.spacing(4)}`,
    height: theme.spacing(4)
  })
}));

const BlockHeader = styled(Box, {
  name: 'AppbarMenu'
})(({ theme }) => ({
  padding: theme.spacing(0.5, 2, 1, 2)
}));

const Content = styled(Box, {
  name: 'AppbarMenu'
})(({ theme }) => ({
  display: 'grid',
  gap: 2,
  [theme.breakpoints.up('md')]: {
    gridTemplateColumns: 'min-content min-content'
  }
}));

const Root = styled('div', { name: 'Root' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  flexDirection: 'column',
  padding: theme.spacing(2)
}));

const Title = styled(Typography, { name: 'Title' })(({ theme }) => ({
  fontWeight: 'bold',
  textAlign: 'center',
  fontSize: theme.mixins.pxToRem(18),
  [theme.breakpoints.down('xs')]: {
    fontSize: theme.mixins.pxToRem(15)
  }
}));

export default function AppBarMenuMore({
  anchorRef,
  enableSearch,
  closePopover,
  ...rest
}: PopperProps & {
  closePopover: any;
  enableSearch: boolean;
  anchorRef: RefOf<HTMLDivElement>;
}) {
  const primaryMenu = useAppMenu('core', 'dropdownMenu');
  const quickMenu = useAppMenu('core', 'quickCreateMenu');
  const [query, setQuery] = React.useState<string>('');
  const { i18n, getSetting, useSession, getAcl, useTheme } = useGlobal();
  const acl = getAcl();
  const { key } = useLocation();
  const setting = getSetting();
  const session = useSession();
  const theme = useTheme();
  const [open, setOpen] = React.useState<boolean>(false);
  const limit = 5;
  const location = useLocation() as any;

  const toggleOpen = useCallback(() => {
    setOpen(prev => !prev);
  }, []);

  React.useEffect(() => {
    closePopover();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key]);

  const mainMenuFilter = React.useMemo(() => {
    if (!primaryMenu?.items?.length) return [];

    return filterShowWhen(primaryMenu?.items, {
      setting,
      session,
      acl
    });
  }, [primaryMenu?.items, setting, session, acl]);

  const menu = React.useMemo(() => {
    if (!mainMenuFilter.length) return [];

    const x = query.trim().toLowerCase();

    if (isEmpty(x)) {
      return mainMenuFilter;
    }

    return mainMenuFilter.filter(
      item => item.label.toLowerCase().indexOf(x) !== -1
    );
  }, [mainMenuFilter, query]);

  const quickCreateMenu = React.useMemo(() => {
    if (!quickMenu?.items.length) return [];

    return filterShowWhen(quickMenu?.items, {
      setting,
      session,
      acl
    });
  }, [quickMenu?.items, setting, session, acl]);

  const menuQuick = useMemo(() => {
    if (isEmpty(quickCreateMenu)) return [];

    if (quickCreateMenu.length <= limit) {
      return quickCreateMenu;
    } else if (open) {
      return quickCreateMenu.filter(Boolean).concat({
        icon: 'ico-angle-up',
        testid: 'less',
        label: i18n.formatMessage({ id: 'less' }),
        onClick: toggleOpen
      });
    } else {
      return quickCreateMenu.slice(0, limit).concat({
        icon: 'ico-angle-down',
        testid: 'more',
        label: i18n.formatMessage({ id: 'more' }),
        onClick: toggleOpen
      });
    }
  }, [quickCreateMenu, limit, open, i18n, toggleOpen]);

  React.useEffect(() => {
    setQuery('');
    setOpen(false);
  }, [location.pathname]);

  React.useEffect(() => {
    if (!rest.open) {
      setQuery('');
    }
  }, [rest.open]);

  const placeholder = i18n.formatMessage({ id: 'search_menu' });

  return (
    <Popper
      id="popupMoreMenu"
      data-testid="popupMoreMenu"
      anchorEl={anchorRef.current}
      popperOptions={{
        strategy: 'fixed'
      }}
      {...rest}
    >
      <Box
        sx={{
          p: 2,
          borderRadius: 1,
          bgcolor: theme =>
            (theme.palette.mode === 'light'
              ? lighten(theme.palette.background.secondary, 0.8)
              : lighten(theme.palette.background.secondary, 0.1)),
          boxShadow: theme.shadows[8]
        }}
      >
        <Typography variant="h3" component="h4" sx={{ pb: 2 }}>
          {i18n.formatMessage({ id: 'menu' })}
        </Typography>
        <Content>
          <Paper sx={{ width: 360, py: 1 }} elevation={2}>
            {!enableSearch ? (
              <Box sx={{ p: [2, 2, 1, 2] }}>
                <SearchBox
                  autoFocus
                  name="q"
                  placeholder={placeholder}
                  type="search"
                  onChange={e => setQuery(e.target.value)}
                  autoComplete="off"
                  role="combobox"
                />
              </Box>
            ) : null}
            <ScrollContainer
              autoHide
              autoHeight
              autoHeightMax={'70vh'}
              style={{ height: '70vh' }}
            >
              {menu.filter(Boolean).map((item, index) => (
                <ListItem
                  key={index.toString()}
                  variant="contained"
                  to={item.to}
                  data-testid={item.testid || item.name}
                  component={RouteLink}
                >
                  <StyledAvatar>
                    <LineIcon icon={item.icon} />
                  </StyledAvatar>
                  <ListItemText primary={item.label} secondary={item.subInfo} />
                </ListItem>
              ))}
              {!menu.length ? (
                <Root>
                  <Title variant="h4">
                    {i18n.formatMessage({
                      id: 'no_dropdown_menus_are_found_label'
                    })}
                  </Title>
                </Root>
              ) : null}
            </ScrollContainer>
          </Paper>
          {menuQuick.length ? (
            <Box>
              <Paper sx={{ width: 250, py: 1, ml: 1 }} elevation={2}>
                <ScrollContainer autoHide autoHeight autoHeightMax={'78vh'}>
                  <BlockHeader>
                    <Typography variant="h4" component="h4">
                      {i18n.formatMessage({ id: 'create' })}
                    </Typography>
                  </BlockHeader>
                  {menuQuick.filter(Boolean).map((item, index) => (
                    <ListItem
                      key={index.toString()}
                      variant="contained"
                      to={item.to}
                      data-testid={item.testid || item.name}
                      component={RouteLink}
                      onClick={item?.onClick || null}
                    >
                      <StyledAvatar small={1}>
                        <LineIcon icon={item.icon} sx={{ fontSize: 20 }} />
                      </StyledAvatar>
                      <ListItemText primary={item.label} />
                    </ListItem>
                  ))}
                </ScrollContainer>
              </Paper>
            </Box>
          ) : null}
        </Content>
      </Box>
    </Popper>
  );
}
