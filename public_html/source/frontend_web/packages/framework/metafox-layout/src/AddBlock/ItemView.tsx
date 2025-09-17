import { useGlobal } from '@metafox/framework';
import { Box, Button, Divider, Skeleton, Typography } from '@mui/material';
import React from 'react';

export type BlockItemShape = {
  name: string;
  title: string;
  keywords: string;
  description: string;
  previewImage: string;
};

export interface BlockItemOptionProps {
  item: BlockItemShape;
  onItemSelected(blockName: string): void;
  onKeyWorkClicked(keyword: string): void;
}

export default function ItemView({
  item: { title, keywords, description, previewImage, name },
  onItemSelected,
  onKeyWorkClicked
}: BlockItemOptionProps) {
  const { i18n } = useGlobal();

  return (
    <>
      <Box component="div" flexDirection="row" display="flex">
        <Box component="div" mr="16px">
          <Skeleton
            component="div"
            variant="rectangular"
            width="120px"
            height="80px"
            animation={false}
          />
        </Box>
        <Box sx={{ flex: 1 }}>
          <Typography variant="h4">{title ?? name}</Typography>
          <Typography variant="body1">{keywords}</Typography>
          <Typography variant="body1">{description}</Typography>
        </Box>
        <Box sx={{ mt: 1.5 }}>
          <Button onClick={() => onItemSelected(name)}>
            {i18n.formatMessage({ id: 'create_layout_block' })}
          </Button>
        </Box>
      </Box>
      <Divider style={{ margin: '12px 0' }} />
    </>
  );
}
