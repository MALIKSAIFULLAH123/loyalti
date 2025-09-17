import { MenuShape, useGlobal } from '@metafox/framework';
import { ScrollProvider, UIBlockProps } from '@metafox/layout';
import { Scrollbars } from '@metafox/scrollbars';
import { filterShowWhen } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import React from 'react';
import { useLocation } from 'react-router-dom';
import SiteVersion from './SiteVersion';

export type Props = UIBlockProps & {
  drawerVisible: boolean;
  isDesktop?: boolean;
  toggleDrawer?: () => void;
};

export function getIndicatePaths(pathname: string): string[] {
  const indicates = [];

  const parts = pathname.split('/').filter(Boolean);

  parts.forEach((_, index) => {
    if (index > 0) indicates.unshift(`/${parts.slice(0, index).join('/')}`);
  });
  indicates.unshift(pathname);

  return indicates;
}

const useMenuPathMap = (menu: MenuShape) => {
  const result = {};

  const paths = React.useMemo(() => {
    return menu.items
      .reduce((acc, value) => {
        acc.push(value.to);

        if (value.items) {
          value.items.forEach(item => {
            acc.push(item.to);
          });
        }

        return acc;
      }, [])
      .filter(Boolean);
  }, [menu]);

  paths.forEach(str => {
    result[str] = str; // indicate first
  });

  paths.forEach(path => {
    getIndicatePaths(path).forEach(str => {
      if (result[str]) return;

      result[str] = path;
    });
  });

  return result;
};

const useActiveMenuPath = (menu: MenuShape, pathname: string): string => {
  const map = useMenuPathMap(menu);

  const indicate = getIndicatePaths(pathname).find(str => map[str]);

  const matchPath = map[indicate];

  if (matchPath) {
    // find parent paths
    return matchPath;
  }

  return '';
};

const Root = styled(Box, {
  name: 'AdminSideMenu',
  slot: 'Root',
  shouldForwardProp: prop => prop !== 'mobile' && prop !== 'drawerVisible'
})<{ mobile?: boolean; drawerVisible?: boolean }>(
  ({ mobile, theme, drawerVisible }) => ({
    position: 'relative',
    zIndex: 99,
    ...(mobile
      ? {
          zIndex: theme.zIndex.appBar + 2,
          width: 48
        }
      : {
          width: drawerVisible ? 256 : 48
        })
  })
);

const Staged = styled(Box, {
  name: 'AdminSideMenu',
  slot: 'Staged',
  shouldForwardProp: prop => prop !== 'drawerVisible'
})<{ drawerVisible?: boolean }>(({ theme, drawerVisible }) => ({
  position: 'fixed',
  width: drawerVisible ? 256 : 48,
  top: 58,
  bottom: 0,
  left: 0,
  background: 'rgb(35, 48, 68)',
  overflowX: 'hidden',
  overflowY: 'auto'
}));

const Content = styled(Box, {
  name: 'AdminSideMenu',
  slot: 'Content'
})(({ theme }) => ({
  paddingTop: '12px',
  paddingBottom: theme.spacing(10)
}));

export default function AdminSideMenu({
  drawerVisible,
  isDesktop = true
}: Props) {
  const {
    usePageParams,
    useSession,
    getAcl,
    getSetting,
    jsxBackend,
    compactUrl,
    useAppMenu,
    useLoggedIn,
    dispatch,
    useIsMobile
  } = useGlobal();
  const { id } = usePageParams();
  const location = useLocation();
  const session = useSession();
  const acl = getAcl();
  const setting = getSetting();
  const menu = useAppMenu('core', 'adminSidebarMenu');
  const [hoverId, setHoverId] = React.useState<Number>(-1);

  const handleAction = React.useCallback(
    (type: string) => {
      dispatch({ type, payload: {} });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );
  const scrollRef = React.useRef();
  const loggedIn = useLoggedIn();
  const activePath = useActiveMenuPath(menu, location.pathname);
  const timeClear = React.useRef<any>();
  const isMobile = useIsMobile();

  const handlePopoverOpen = index => {
    clearTimeout(timeClear.current);
    setHoverId(index);
  };

  const handlePopoverClose = () => {
    timeClear.current = setTimeout(() => {
      setHoverId(-1);
    }, 500);
  };

  // filter based on showWhen property
  const items = filterShowWhen(menu.items, { session, acl, setting, isMobile });

  // has any items ?
  if (!items.length) {
    return null;
  }

  const indexActiveMenu = items.findIndex(parentMenu => {
    if (parentMenu.items && parentMenu.items.length) {
      return parentMenu.items.some(value => value.to === activePath);
    } else {
      return activePath === parentMenu.to;
    }
  });

  if (!loggedIn) return null;

  return (
    <Root
      mobile={!isDesktop}
      drawerVisible={drawerVisible}
      data-testid="layoutSlotSide"
    >
      <Staged drawerVisible={drawerVisible}>
        <ScrollProvider scrollRef={scrollRef}>
          <Scrollbars scrollRef={scrollRef}>
            <Box
              sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}
            >
              <Content>
                {items.map((item, index) =>
                  jsxBackend.render({
                    component: `adminSideMenu.as.${item.as || 'normal'}`,
                    props: {
                      key: index.toString(),
                      handleAction,
                      item: {
                        ...item,
                        to: item.to ? compactUrl(item.to, { id }) : undefined
                      },
                      pathname: activePath,
                      active: indexActiveMenu === index,
                      minimize: !drawerVisible,
                      isDesktop,
                      activeHover: hoverId === index,
                      handlePopoverOpen: () => handlePopoverOpen(index),
                      handlePopoverClose
                    }
                  })
                )}
              </Content>
              {drawerVisible ? <SiteVersion /> : null}
            </Box>
          </Scrollbars>
        </ScrollProvider>
      </Staged>
    </Root>
  );
}
