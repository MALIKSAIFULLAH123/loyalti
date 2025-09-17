/**
 * @type: ui
 * name: feed.ui.Listing
 * bundle: web
 * chunkName: feed
 */
import React from 'react';
import { createBlock, useGlobal } from '@metafox/framework';

function FeedListing(props) {
  const { jsxBackend, dispatch } = useGlobal();
  const { pagingId, sort } = props || {};

  const ListView = jsxBackend.get('core.block.listview');

  React.useEffect(() => {
    dispatch({ type: 'feed/pagingId', payload: pagingId });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId]);

  return (
    <ListView
      {...props}
      pagingId={pagingId}
      pageParamsDefault={{ ...props?.pageParamsDefault, sort }}
    />
  );
}

export default createBlock({
  extendBlock: FeedListing,
  overrides: {
    blockLayout: 'Sort - Main Listings'
  }
});
