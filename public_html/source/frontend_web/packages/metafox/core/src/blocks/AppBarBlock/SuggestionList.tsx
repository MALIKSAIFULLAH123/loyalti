import {
  GlobalState,
  RefOf,
  RouteLink,
  SuggestionListHandle,
  useGlobal
} from '@metafox/framework';
import { SuggestionShape } from '@metafox/search';
import { colorHash, getImageSrc, shortenFullName } from '@metafox/utils';
import { Avatar, Box, CircularProgress, Typography } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import { useSelector } from 'react-redux';

const getSuggestions = (state: GlobalState) => state.search.suggestions;

export type SuggestionListProps = {
  limit?: number;
  text: string;
  onSearch: (query: string) => void;
  classes: any;
  isActionSearch?: boolean;
  blurSearch?: () => void;
} & Partial<SuggestionShape>;

function SuggestionList(
  {
    limit = 6,
    text,
    classes,
    onSearch,
    isActionSearch,
    blurSearch
  }: SuggestionListProps,
  ref: RefOf<SuggestionListHandle>
) {
  const { i18n } = useGlobal();
  const [selectedIndex, setSelectedIndex] = React.useState<number>(-1);

  const state = useSelector(getSuggestions);

  const { data, loaded } = Object.assign({}, state[text]);

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

  const handleSearch = () => {
    typeof onSearch === 'function' && onSearch(text);
  };

  return (
    <>
      {!!text && (
        <Typography
          paragraph
          padding="16px"
          margin="0"
          sx={{ cursor: 'pointer' }}
        >
          {isActionSearch && (
            <span onClick={handleSearch}>
              {`${i18n.formatMessage({
                id: 'search'
              })} “${text}”`}
            </span>
          )}
        </Typography>
      )}
      <div className={classes.menuList} data-testid="suggestions">
        {loaded && data.length
          ? data.slice(0, limit).map((item, index) => (
              <RouteLink
                role="option"
                data-testid="suggestion"
                key={item.to}
                to={item.to}
                onClick={blurSearch}
                onMouseOver={() => setSelectedIndex(index)}
                onMouseLeave={() => setSelectedIndex(-1)}
                className={clsx(
                  classes.searchItem,
                  selectedIndex === index && classes.focusedItem
                )}
              >
                <Avatar
                  className={classes.searchAvatar}
                  src={getImageSrc(item?.image, '200')}
                  children={shortenFullName(item.title)}
                  style={{
                    backgroundColor: colorHash.hex(
                      shortenFullName(item.title) || ''
                    )
                  }}
                />
                <div className={classes.searchContent}>
                  <Typography variant="h5" className={classes.searchTitle}>
                    {item.title}
                  </Typography>
                  <div className={classes.searchNote}>{item.note}</div>
                </div>
              </RouteLink>
            ))
          : null}
        {!loaded ? (
          <Box
            sx={{
              display: 'flex',
              justifyContent: 'center',
              pb: 2,
              color: 'text.disabled'
            }}
          >
            <CircularProgress variant="indeterminate" color="inherit" />
          </Box>
        ) : null}
      </div>
    </>
  );
}

export default React.forwardRef(SuggestionList);
