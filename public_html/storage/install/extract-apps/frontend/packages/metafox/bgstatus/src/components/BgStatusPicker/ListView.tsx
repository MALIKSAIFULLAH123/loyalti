import { GlobalState, useGlobal, useScrollEnd } from '@metafox/framework';
import Collection from './BgStatusCollection';
import { Box } from '@mui/material';
import { isArray } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import BgStatusSkeleton from './BgStatusSkeleton';
import { getBgStatusSelector } from '@metafox/bgstatus/selectors';
import { AppState } from '@metafox/bgstatus';

function ListView({
  emptyPage = 'core.block.no_results',
  handleSelect,
  selectedId
}) {
  const { jsxBackend, dispatch } = useGlobal();

  const { collections, ended, loading } = useSelector<
    GlobalState,
    AppState['collections']
  >(getBgStatusSelector);

  const triggerLoadmore = () => {
    dispatch({ type: 'bgstatus/LOAD', payload: {} });
  };

  useScrollEnd(() => {
    if (!ended && !loading) {
      triggerLoadmore();
    }
  });
  React.useEffect(() => {
    if (!ended && !loading) {
      triggerLoadmore();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const showLoadSmooth = !ended;

  if (!collections.length && ended) {
    return jsxBackend.render({ component: emptyPage });
  }

  return (
    <Box>
      {isArray(collections) &&
        collections.map(collection => (
          <Collection
            key={collection?.id.toString()}
            data={collection}
            onSelectItem={handleSelect}
            selectedId={selectedId}
          />
        ))}
      {showLoadSmooth ? <BgStatusSkeleton /> : null}
    </Box>
  );
}

export default ListView;
