import { usePageParams } from '@metafox/layout';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import Item from './Item';
import Sections from './Sections';

const name = 'GlobalSearchResult';
const SearchWrapper = styled(Box, {
  name,
  slot: 'wrapper',
  overridesResolver(props, styles) {
    return [styles.wrapper];
  }
})(({ theme }) => ({
  '& .error404Block': {
    '& img': {
      display: 'none'
    }
  },
  [theme.breakpoints.up('sm')]: {
    display: 'flex',
    justifyContent: 'center',
    '& .error404Block': {
      '& h2': {
        fontSize: theme.typography.h3.fontSize
      }
    }
  }
}));

export default function SearchListing(props) {
  const pageParams = usePageParams();
  const { view } = pageParams;

  React.useEffect(() => {
    window.scrollTo(0, 0);
  }, [view]);

  return (
    <SearchWrapper>
      {view === 'all' ? <Sections {...props} /> : <Item {...props} />}
    </SearchWrapper>
  );
}
