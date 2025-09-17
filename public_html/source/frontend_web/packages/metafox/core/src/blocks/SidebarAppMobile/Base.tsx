import {
  SideMenuBlockProps,
  useAppUI,
  useGlobal,
  useLocation,
  HistoryState
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import {
  IconButton,
  Popover,
  styled,
  Box,
  useScrollTrigger
} from '@mui/material';
import React, { useEffect, useRef, useState } from 'react';

const Root = styled('div', { name: 'Root' })<{
  visibleAppBar?: boolean;
  minHeight?: number;
}>(({ theme, visibleAppBar, minHeight }) => ({
  position: 'fixed',
  top: minHeight,
  left: 0,
  right: 0,
  zIndex: theme.zIndex.speedDial,
  transitionDuration: '.5s',
  boxShadow:
    '0px 2px 1px 0 rgba(0, 0, 0, 0.05), 0px -2px 1px 0 rgba(0, 0, 0, 0.05)',
  ...(!visibleAppBar && {
    top: 0
  })
}));

const HeightFixed = styled('div', { name: 'HeightFixed' })<{
  heightApp?: number;
}>(({ heightApp }) => ({
  height: heightApp
}));

const PopoverWrapper = styled(Popover, { name: 'PopoverWrapper' })<{
  visibleAppBar?: boolean;
  top?: number;
}>(({ theme, visibleAppBar, top }) => ({
  paddingTop: theme.mixins.pxToRem(16),
  minWidth: '100%',
  top: theme.appBarMobileConfig?.brand ?? '48px',
  bottom: 0,
  '.appbar-bottom &': {
    bottom: theme.appBarMobileConfig?.bottom ?? '48px'
  },
  '& .MuiPopover-paper': {
    maxWidth: '100%',
    width: '100%',
    borderRadius: 0,
    top: `${top}px !important`,
    bottom: 0,
    left: '0 !important'
  },
  '& .MuiPaper-elevation8': {
    overflow: 'hidden',
    boxShadow: 'unset'
  },
  ...(!visibleAppBar && {
    top: 0,
    bottom: '0 !important'
  })
}));

const HeaderBlock = styled('div', {
  name: 'HeaderBlock'
})(({ theme }) => ({
  width: '100%',
  display: 'flex',
  justifyContent: 'space-between',
  backgroundColor: theme.palette.background.paper,
  padding: theme.spacing(2, 2)
}));

const IconBack = styled(IconButton, {
  name: 'IconBack',
  shouldForwardProp: props => props !== 'backPage'
})<{ backPage?: boolean }>(({ theme, backPage }) => ({
  marginLeft: theme.spacing(-1),
  marginRight: theme.spacing(0.5),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(18),
    color: theme.palette.text.primary,
    fontWeight: theme.typography.fontWeightBold
  }
}));

const TitleWrapper = styled(Box, { name: 'TitleWrapper' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const Title = styled('div', { name: 'Title' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  fontSize: theme.mixins.pxToRem(24),
  fontWeight: theme.typography.fontWeightMedium
}));

const Icon = styled(IconButton, { name: 'Icon' })(({ theme }) => ({
  minWidth: theme.mixins.pxToRem(32),
  padding: 0
}));

const ContentWrapper = styled('div', { name: 'ContentWrapper' })(
  ({ theme }) => ({
    width: '100%'
  })
);

export type Props = SideMenuBlockProps & {
  menuName: string;
  appName?: string;
  contents: { name: string; props?: any }[];
  keepMounted?: boolean;
};

export default function SideAppMobileBlock({
  blockProps,
  appName,
  menuName,
  contents,
  title: initialTitle,
  keepMounted = false
}: Props) {
  const { usePageParams, jsxBackend, i18n, goSmartBack, useTheme } =
    useGlobal();
  const theme = useTheme();
  const { appName: pageAppName, backPage } = usePageParams();
  const sidebarHeader = useAppUI(pageAppName, 'homepageHeader');
  const { pathname: _pathname, search, state } = useLocation<HistoryState>();
  const scrollTrigger = useScrollTrigger();
  const minHeight = theme.appBarMobileConfig?.nav ?? 48;

  const ref = useRef(null);

  const [heightApp, setHeightApp] = useState(0);

  useEffect(() => {
    setHeightApp(ref.current.clientHeight);
  }, []);

  const [open, setOpen] = useState(false);
  const anchorRef = useRef(null);
  const pathname = state?.as || _pathname;

  const Components =
    contents?.length > 0
      ? contents
          .map(item => ({
            name: jsxBackend.get(item.name),
            props: item.props || {}
          }))
          .filter(item => item.name)
      : [];

  useEffect(() => {
    return () => {
      setOpen(false);
    };
  }, [pathname, search]);

  if (!sidebarHeader) return null;

  const { title } = sidebarHeader;

  const handleClose = () => {
    setOpen(false);
  };

  const handleClick = () => {
    setOpen(prev => !prev);
  };

  return (
    <>
      <HeightFixed heightApp={heightApp} />
      <Root ref={ref} minHeight={minHeight} visibleAppBar={!scrollTrigger}>
        <Box>
          <HeaderBlock>
            <TitleWrapper>
              {backPage ? (
                <IconBack size="small" onClick={() => goSmartBack()}>
                  <LineIcon icon="ico-angle-left"></LineIcon>
                </IconBack>
              ) : null}
              <Title>{i18n.formatMessage({ id: initialTitle || title })}</Title>
            </TitleWrapper>
            <Icon
              onClick={handleClick}
              variant="outlined-square"
              size="small"
              color="primary"
              ref={anchorRef}
              disableRipple="true"
            >
              <LineIcon
                icon={open ? 'ico-angle-up' : 'ico-angle-down'}
              ></LineIcon>
            </Icon>
            <PopoverWrapper
              visibleAppBar={!scrollTrigger}
              top={heightApp}
              open={open}
              keepMounted={keepMounted}
              anchorEl={anchorRef.current}
              onClose={handleClose}
              disablePortal
              transitionDuration={0}
              anchorOrigin={{
                vertical: 'bottom',
                horizontal: 'left'
              }}
              transformOrigin={{
                vertical: 'bottom',
                horizontal: 'left'
              }}
            >
              <ScrollContainer autoHide autoHeightMax={'100%'}>
                <ContentWrapper>
                  {Components.map((Component, index) => (
                    <Component.name
                      key={index.toString()}
                      {...Component.props}
                      isSidebarAppMobile
                      visibleAppBar={!scrollTrigger}
                    />
                  ))}
                </ContentWrapper>
              </ScrollContainer>
            </PopoverWrapper>
          </HeaderBlock>
        </Box>
      </Root>
    </>
  );
}
