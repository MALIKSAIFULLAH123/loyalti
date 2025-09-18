import { Grid, Typography, Box } from '@mui/material';
import React from 'react';
import Item from './BgStatusItem';
import { BgStatusCollectionShape } from '@metafox/bgstatus/types';

export type BgStatusCollectionProps = {
  data: BgStatusCollectionShape;
  onSelectItem: (item: unknown) => void;
  selectedId?: number;
};

const MAXIMUM_NUMBER_MORE = 10;

export default function BgStatusCollection(props: BgStatusCollectionProps) {
  const { onSelectItem, selectedId, data } = props;
  const ids = data.backgrounds.map(x => x?.id);
  const [isFull, setIsFull] = React.useState(
    selectedId && ids.includes(selectedId)
  );

  if (!data?.backgrounds?.length) return null;

  return (
    <Box sx={{ '&:not(:last-child)': { mb: 3 } }}>
      <Typography variant={'h4'} mb={2}>
        {data?.name}
      </Typography>
      <Grid container spacing={1}>
        {data.backgrounds.map((item, index) => (
          <Item
            key={item.id.toString()}
            item={item}
            onClick={() => onSelectItem(item)}
            isHide={!isFull && index >= MAXIMUM_NUMBER_MORE}
            selected={selectedId === item.id}
            onClickLoadMore={
              !isFull &&
              index + 1 === MAXIMUM_NUMBER_MORE &&
              data.backgrounds.length > MAXIMUM_NUMBER_MORE
                ? () => setIsFull(true)
                : undefined
            }
            labelLoadmore={`+${data.backgrounds.length - MAXIMUM_NUMBER_MORE}`}
          />
        ))}
      </Grid>
    </Box>
  );
}
