import {
  SuggestionListHandle,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { ClickOutsideListener, LineIcon } from '@metafox/ui';
import { Box, BoxProps, InputBase } from '@mui/material';
import { styled } from '@mui/material/styles';
import { trim, isString } from 'lodash';
import * as React from 'react';
import useStyles from './GlobalSearchForm.style';
import RecentSearchList from './RecentSearchList';
import SuggestionList from './SuggestionList';
import qs from 'querystring';
import { whenParamRules } from '@metafox/utils';

const APP_SEARCH = 'search';

type Placement = 'left' | 'center';

interface Props {
  openSearch?: any;
  minimize?: boolean;
  closeSearch?: () => void;
  placement?: Placement;
}

interface State {
  open: boolean;
  focus: boolean;
  text: string;
  menuOpened?: boolean;
  minimize: boolean;
}

const name = 'MfHeaderSearchForm';

type RootProps = {
  open?: boolean;
  placement?: Placement;
};

const Root = styled(Box, {
  name,
  slot: 'Root',
  overridesResolver: ({ placement }, styles) => {
    return [
      styles.root,
      placement === 'left' && styles.rootPlacementLeft,
      placement === 'float-left' && styles.rootPlacementFloatLeft,
      placement === 'center' && styles.rootPlacementCenter,
      placement === 'right' && styles.rootPlacementRight
    ];
  }
})<RootProps & BoxProps>(({ theme, open, placement }) => ({
  width: 320,
  position: 'absolute',
  top: 0,
  paddingTop: 13,
  zIndex: theme.zIndex.appBar,
  transform: 'translateX(-50%)',
  left: '50%',
  ...(open && {
    background: theme.palette.background.paper,
    borderBottomLeftRadius: theme.shape.borderRadius,
    borderBottomRightRadius: theme.shape.borderRadius,
    boxShadow: theme.shadows[8],
    '& $searchIcon': {
      color: theme.palette.primary.main
    },
    '& $resultWrapper': {
      display: 'block'
    }
  })
}));

const Form = styled('form', {
  name,
  slot: 'Form',
  skipSx: true,
  skipVariantsResolver: true,
  shouldForwardProp: props => props !== 'focused',
  overridesResolver: (props, styles) => [styles.form]
})<{ focused?: boolean }>(({ theme, focused }) => ({
  width: 284,
  position: 'relative',
  background: theme.palette.action.hover,
  borderRadius: '999px',
  height: theme.spacing(4),
  margin: theme.spacing(0, 2, 0, 2),
  '& .MuiOutlinedInput-notchedOutline': {
    border: 'none'
  },
  '& .MuiInputBase-input': {
    height: theme.spacing(4),
    boxSizing: 'border-box'
  },
  ...(focused && {
    border: theme.mixins.border('primary'),
    borderColor: theme.palette.primary.main,
    background: 'none'
  })
}));

const Input = styled(InputBase, {
  name,
  slot: 'Input'
})(({ theme }) => ({
  color: `${theme.palette.text.secondary} !important`,
  width: '100%',
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const Results = styled(Box, {
  name,
  slot: 'ResultWrapper'
})<{ open?: boolean }>(({ open }) => ({
  display: open ? 'block' : 'none'
}));

const IconSearch = styled(LineIcon, {
  name,
  slot: 'SearchIcon',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ active, theme }) => ({
  color: theme.palette.text.hint,
  ...(active && { color: theme.palette.primary.main })
}));

export default function AppBarSearch({
  openSearch,
  minimize: isMinimized,
  closeSearch,
  placement,
  menuRef
}: Props) {
  const classes = useStyles();

  const {
    dispatch,
    usePageParams,
    i18n,
    eventCenter,
    navigate,
    location,
    getSetting
  } = useGlobal();

  const searchHashTag = useResourceAction(
    APP_SEARCH,
    APP_SEARCH,
    'searchHashtagItem'
  );

  const dataSource = useResourceAction(APP_SEARCH, APP_SEARCH, 'viewAll');

  const { pathname } = location;

  const { q } = usePageParams();

  const text = React.useMemo(() => {
    if (/^\/hashtag\/search/.test(pathname)) {
      return q ? `#${q}` : '';
    }

    return /^\/search/.test(pathname) ? q : '';
  }, [pathname, q]);

  const [state, setState] = React.useState<State>({
    open: false,
    text,
    focus: false,
    minimize: !!isMinimized
  });
  const appSearchSetting = getSetting('search');

  const placeholder = i18n.formatMessage({ id: 'search' });
  const listRef = React.useRef<SuggestionListHandle>();
  const inputRef = React.useRef<HTMLInputElement>();
  const containerRef = React.useRef<HTMLDivElement>();

  React.useEffect(() => {
    const token = eventCenter.on('minimizeGlobalSearchForm', () => {
      setState(prev => ({ ...prev, minimize: true }));
    });

    return () => {
      eventCenter.off('minimizeGlobalSearchForm', token);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (openSearch === true) {
      onFocus();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [openSearch]);

  const handleSearch = React.useCallback(
    (query: string) => {
      setState(prev => ({ ...prev, text: query }));

      if (query) {
        dispatch({
          type: 'recentSearch/ADD',
          payload: { text: trim(query) }
        });
      }

      if (query.startsWith('#') && query.substring(1)) {
        const paramValues = {
          q: query.substring(1),
          ...searchHashTag?.pageParams
        };

        const apiRules = dataSource?.apiRules;

        const params = whenParamRules(paramValues, apiRules);

        navigate(
          {
            pathname: '/hashtag/search',
            search: qs.stringify(params)
          },
          { replace: true }
        );
      } else {
        navigate(`/search/?q=${query}`);
      }

      if (inputRef?.current) {
        inputRef.current.blur();
      }

      onBlur();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const onKeyboardEscape = React.useCallback(() => {
    setState(prev => ({ ...prev, focus: false, open: false }));
  }, []);

  const onKeyboardArrowUp = React.useCallback(() => {
    if (listRef.current?.movePrev) {
      listRef.current.movePrev();
    }
  }, []);

  const onKeyboardArrowDown = React.useCallback(() => {
    if (listRef.current?.moveNext) {
      listRef.current.moveNext();
    }
  }, []);

  const onBlur = React.useCallback(() => {
    setState(prev => ({ ...prev, focus: false, open: false }));

    if (closeSearch) closeSearch();
  }, [closeSearch]);

  const onSubmit = React.useCallback(
    (evt?: React.FormEvent) => {
      const x: string = inputRef.current?.value;

      if (x === '#') return;

      if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
      }

      const trimmedQuery = trim(x);

      if (!trimmedQuery) {
        onBlur();
      }

      if (trimmedQuery) {
        dispatch({
          type: 'recentSearch/ADD',
          payload: { text: trimmedQuery }
        });
        handleSearch(trimmedQuery);
      }

      if (closeSearch) closeSearch();
      // eslint-disable-next-line
    },
    [closeSearch]
  );

  const onQueryChanged = React.useCallback(
    (evt: React.ChangeEvent<{ value: string }>) => {
      const text = evt.currentTarget.value;
      setState(prev => ({ ...prev, text, selectedIndex: 0 }));
      dispatch({ type: 'suggestions/QUERY', payload: { text } });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const onFocus = React.useCallback(() => {
    if (!state.open) {
      setState(prev => ({ ...prev, focus: true, open: true }));

      if (inputRef.current) {
        inputRef.current?.focus();
      }
    }

    dispatch({ type: 'suggestions/QUERY', payload: { text: state.text } });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state.open, state.text]);

  const handleClickAway = React.useCallback(() => {
    if (closeSearch) closeSearch();

    setState(prev => ({ ...prev, open: false, focus: false }));
  }, [closeSearch]);

  const onKeyboardEnter = React.useCallback(() => {
    const itemSelect = listRef.current?.selected();

    if (itemSelect) {
      inputRef.current?.blur();

      if (isString(itemSelect)) {
        handleSearch(itemSelect);
      } else {
        navigate(itemSelect?.to);
      }

      return;
    }

    onSubmit();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state.text]);

  const cancelEvent = React.useCallback((evt: React.KeyboardEvent) => {
    evt.preventDefault();
    evt.stopPropagation();
  }, []);

  const onKeyDown = React.useCallback(
    (evt: React.KeyboardEvent) => {
      const code = evt?.keyCode;

      if (evt.metaKey || evt.ctrlKey) return;

      switch (code) {
        case 9:
          onKeyboardEscape();
          break;
        case 27: // escape
          onKeyboardEscape();
          cancelEvent(evt);
          break;
        case 13: // enter
          onKeyboardEnter();
          cancelEvent(evt);
          break;
        case 38: // arrow up
          onKeyboardArrowUp();
          cancelEvent(evt);
          break;
        case 40: // arrow down
          onKeyboardArrowDown();
          cancelEvent(evt);
          break;
        default:
          if (!state.open) {
            onFocus();
          }
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [state.open]
  );

  React.useEffect(() => {
    const isSearchPath = ['/search', '/hashtag/search'].some(path =>
      pathname.startsWith(path)
    );

    setState(prev => ({ ...prev, text: isSearchPath ? text : '' }));
  }, [pathname, text]);

  if (!appSearchSetting) return null;

  return (
    <ClickOutsideListener onClickAway={handleClickAway} excludeRef={menuRef}>
      <Root
        ref={containerRef}
        data-testid="formSearch"
        open={state.open}
        placement={placement ?? 'center'}
        role="search"
        id="globalSearchBox"
      >
        <Form
          method="get"
          focused={!!state.open}
          aria-expanded={!state.minimize}
          onSubmit={onSubmit}
        >
          <Input
            startAdornment={
              <IconSearch active={!!state.open} icon={'ico-search-o'} />
            }
            placeholder={placeholder}
            classes={{
              root: classes.inputRoot,
              input: classes.inputInput
            }}
            autoComplete="off"
            value={state.text}
            name="globalSearch"
            inputProps={{
              'aria-label': 'search',
              'data-testid': 'searchBox',
              autoComplete: 'off',
              autoCapitalize: 'off'
            }}
            inputRef={inputRef}
            onFocus={onFocus}
            onChange={onQueryChanged}
            onKeyDown={onKeyDown}
          />
        </Form>
        {state.open ? (
          <Results open={state.open}>
            {state.text ? (
              <SuggestionList
                ref={listRef}
                onSearch={handleSearch}
                classes={classes}
                text={state.text}
                isActionSearch={inputRef.current?.value !== '#'}
                blurSearch={handleClickAway}
              />
            ) : (
              <RecentSearchList
                onSearch={handleSearch}
                ref={listRef}
                classes={classes}
              />
            )}
          </Results>
        ) : null}
      </Root>
    </ClickOutsideListener>
  );
}
