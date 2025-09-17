import { Divider, styled } from '@mui/material';

export const TopDivider = styled(Divider, {
  name: 'FeedItem',
  slot: 'TopDivider',
  overridesResolver(props, styles) {
    return [styles.topDivider];
  }
})({});

export const BottomDivider = styled(Divider, {
  name: 'FeedItem',
  slot: 'BottomDivider',
  overridesResolver(props, styles) {
    return [styles.bottomDivider];
  }
})({});

export const CommentDivider = styled(Divider, {
  name: 'FeedItem',
  slot: 'CommentDivider',
  overridesResolver(props, styles) {
    return [styles.commentDivider];
  }
})(({ theme }) => ({
  marginTop: theme.spacing(0.5)
}));
