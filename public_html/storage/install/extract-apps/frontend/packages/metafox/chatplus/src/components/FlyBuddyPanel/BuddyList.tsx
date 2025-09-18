import { RoomItemShape } from '@metafox/chatplus';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import BuddyItem from './BuddyItem';

const Root = styled('div')(({ theme }) => ({}));
const NoResult = styled('div')(({ theme }) => ({
  padding: theme.spacing(3, 0),
  fontSize: theme.spacing(1.75),
  color: theme.palette.grey['600'],
  textAlign: 'center'
}));
interface Props {
  searchValue?: string;
  data: RoomItemShape[];
}

const LIMIT = 50;

function BuddyList({ data, searchValue }: Props) {
  const { i18n } = useGlobal();

  if (searchValue && !data.length) {
    return <NoResult>{i18n.formatMessage({ id: 'no_messages' })}</NoResult>;
  }

  if (!data.length) {
    return <NoResult>{i18n.formatMessage({ id: 'no_messages' })}</NoResult>;
  }

  return (
    <Root>
      {data.slice(0, LIMIT).map(item => (
        <BuddyItem item={item} key={item.id.toString()} />
      ))}
    </Root>
  );
}

export default BuddyList;
