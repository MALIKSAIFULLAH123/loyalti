import { Link } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import * as React from 'react';

const name = 'MarketplaceDetail';

type Props = {
  tags: string[];
  module_name: string;
};

const TagItem = styled('div', { 
  name, 
  slot: 'tagItem',
  overridesResolver(props, styles) {
    return [styles.tagItem];
   }
 })(({ theme }) => ({
  '& a': {
    fontSize: theme.mixins.pxToRem(13),
    fontWeight: theme.typography.fontWeightBold,
    borderRadius: 4,
    background:
      theme.palette.mode === 'light'
        ? theme.palette.background.default
        : theme.palette.action.hover,
    marginRight: theme.spacing(1),
    marginBottom: theme.spacing(1),
    padding: theme.spacing(0, 1.5),
    height: theme.spacing(3),
    lineHeight: theme.spacing(3),
    display: 'block'
  }
}));

export default function Tags(props: Props) {
  const { tags, module_name } = props;

  if (!tags?.length) return null;

  return (
    <Box display="flex" flexWrap="wrap">
      {tags.map(tag => (
        <TagItem key={tag}>
          <Link
            to={`/${module_name}/search?q=%23${encodeURIComponent(tag)}`}
            children={tag}
          />
        </TagItem>
      ))}
    </Box>
  );
}
