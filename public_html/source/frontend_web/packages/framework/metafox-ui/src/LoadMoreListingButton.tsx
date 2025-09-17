import { useGlobal } from '@metafox/framework';
import React from 'react';
import { styled, Link } from '@mui/material';

export interface LoadMoreListingButtonProps {
  'data-testid'?: string;
  handleClick: () => void;
  message?: string;
  loadMoreTypeProp: any;
}
const Wrapper = styled(Link, {
  name: 'PaginationWrapper'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  padding: theme.spacing(2),
  cursor: 'pointer'
}));

export default function LoadMoreButton({
  handleClick,
  message = 'load_more',
  'data-testid': testid = 'pagination',
  loadMoreTypeProp
}: LoadMoreListingButtonProps) {
  const { i18n } = useGlobal();
  const { contentStyle } = loadMoreTypeProp || {};

  return (
    <Wrapper
      data-testid="loadMoreButton"
      color="primary"
      variant="body2"
      onClick={handleClick}
      sx={{ ...contentStyle }}
    >
      {i18n.formatMessage({
        id: message
      })}
    </Wrapper>
  );
}
