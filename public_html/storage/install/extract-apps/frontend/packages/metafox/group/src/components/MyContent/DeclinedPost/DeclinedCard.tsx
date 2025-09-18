/**
 * @type: itemView
 * name: group.itemView.myDeclinedPost
 * chunkName: group
 */

import { connectItemView, useGlobal, withItemView } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group';
import { ItemView } from '@metafox/ui';
import { Button, CardActions, CardContent, styled } from '@mui/material';
import * as React from 'react';
import { LoadingSkeleton } from '../LoadingSkeleton';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: 0
}));

const StyledButtonDelete = styled(Button)(({ theme }) => ({
  color:
    theme.palette.mode === 'light'
      ? theme.palette.error.light
      : theme.palette.error.main,
  borderColor:
    theme.palette.mode === 'light'
      ? `${theme.palette.error.light} !important`
      : `${theme.palette.error.main} !important`
}));

const PostsCard = styled(CardContent, {
  name: 'LayoutItemCard',
  slot: 'postsCard',
  overridesResolver(props, styles) {
    return [styles.postsCard];
  }
})(({ theme }) => ({
  paddingTop: theme.spacing(2)
}));

const PublishedPosts = ({
  identity,
  wrapAs: WrapAs,
  wrapProps,
  actions
}: GroupItemProps) => {
  const { jsxBackend, i18n, dispatch } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(
    FeedContent as React.FC<any>
  );

  const Content = connectItemView(ContentWrapper, () => {});

  const handleEdit = () => {
    dispatch({
      type: 'editFeedItem',
      payload: { identity }
    });
  };

  const handleDelete = () => {
    dispatch({ type: 'deleteItem', payload: { identity } });
  };

  return (
    <StyledItemView
      testid="publishedPosts"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard>
        <Content identity={identity} isItemAction={false} />
      </PostsCard>
      <CardActions sx={{ p: 0, pl: 2, pb: 2 }}>
        <Button size="small" variant="contained" onClick={handleEdit}>
          {i18n.formatMessage({ id: 'edit' })}
        </Button>
        <StyledButtonDelete
          size="small"
          color="error"
          variant="outlined"
          onClick={handleDelete}
        >
          {i18n.formatMessage({ id: 'delete' })}
        </StyledButtonDelete>
      </CardActions>
    </StyledItemView>
  );
};

PublishedPosts.LoadingSkeleton = LoadingSkeleton;

export default PublishedPosts;
