import { ListViewBlockProps, useGetItem, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, SearchBox } from '@metafox/layout';
import {
  LineIcon,
  UIBlockViewProps,
  ItemTabProps,
  TruncateText
} from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { filterShowWhen } from '@metafox/utils';
import { Menu, Tab, Tabs, styled } from '@mui/material';
import produce from 'immer';
import { camelCase, get, isArray } from 'lodash';
import qs from 'querystring';
import React, { useCallback, useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';

const name = 'TabContainer';

const MARGINLEFT_TAB_ITEM = 20;

const TabWrapperStyles = styled(Tabs, { name, slot: 'TabItemWrapper' })(
  ({ theme }) => ({
    '& .MuiTabs-flexContainer': {
      width: '100%'
    },
    [theme.breakpoints.down('sm')]: {
      width: '100%'
    }
  })
);

const TabMoreItem = styled(Tab, {
  name,
  slot: 'TabMoreItem'
})(({ theme }) => ({}));

const TabItemHidden = styled(Tab, { name, slot: 'TabItemHidden' })(
  ({ theme }) => ({
    color: theme.palette.text.secondary,
    fontWeight: 'bold',
    fontSize: theme.mixins.pxToRem(15),
    padding: '0 !important',
    width: 'fit-content !important',
    minWidth: 'fit-content !important',
    marginLeft: `${MARGINLEFT_TAB_ITEM}px`,
    '&:first-of-type': {
      marginLeft: theme.spacing(0)
    }
  })
);

const TabItemWrapper = styled(Tab, {
  name,
  slot: 'TabItemWrapper',
  shouldForwardProp: props =>
    props !== 'widthTabMore' && props !== 'numberTabItem'
})<{ widthTabMore?: number; numberTabItem?: number }>(
  ({ theme, widthTabMore, numberTabItem }) => ({
    color: theme.palette.text.secondary,
    fontWeight: 'bold',
    fontSize: theme.mixins.pxToRem(15),
    padding: '0 !important',
    marginLeft: `${MARGINLEFT_TAB_ITEM}px`,
    width: 'fit-content !important',
    minWidth: 'fit-content !important',
    '&:first-of-type': {
      marginLeft: theme.spacing(0)
    },
    maxWidth: 290,
    '& .tab-label-item': {
      maxWidth: 290
    },
    [theme.breakpoints.down('sm')]: {
      maxWidth: widthTabMore ? `calc(100% - ${widthTabMore}px)` : 200,
      ...(numberTabItem === 1 && {
        minWidth: 'auto !important',
        '& .tab-label-item': {
          maxWidth: '100%'
        }
      })
    }
  })
);

const SubTabWrapper = styled('div', {
  name,
  slot: 'subTabWrapper',
  shouldForwardProp: prop => prop !== 'pageDetail',
  overridesResolver(props, styles) {
    return [
      styles.subTabWrapper,
      props.pageDetail && styles.subTabWrapperPageDetail
    ];
  }
})<{ pageDetail?: boolean }>(({ theme, pageDetail }) => ({
  paddingBottom: theme.spacing(1.875),
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column-reverse',
    alignItems: 'flex-start',
    ...(pageDetail && {
      padding: theme.spacing(0, 2)
    })
  }
}));

const MenuItem = styled('div', { name: 'MenuItem' })<{
  tabItemActive?: boolean;
}>(({ theme, tabItemActive }) => ({
  width: 240,
  minHeight: '40px',
  display: 'block',
  padding: theme.spacing(1, 2),
  alignItems: 'center',
  justifyContent: 'center',
  textDecoration: 'none',
  fontSize: '15px',
  color: theme.palette.text.secondary,
  '&:hover': {
    textDecoration: 'none !important',
    backgroundColor: theme.palette.action.hover,
    cursor: 'pointer'
  },
  ...(tabItemActive && {
    color: `${theme.palette.primary.main} !important`
  })
}));

const SecondMenu = styled('div', { name: 'SecondMenu' })(({ theme }) => ({
  listStyle: 'none none outside',
  margin: 0,
  padding: 0,
  display: 'inline-flex'
}));

const HiddenTabs = styled('div', { name, slot: 'HiddenTabs' })(({ theme }) => ({
  visibility: 'hidden',
  position: 'absolute'
}));

const DisableGutter = styled('div', {
  name,
  slot: 'DisableGutter',
  shouldForwardProp: props => props !== 'disableGutter'
})<{
  disableGutter?: boolean;
}>(({ disableGutter }) => ({
  ...(disableGutter && {
    padding: 0
  })
}));

const TabItem = styled('div', { name, slot: 'TabItem' })(({ theme }) => ({
  height: '100%',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  float: 'left',
  textDecoration: 'none',
  fontSize: '15px',
  fontWeight: 'bold',
  color: `${theme.palette.text.secondary} !important`,
  position: 'relative',
  whiteSpace: 'nowrap',
  '&:hover': {
    textDecoration: 'none',
    color: `${theme.palette.primary.main} !important`
  },
  [theme.breakpoints.down('xs')]: {
    padding: `26px ${theme.spacing(1)}px`,
    marginBottom: 0
  },
  minWidth: 60,
  cursor: 'pointer',
  marginRight: 0,
  flexGrow: 1
}));

type TabContainerProps = UIBlockViewProps &
  ListViewBlockProps & {
    profileActionMenu?: string;
    hasSearchBox?: string;
    item: any;
    user: UserItemShape;
    pageDetail: boolean;
  };

const convertTabToArray = (tabs: any[]) => {
  if (!isArray(tabs)) return [];

  return tabs.filter(Boolean);
};

export default function TabContainer({
  title,
  tabProps = {
    tabs: [],
    tabsNoSearchBox: [],
    disableGutter: false,
    activeTab: '',
    placeholderSearch: 'search_dot'
  },
  elements,
  hasSearchBox,
  item,
  user,
  pageDetail = false,
  compose
}: TabContainerProps) {
  const {
    tabs: items,
    tabsNoSearchBox = [],
    disableGutter,
    activeTab,
    to
  } = tabProps;

  const {
    navigate,
    jsxBackend,
    useSession,
    usePageParams,
    i18n,
    useIsMobile,
    getAcl,
    compactUrl
  } = useGlobal();
  const isMobile = useIsMobile();
  const acl = getAcl();
  const defaultTab = React.useMemo(
    () => activeTab || convertTabToArray(tabProps.tabs)[0]?.tab || '',
    [activeTab, tabProps.tabs]
  );
  const [tab, setTab] = React.useState<string>(defaultTab);

  const location = useLocation();
  const refMenuMore = useRef(null);
  const [query, setQuery] = React.useState('');
  const session = useSession();
  const { user: authUser, loggedIn } = session;
  const pageParams = usePageParams();
  const detailUser = useGetItem(`user.entities.user.${authUser?.id}`);
  const [open, setOpen] = React.useState<boolean>(false);
  const element = elements.find(element => element.props.name === tab);

  const isAuthUser =
    (authUser?.id === item?.id || user?.id === authUser?.id) && loggedIn;
  const [state, setState] = React.useState<number>(0);
  const searchRef = useRef<HTMLDivElement>();
  const searchHiddenRef = useRef<HTMLDivElement>();
  const totalRef = useRef<HTMLDivElement>();
  const hiddenTabsRef = useRef<HTMLDivElement>();
  const moreTabRef = useRef<HTMLDivElement>();
  const tabRef = useRef<HTMLDivElement>();
  const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);

  const selectMoreTabItem = (event: any, tab: any) => {
    event.stopPropagation();
    setTab(tab);
    const stab = qs.stringify({ stab: tab });

    navigate(
      {
        pathname: location.pathname,
        search: `?${stab}`
      },
      {
        state: Object.assign({}, location.state, { keepScroll: true })
      }
    );

    closeMenu();
  };

  const displayTab = filterShowWhen(items, {
    isAuthUser,
    session,
    item,
    authUser: detailUser,
    acl
  }) as ItemTabProps[];

  const tabs = displayTab.map(item => item.tab);
  const tabValue = items.find(item => item.tab === tab);

  const translateTabLabel = React.useCallback(
    (tab, isHidden = true) => {
      const { statisticKey, label } = tab || {};

      if (!label) return null;

      const statistic = get({ item }, statisticKey);
      const text = i18n.formatMessage({ id: label }, { value: statistic || 0 });

      return (
        <TruncateText
          lines={1}
          variant="h5"
          {...(!isHidden && { className: 'tab-label-item' })}
        >
          {text}
        </TruncateText>
      );
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [item]
  );

  React.useEffect(() => {
    if (
      !tabs.includes(tab) &&
      tabValue &&
      tabValue?.redirectWhenNoPermission &&
      tabs.includes(tabValue?.redirectWhenNoPermission)
    ) {
      navigate({
        pathname: location.pathname,
        search: `?stab=${tabValue?.redirectWhenNoPermission || defaultTab}`
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tab, displayTab, items]);

  const moreTab = useCallback(
    (isHidden: Boolean) => (
      <SecondMenu ref={refMenuMore}>
        <TabItem>
          {i18n.formatMessage({ id: 'more' })}&nbsp;
          <LineIcon icon="ico-caret-down" />
        </TabItem>
        <Menu open={open} anchorEl={anchorEl} onClose={closeMenu}>
          {(state >= displayTab.length
            ? displayTab
            : displayTab.slice(state)
          ).map((itemTab, index) => (
            <MenuItem
              tabItemActive={itemTab.tab === tab}
              key={index.toString()}
              onClick={event => selectMoreTabItem(event, itemTab.tab)}
            >
              {translateTabLabel(itemTab)}
            </MenuItem>
          ))}
        </Menu>
      </SecondMenu>
    ),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [anchorEl, displayTab, open, selectMoreTabItem, state, tab]
  );

  const itemsNoSearchBox = convertTabToArray(tabsNoSearchBox);

  const hasSearch = hasSearchBox && !itemsNoSearchBox.includes(tab);

  const onResize = React.useCallback(() => {
    const cc: HTMLCollection[] = hiddenTabsRef.current?.children;

    if (!cc || !cc.length) return;

    const maxWidth = totalRef.current.getBoundingClientRect().width;
    const tabMoreWidth = moreTabRef.current?.getBoundingClientRect().width;

    const searchWidth =
      hasSearch && !isMobile
        ? searchHiddenRef.current?.getBoundingClientRect().width
        : 0;

    let totalWidth = 0;
    let index = 0;

    while (totalWidth + tabMoreWidth + searchWidth < maxWidth && cc[index]) {
      totalWidth +=
        cc[index]?.getBoundingClientRect()?.width +
        (index === 0 ? 0 : MARGINLEFT_TAB_ITEM);
      index++;
    }

    let countTabHasMore = index;

    if (maxWidth - totalWidth < tabMoreWidth) {
      countTabHasMore = index === 1 ? index : index - 1;
    }

    setState(countTabHasMore);
  }, [hasSearch, isMobile]);

  useEffect(() => {
    onResize();
  }, [onResize, tab, displayTab]);

  useEffect(() => {
    const { stab } = pageParams;
    const isTabOnDisplayTab = displayTab.some(tab => tab.tab === stab);

    if (!isTabOnDisplayTab && stab) {
      setTab(activeTab || convertTabToArray(tabProps.tabs)[0].tab);

      navigate({
        search: `?stab=${activeTab || convertTabToArray(tabProps.tabs)[0].tab}`
      });

      return;
    }

    if (!isArray(tabProps?.tabs)) return;

    setTab(stab || activeTab || convertTabToArray(tabProps.tabs)[0].tab);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tabProps.tabs, activeTab, pageParams]);

  const closeMenu = () => setOpen(false);

  const toggleOpen = (event: React.MouseEvent<HTMLButtonElement>) => {
    setOpen(true);
    setAnchorEl(event.currentTarget);
  };

  const handleChange = (_: any, tab: any) => {
    if (tab !== 'more') {
      const stab = qs.stringify({ stab: tab });

      navigate(
        {
          pathname: to ? compactUrl(to, pageParams) : location.pathname,
          search: `?${stab}`
        },
        {
          state: Object.assign({}, location.state, { keepScroll: true })
        }
      );
    }
  };

  useEffect(() => {
    setQuery('');
  }, [pageParams?.stab, pageParams?.tab]);

  const modifiedElement =
    element?.props?.elements?.length &&
    produce(element, draft => {
      for (let i = 0; i < draft.props.elements.length; i++) {
        // draft.props.elements[i].props.hasSearchBox = true;
        draft.props.elements[i].props.query = query;
      }
    });

  compose(props => {
    props.hasSearchBox = false;
  });

  const isShowMoreButton = React.useMemo(() => {
    return state < displayTab?.length;
  }, [state, displayTab?.length]);

  return (
    <Block>
      <BlockHeader title={title}></BlockHeader>
      <BlockContent>
        <SubTabWrapper ref={totalRef} pageDetail={pageDetail}>
          <HiddenTabs ref={hiddenTabsRef}>
            {displayTab.map((tab, index) => (
              <TabItemHidden
                ref={tabRef}
                aria-label={tab.tab}
                value={tab.tab}
                label={translateTabLabel(tab)}
                key={index.toString()}
              />
            ))}
          </HiddenTabs>
          <TabWrapperStyles
            value={tab}
            onChange={handleChange}
            textColor="primary"
            indicatorColor="primary"
            data-testid="tabWrapper"
          >
            {(isShowMoreButton ? displayTab.slice(0, state) : displayTab).map(
              (tab, index) => (
                <TabItemWrapper
                  data-testid={camelCase(`tabItem ${tab.tab}`)}
                  aria-label={tab.tab}
                  value={tab.tab}
                  label={translateTabLabel(tab, false)}
                  numberTabItem={state}
                  key={index.toString()}
                  widthTabMore={
                    moreTabRef.current?.getBoundingClientRect().width
                  }
                />
              )
            )}
            {isShowMoreButton && (
              <TabMoreItem
                onClick={toggleOpen}
                value={'more'}
                label={moreTab(true)}
                ref={refMenuMore}
              />
            )}
          </TabWrapperStyles>
          <HiddenTabs ref={moreTabRef}>
            <Tab value={'more'} label={moreTab(false)} ref={moreTabRef} />
          </HiddenTabs>

          <HiddenTabs ref={searchHiddenRef}>
            <SearchBox
              placeholder={tabProps?.placeholderSearch}
              onQueryChange={setQuery}
              sx={{
                width: { sm: 'auto', xs: '100%' },
                margin: { sm: 'initial', xs: '16px 0 0 0' },
                padding: '10px'
              }}
            />
          </HiddenTabs>
          {hasSearch ? (
            <SearchBox
              ref={searchRef}
              placeholder={
                tabValue?.placeholderSearch || tabProps?.placeholderSearch
              }
              onQueryChange={setQuery}
              sx={{
                width: { sm: 'auto', xs: '100%' },
                margin: { sm: 'initial', xs: '16px 0 0 0' }
              }}
              keyTab={`${pageParams?.tab}${
                pageParams?.stab || displayTab[0]?.tab
              }`}
            />
          ) : null}
        </SubTabWrapper>
        <DisableGutter disableGutter={disableGutter}>
          {modifiedElement ? jsxBackend.render(modifiedElement) : null}
        </DisableGutter>
      </BlockContent>
    </Block>
  );
}
