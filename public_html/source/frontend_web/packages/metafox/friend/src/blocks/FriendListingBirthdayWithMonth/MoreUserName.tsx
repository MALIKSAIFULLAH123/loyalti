import { Link, useGlobal } from '@metafox/framework';
import { Box } from '@mui/material';
import React from 'react';

const MoreUserName = ({ data }) => {
  const { i18n } = useGlobal();

  if (!data?.length) return;

  return (
    <Box fontSize={theme => theme.mixins.pxToRem(14)} my={0.5}>
      <UserNameStyled item={data[0]} />
      {data.length === 2 && (
        <>
          {` ${i18n.formatMessage({ id: '_and_' })} `}
          <UserNameStyled item={data[1]} />
        </>
      )}
      {data.length === 3 && (
        <>
          {', '}
          <UserNameStyled item={data[1]} />
          {` ${i18n.formatMessage({ id: '_and_' })} `}
          <UserNameStyled item={data[2]} />
        </>
      )}

      {data.length > 3 && (
        <>
          {', '}
          <UserNameStyled item={data[1]} />{' '}
          {i18n.formatMessage(
            { id: 'and_value_others' },
            { value: data.length - 2 }
          )}
        </>
      )}
    </Box>
  );
};

const UserNameStyled = ({ item }) => {
  return (
    <Link hoverCard={false} to={item?.link} sx={{ fontWeight: 'bold' }}>
      {item?.full_name}
    </Link>
  );
};

export default MoreUserName;
