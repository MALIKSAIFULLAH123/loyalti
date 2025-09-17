import Base from './Base';
import ListViewPagination from './ListViewPagination';
import React from 'react';

const ListViewContainer = props => {
  const { isLoadMorePagination } = props;

  if (isLoadMorePagination) {
    return <ListViewPagination {...props} />;
  }

  return <Base {...props} />;
};
export default ListViewContainer;
