import { Typography, styled } from '@mui/material';

const BlockDesc = styled(Typography, {
  name: 'LayoutBlock',
  slot: 'DescriptionRoot',
  overridesResolver(props, styles) {
    return [styles.descriptionRoot];
  }
})({});

export default BlockDesc;
