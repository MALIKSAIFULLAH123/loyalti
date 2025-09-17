import { useGlobal } from '@metafox/framework';
import { Tooltip, Box } from '@mui/material';
import { isEmpty, isString } from 'lodash';
import React from 'react';

const Item = ({ item }) => {
  const { useGetItem } = useGlobal();

  if (isString(item)) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    item = useGetItem(item);
  }

  return <div>{item?.name}</div>;
};

const Items = ({ data }) => {
  return (
    <Box>
      {data.map((item: any) => (
        <Item key={item.id} item={item} />
      ))}
    </Box>
  );
};

function MoreTooltip({ data }: any) {
  const { i18n } = useGlobal();

  return (
    <Tooltip
      disableInteractive
      placement="top"
      title={<Items data={data.slice(1)} />}
    >
      <span>
        {i18n.formatMessage(
          { id: 'and_total_more' },
          { value: data.length - 1 }
        )}
      </span>
    </Tooltip>
  );
}

export default function MoreOthers({ data }: any) {
  const { useGetItem } = useGlobal();

  if (isEmpty(data)) return '';

  if (data.length === 1) {
    let item = data[0];

    if (isString(item)) {
      // eslint-disable-next-line react-hooks/rules-of-hooks
      item = useGetItem(data[0]);
    }

    return item.name;
  }

  let firstItem: any = data[0];

  if (isString(firstItem)) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    firstItem = useGetItem(firstItem);
  }

  return (
    <Box>
      {firstItem.name} <MoreTooltip data={data} />
    </Box>
  );
}
