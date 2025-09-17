/* eslint-disable eqeqeq */
import { useGlobal, MFOX_BUILD_TYPE } from '@metafox/framework';
import snippets from '@metafox/react-app/bundle-web/theme.style.editor';
import { Scrollbars } from '@metafox/scrollbars';
import { LineIcon, MenuItemShape } from '@metafox/ui';
import { filterShowWhen, withDisabledWhen } from '@metafox/utils';
import {
  Box,
  Button,
  IconButton,
  styled,
  Tooltip,
  Typography,
  Link
} from '@mui/material';
import React from 'react';
import deviceList from '../DeviceList';
import ScrollProvider from '../ScrollProvider';
import { EditMode } from '../types';
import DraggablePanel from './DraggablePanel';
import StyleEditor from './StyleEditor';
import MenuItem from './MenuItem';
import PublishButton from './PublishButton';
import DefaultThemeProvider from './DefaultThemeProvider';

const name = 'LayoutEditor';

const Title = styled(Box, {
  name,
  slot: 'Title'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 2, 1, 1),
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  fontWeight: 'bold'
}));

const Menu = styled('ul', {
  name,
  slot: 'Menu'
})<{}>(({ theme }) => ({
  listStyle: 'none outside',
  margin: 0,
  padding: theme.spacing(0, 0, 1, 0)
}));

export const ContentRoot = styled(Box, {
  name,
  slot: 'Content'
})(() => ({
  paddingBottom: 8
}));

export const LinkTo = styled(Link, {
  name,
  slot: 'Link'
})(() => ({
  display: 'flex'
}));

export const Footer = styled(Box, {
  name,
  slot: 'Footer',
  shouldForwardProp: prop => !/vertical/i.test(prop)
})<{ vertical?: boolean }>(({ vertical, theme }) => ({
  padding: theme.spacing(1, 2),
  borderTop: '1px solid rgba(0,0,0,0.15)',
  ...(vertical
    ? { display: 'flex', flexDirection: 'column' }
    : {
        display: 'flex',
        alignItems: 'center'
      })
}));

export const Content = ({ children }) => {
  const scrollRef = React.useRef();

  return (
    <ScrollProvider scrollRef={scrollRef}>
      <Scrollbars
        scrollRef={scrollRef}
        autoHeightMax="60vh"
        autoHeightMin="60vh"
        autoHeight
      >
        <ContentRoot>{children}</ContentRoot>
      </Scrollbars>
    </ScrollProvider>
  );
};

const BackButton = ({ onClick }) => {
  const { i18n } = useGlobal();

  return (
    <IconButton size="small" onClick={onClick}>
      <Tooltip title={i18n.formatMessage({ id: 'layout_back' })}>
        <LineIcon icon="ico-arrow-left" />
      </Tooltip>
    </IconButton>
  );
};

const Header = ({ title, handleBack, closeEditor }) => {
  const { i18n } = useGlobal();

  return (
    <>
      <Title>
        {title ? <BackButton onClick={handleBack} /> : null}
        <Typography variant="body2">{title}</Typography>
      </Title>
      <Tooltip title={i18n.formatMessage({ id: 'help' })}>
        <IconButton size="small">
          <LinkTo
            target="_blank"
            href="https://docs.phpfox.com/display/MFMAN/Enable+Live+Editor+mode"
            underline="none"
          >
            <LineIcon icon="ico-question-circle-o" />
          </LinkTo>
        </IconButton>
      </Tooltip>
      <Tooltip title={i18n.formatMessage({ id: 'close' })}>
        <IconButton size="small" onClick={closeEditor}>
          <LineIcon icon="ico-close-circle-o" />
        </IconButton>
      </Tooltip>
    </>
  );
};

export default function LayoutEditor() {
  const { eventCenter, i18n, useLayout, layoutBackend, dispatch } = useGlobal();
  const [title, setTitle] = React.useState<string>();
  const [editTheme, setEditTheme] = React.useState<boolean>(false);
  const [activeLtr, setActiveLtr] = React.useState<boolean>(false);
  const [themeSnippet, setThemeSnippet] = React.useState<any>();
  const [dirty, setDirty] = React.useState<boolean>(layoutBackend.isDirty());

  React.useEffect(() => {
    const token = eventCenter.on('layout.dirty.changed', setDirty);

    return () => eventCenter.off('layout.dirty.changed', token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleBack = React.useCallback(() => {
    if (themeSnippet) {
      setThemeSnippet(undefined);
      setTitle(i18n.formatMessage({ id: 'layout_edit_theme_styles' }));
    } else if (editTheme) {
      setEditTheme(false);
      setTitle(undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [editTheme, themeSnippet]);

  // eslint-disable-next-line react-hooks/exhaustive-deps
  const closeEditor = React.useCallback(
    () => dispatch({ type: '@layout/closeEditor' }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const {
    pageName,
    pageSize,
    previewDevice,
    templateName,
    layoutEditMode: editMode
  } = useLayout();

  const handleAction = (action: string, params?: object) => {
    switch (action) {
      case '@layout/editStyling':
        setEditTheme(true);
        layoutBackend.setEditMode(EditMode.launch);
        setTitle(i18n.formatMessage({ id: 'layout_edit_theme_styles' }));
        break;
      case '@layout/editThemeSnippet':
        setThemeSnippet((params as any).component);
        setTitle(
          (params as any).title
            ? i18n.formatMessage({ id: (params as any).title })
            : ''
        );
        break;
      case '@layout/toggleRtl':
        setActiveLtr(!activeLtr);
        dispatch({
          type: action,
          payload: {
            ...params,
            pageName,
            pageSize,
            templateName
          }
        });
        break;
      default:
        dispatch({
          type: action,
          payload: {
            ...params,
            pageName,
            pageSize,
            templateName
          }
        });
    }
  };

  const device = React.useMemo(() => {
    return deviceList.find(x => x.value === previewDevice);
  }, [previewDevice]);

  const layoutMenu: MenuItemShape[] = [
    {
      name: 'toggleGuest',
      label: i18n.formatMessage({ id: 'layout_toggle_guest_view' }),
      icon: 'ico-user1-minus-o',
      value: '@layout/asGuestMode',
      as: 'switch',
      active: layoutBackend.getAsGuestMode(),
      showWhen: ['falsy', 'admincp']
    },
    {
      as: 'divider'
    },
    {
      name: 'forScreen',
      label: i18n.formatMessage({ id: 'layout_for_screens_' }),
      icon: 'ico-desktop-o',
      labelSub: pageSize,
      as: 'popover',
      iconSecondary: 'ico-arrow-right',
      enabledWhen: ['and', ['falsy', 'previewDevice']],
      content: {
        component: 'layout.popup.ChoosePageSizePopup',
        props: {
          pageName,
          pageSize,
          handleAction
        }
      }
    },
    {
      name: 'leaveViewOn',
      label: i18n.formatMessage({ id: 'layout_leave_view_on_' }),
      value: '@layout/leavePreview',
      icon: 'ico-signout',
      showWhen: ['truthy', 'previewDevice']
    },
    {
      name: 'viewOn',
      label: i18n.formatMessage({ id: 'layout_view_on_' }),
      labelSub: device?.label,
      icon: 'ico-mobile-o',
      as: 'popover',
      active: !!previewDevice,
      iconSecondary: 'ico-arrow-right',
      enabledWhen: ['eq', 'editMode', EditMode.launch],
      content: {
        component: 'layout.popup.ChooseDevicePopup',
        props: {
          pageSize,
          pageName,
          handleAction
        }
      }
    },
    {
      name: 'editingLive',
      label: i18n.formatMessage({ id: 'layout_live_edit' }),
      value: '@layout/liveEdit',
      icon: 'ico-pencilline-o',
      active: editMode == EditMode.editLive,
      enabledWhen: ['falsy', 'previewDevice']
    },
    {
      name: 'editPageContent',
      label: i18n.formatMessage({ id: 'layout_edit_page_content' }),
      value: '@layout/editPageContent',
      icon: 'ico-list-sort',
      active: editMode == EditMode.editPageContent,
      enabledWhen: ['falsy', 'previewDevice']
    },
    {
      name: 'editSiteContent',
      label: i18n.formatMessage({ id: 'layout_edit_site_content' }),
      value: '@layout/editSiteContent',
      icon: 'ico-list-sort',
      active: editMode == EditMode.editSiteContent,
      enabledWhen: ['falsy', 'previewDevice']
    },
    {
      name: 'editLayout',
      label: i18n.formatMessage({ id: 'layout_edit_layout' }),
      value: '@layout/editLayout',
      icon: 'ico-casual-o',
      active: editMode == EditMode.editLayout,
      enabledWhen: ['falsy', 'previewDevice']
    },
    {
      name: 'chooseLayout',
      label: i18n.formatMessage({ id: 'layout_change_layout' }),
      icon: 'ico-th-o',
      value: '@layout/chooseLayout',
      enabledWhen: ['and', ['falsy', 'previewDevice']]
    },
    {
      name: 'editingTheme',
      label: i18n.formatMessage({ id: 'layout_edit_theme_styles' }),
      icon: 'ico-color-palette',
      iconSecondary: 'ico-arrow-right',
      value: '@layout/editStyling',
      enabledWhen: ['and', ['falsy', 'previewDevice']]
    },
    {
      name: 'editStyle',
      label: i18n.formatMessage({ id: 'layout_edit_presets' }),
      icon: 'ico-list-o',
      as: 'popover',
      iconSecondary: 'ico-arrow-right',
      content: {
        component: 'layout.popup.ManagePresetPopup',
        props: {
          pageSize,
          pageName,
          handleAction
        }
      }
    },
    {
      as: 'divider'
    },
    {
      name: 'toggleRtl',
      label: i18n.formatMessage({
        id: activeLtr ? 'layout_toggle_ltr' : 'layout_toggle_rtl'
      }),
      icon: activeLtr ? 'ico-goright' : 'ico-goleft',
      value: '@layout/toggleRtl'
    },
    {
      name: 'toggleDarkMode',
      label: i18n.formatMessage({ id: 'layout_toggle_dark_mode' }),
      icon: 'ico-lightbub',
      value: '@layout/toggleDarkMode'
    },
    {
      name: 'manageLayouts',
      label: i18n.formatMessage({ id: 'layout_manage_layouts' }),
      icon: 'ico-list-o',
      value: '@layout/manageLayouts',
      showWhen: ['and', ['falsy', 'previewDevice'], ['truthy', 'development']]
    },
    {
      as: 'divider'
    },
    {
      name: 'saveLayouts',
      label: i18n.formatMessage({ id: 'save_changes' }),
      icon: 'ico-save',
      value: '@layout/saveTheme',
      enabledWhen: ['and', ['truthy', 'dirty'], ['falsy', 'previewDevice']]
    },
    {
      name: 'viewThemeHistory',
      label: i18n.formatMessage({ id: 'layout_view_histories' }),
      icon: 'ico-calendar-o',
      value: '@layout/viewHistory'
    },
    {
      name: 'discardChanges',
      label: i18n.formatMessage({ id: 'layout_discard_changes' }),
      icon: 'ico-rotate-left-alt',
      value: '@layout/discardChanges'
    },
    {
      name: 'resetPage',
      label: i18n.formatMessage({
        id: 'layout_reset_page',
        defaultMessage: 'Reset Page'
      }),
      icon: 'ico-refresh-o',
      value: '@layout/resetPage'
    },
    {
      name: 'resetPage',
      label: i18n.formatMessage({
        id: 'layout_reset_site_content',
        defaultMessage: 'Reset Site Block'
      }),
      icon: 'ico-refresh-o',
      value: '@layout/resetSite'
    },
    {
      name: 'inspectTheme',
      label: i18n.formatMessage({ id: 'layout_inspect_theme' }),
      icon: 'ico-cloud-alt-o',
      value: '@layout/inspectTheme',
      enabledWhen: ['and', ['falsy', 'previewDevice']],
      showWhen: ['truthy', 'development']
    },
    {
      name: 'inspectPage',
      label: i18n.formatMessage({ id: 'layout_inspect_page' }),
      icon: 'ico-cloud-alt-o',
      value: '@layout/inspectPage',
      showWhen: ['and', ['truthy', 'development']]
    }
  ];

  const themeMenu: MenuItemShape[] = Object.keys(snippets).map(name => {
    const item = snippets[name];

    return {
      name,
      label: item.title ? i18n.formatMessage({ id: item.title }) : '',
      icon: item.icon ?? 'ico-color-palette',
      iconSecondary: 'ico-arrow-right',
      value: '@layout/editThemeSnippet',
      params: item
    };
  });

  const context = {
    previewDevice,
    editMode,
    editTheme,
    dirty,
    development: process.env.NODE_ENV === 'development',
    admincp: MFOX_BUILD_TYPE === 'admincp'
  };

  const themeItems = filterShowWhen(themeMenu, context);

  const layoutItems = withDisabledWhen(
    filterShowWhen(layoutMenu, context),
    context
  );

  return (
    <DefaultThemeProvider>
      <DraggablePanel
        header={
          <Header
            title={title}
            closeEditor={closeEditor}
            handleBack={handleBack}
          />
        }
      >
        {editTheme && themeSnippet ? (
          <StyleEditor component={themeSnippet} />
        ) : null}
        {editTheme && !themeSnippet ? (
          <>
            <Content>
              <Menu>
                {themeItems.map((item, index) => (
                  <MenuItem
                    {...item}
                    handleAction={handleAction}
                    key={index.toString()}
                  />
                ))}
              </Menu>
            </Content>
            <Footer vertical>
              <Box>
                <Button
                  fullWidth
                  size="small"
                  variant="outlined"
                  onClick={() => handleAction('@layout/resetVariant')}
                >
                  {i18n.formatMessage({ id: 'reset' })}
                </Button>
              </Box>
            </Footer>
          </>
        ) : null}
        {editTheme ? null : (
          <>
            <Content>
              <Menu>
                {layoutItems.map((item, index) => (
                  <MenuItem
                    {...item}
                    dense
                    handleAction={handleAction}
                    key={index.toString()}
                  />
                ))}
              </Menu>
            </Content>
            <Footer vertical>
              <Box>
                <Button
                  fullWidth
                  size="small"
                  variant="outlined"
                  disabled={editMode === EditMode.launch}
                  onClick={() => handleAction('@layout/liveView')}
                >
                  {i18n.formatMessage({ id: 'preview' })}
                </Button>
                <Box sx={{ pt: 1 }}>
                  <PublishButton
                    fullWidth
                    size="small"
                    variant="outlined"
                    onClick={() => handleAction('@layout/publishLayouts')}
                  >
                    {i18n.formatMessage({ id: 'publish_layout' })}
                  </PublishButton>
                </Box>
              </Box>
            </Footer>
          </>
        )}
      </DraggablePanel>
    </DefaultThemeProvider>
  );
}
