import { Box, ImageList, ImageListItem } from '@mui/material';
import React from 'react';
import Image from './Image';

const demoImage =
  'https://qctest.phpfox.us/CDN/file/pic/photo/2020/03/8f69134559b5c9763b2fb39f42e925e4_1024.jpg';

const demoData = [
  {
    img: demoImage,
    title: 'Image',
    author: 'author',
    cols: 1
  },
  {
    img: demoImage,
    title: 'Image2',
    author: 'author2',
    cols: 1
  }
];

export default function ImageGridList({ cols = 3, values = demoData }) {
  return (
    <Box
      sx={{
        display: 'flex',
        flexWrap: 'wrap',
        justifyContent: 'space-around',
        overflow: 'hidden'
      }}
    >
      <ImageList sx={{ width: '100%' }} cols={cols}>
        {values.map(tile => (
          <ImageListItem key={tile.img} cols={tile.cols || 1}>
            <Image src={tile.img} alt={tile.title} />
          </ImageListItem>
        ))}
      </ImageList>
    </Box>
  );
}
