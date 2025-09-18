/**
 * @type: itemView
 * name: group.itemView.myPublishedPost
 * chunkName: group
 */

import { useGlobal } from '@metafox/framework';
import { useGetItem } from '@metafox/framework/hooks';
import { GroupItemProps } from '@metafox/group';
import { ItemView } from '@metafox/ui';
import { Button, CardActions, CardContent, styled } from '@mui/material';
import * as React from 'react';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: 0
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
  const { jsxBackend, navigate, i18n } = useGlobal();
  const item = useGetItem(identity);
  const [visible, setVisible] = React.useState<boolean>(true);

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const handleViewPost = () => {
    navigate(item.link);
  };

  if (!visible) return null;

  return (
    <StyledItemView
      testid="publishedPosts"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard>
        <FeedContent
          identity={identity}
          setVisible={setVisible}
          isItemAction={false}
        />
      </PostsCard>
      <CardActions sx={{ pt: 0, pb: 2 }}>
        <Button
          sx={{ width: '100%' }}
          variant="outlined"
          onClick={handleViewPost}
        >
          {i18n.formatMessage({ id: 'view_post' })}
        </Button>
      </CardActions>
    </StyledItemView>
  );
};

export default PublishedPosts;
