import { LIMIT_ONLINE } from '@metafox/chatplus/constants';
import { UserShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { Box, Button, styled } from '@mui/material';
import React from 'react';
import OnlineItem from './OnlineItem';

const Root = styled('div')(({ theme }) => ({}));

const NoResult = styled('div')(({ theme }) => ({
  padding: theme.spacing(2, 0),
  fontSize: theme.spacing(1.875),
  lineHeight: theme.spacing(2.5),
  color: theme.palette.grey['600'],
  textAlign: 'center'
}));

const InviteButton = styled('div')(({ theme }) => ({
  padding: theme.spacing(0, 1, 1, 1),
  '& button': {
    borderColor: theme.palette.primary.main
  }
}));

interface Props {
  searchValue?: string;
  data: UserShape[];
}

function OnlineList({ data, searchValue }: Props) {
  const { i18n, navigate } = useGlobal();

  const handleAddFriends = () => {
    navigate('/user');
  };

  if (searchValue && !data.length) {
    return (
      <NoResult>{i18n.formatMessage({ id: 'no_results_found' })}</NoResult>
    );
  }

  if (!data.length) {
    return (
      <Box>
        <NoResult>{i18n.formatMessage({ id: 'no_online_friends' })}</NoResult>
        <InviteButton>
          <Button
            variant="outlined"
            color="primary"
            fullWidth
            onClick={handleAddFriends}
          >
            {i18n.formatMessage({ id: 'add_friends' })}
          </Button>
        </InviteButton>
      </Box>
    );
  }

  return (
    <Root>
      {data.slice(0, LIMIT_ONLINE).map(item => (
        <OnlineItem item={item} key={item._id} />
      ))}
    </Root>
  );
}

export default OnlineList;
