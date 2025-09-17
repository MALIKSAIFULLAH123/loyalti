import {
  GlobalState,
  RefOf,
  SuggestionListHandle,
  useGlobal
} from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, IconButton } from '@mui/material';
import clsx from 'clsx';
import { isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';

interface Props {
  limit?: number;
  loaded?: boolean;
  data?: string[];
  classes: any;
  onSearch: (query: string) => void;
}

function RecentSearchList(
  { onSearch, limit = 5, classes }: Props,
  ref: RefOf<SuggestionListHandle>
) {
  const { dispatch, i18n } = useGlobal();
  const [selectedIndex, setSelectedIndex] = React.useState<number>(-1);
  const refs = [];

  const recent = useSelector((state: GlobalState) => state.search.recentSearch);

  const { data, loaded } = recent;

  React.useImperativeHandle(ref, () => {
    return {
      moveNext: () => {
        if (!data?.length) return;

        const size = Math.min(data.length, limit);

        if (!size) return;

        setSelectedIndex(prev => {
          return (prev + 1 + size) % size;
        });
      },
      movePrev: () => {
        if (!data?.length) return;

        const size = Math.min(data.length, limit);

        if (!size) return;

        setSelectedIndex(prev => {
          return (Math.max(0, prev) - 1 + size) % size;
        });
      },
      selected: () => {
        return selectedIndex > -1 ? data[selectedIndex] : undefined;
      }
    };
  });

  React.useEffect(() => {
    setSelectedIndex(-1);
  }, [data]);

  React.useEffect(() => {
    if (!loaded) {
      dispatch({ type: 'recentSearch/INIT', payload: {} });
    }
  }, [dispatch, loaded]);

  const removeItem = React.useCallback(
    (evt: React.MouseEvent, text: string) => {
      if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
      }

      dispatch({ type: 'recentSearch/REMOVE', payload: { text } });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );
  const clearItems = React.useCallback(() => {
    dispatch({ type: 'recentSearch/CLEAR', payload: {} });
  }, [dispatch]);

  if (isEmpty(data)) {
    return (
      <div className={classes.menuList} data-testid="noRecentSearches">
        <span className={classes.noOptions}>
          {i18n.formatMessage({ id: 'no_recent_searches' })}
        </span>
      </div>
    );
  }

  if (!loaded) return null;

  return (
    <>
      <div className={clsx(classes.headerPopup, classes.headerRecent)}>
        <span>{i18n.formatMessage({ id: 'recents' })}</span>
        <Button
          onClick={clearItems}
          className={classes.clearButton}
          tabIndex={-1}
          size="medium"
        >
          {i18n.formatMessage({ id: 'clear' })}
        </Button>
      </div>
      <div className={classes.menuList}>
        {data.slice(0, 5).map((item, index) => (
          <div
            role="option"
            tabIndex={-1}
            ref={node => {
              refs[index] = node;
            }}
            aria-selected={selectedIndex === index}
            key={item.toString()}
            onClick={() => onSearch(item)}
            onMouseOver={() => setSelectedIndex(index)}
            onMouseLeave={() => setSelectedIndex(-1)}
            className={clsx(
              classes.recentItem,
              selectedIndex === index && classes.focusedItem
            )}
          >
            <span className={classes.recentLabel}>{item}</span>
            <IconButton
              tabIndex={-1}
              size="smaller"
              onClick={e => removeItem(e, item)}
              sx={{ fontSize: 16 }}
            >
              <LineIcon color="textSecondary" icon="ico-close" />
            </IconButton>
          </div>
        ))}
      </div>
    </>
  );
}

export default React.forwardRef<SuggestionListHandle, Props>(RecentSearchList);
