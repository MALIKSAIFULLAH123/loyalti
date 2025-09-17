import {
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  Statistic,
  UserAvatar
} from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { Box, styled } from '@mui/material';
import React from 'react';

const ItemWrapper = styled('div', { name: 'ItemWrapper' })(({ theme }) => ({
  marginLeft: theme.spacing(1),
  flex: 1,
  minWidth: 0
}));

const Option = ({ props, value }: { props; value: UserItemShape }) => {
  if (!value) return null;

  const { statistic, full_name, title } = value;

  return (
    <Box component="div" {...props}>
      <ItemMedia>
        <UserAvatar user={value} size={40} />
      </ItemMedia>
      <ItemWrapper>
        <ItemText>
          <ItemTitle>{full_name || title}</ItemTitle>
          <ItemSummary>
            <Statistic values={statistic} display={'total_mutual'} />
          </ItemSummary>
        </ItemText>
      </ItemWrapper>
    </Box>
  );
};

export default Option;
