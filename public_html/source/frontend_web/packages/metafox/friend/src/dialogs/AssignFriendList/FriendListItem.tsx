import { Checkbox, Skeleton, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const FriendListItemRoot = styled('div', {
  name: 'FriendListItem',
  slot: 'Root'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1.5),
  borderBottom: '1px solid',
  borderBottomColor: theme.palette.divider,
  ':hover': {
    backgroundColor: theme.palette.action.hover
  }
}));

export default function FriendListItem({ item, checked, onChange }) {
  const handleChange = React.useCallback(
    (evt: any, checked) => {
      onChange(checked, item.id);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  return (
    <FriendListItemRoot>
      <Typography variant="h5" sx={{ flex: 1 }}>
        {item.name}
      </Typography>
      <Checkbox
        size="small"
        color="primary"
        checked={Boolean(checked)}
        onChange={handleChange}
      />
    </FriendListItemRoot>
  );
}

const LoadingSkeleton = () => {
  return (
    <FriendListItemRoot>
      <Typography variant="h5" sx={{ flex: 1 }}>
        <Skeleton variant="text" width={'50%'} height={24} />
      </Typography>
      <Skeleton variant="rectangular" width={16} height={16} />
    </FriendListItemRoot>
  );
};

FriendListItem.LoadingSkeleton = LoadingSkeleton;
