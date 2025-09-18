/**
 * @type: itemView
 * name: group.itemView.pendingPost
 * chunkName: group
 */

import { useGlobal, withItemView } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group';
import groupManagerActions from '@metafox/group/actions/groupManagerActions';
import { ItemView, LineIcon } from '@metafox/ui';
import { Button, CardActions, CardContent, styled } from '@mui/material';
import * as React from 'react';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: 0
}));

const StyledButtonMore = styled(Button, {
  name: 'pendingPost',
  slot: 'button-more'
})(({ theme }) => ({
  padding: 0,
  minWidth: 32
}));

const PostsCard = styled(CardContent, {
  name: 'LayoutItemCard',
  slot: 'postsCard',
  overridesResolver(props, styles) {
    return [styles.postsCard];
  }
})(({ theme }) => ({}));

const PendingPostCard = ({
  identity,
  wrapAs: WrapAs,
  wrapProps,
  actions,
  state,
  handleAction
}: GroupItemProps) => {
  const { jsxBackend, i18n, ItemActionMenu } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(FeedContent);

  const content = ContentWrapper({ identity, isItemAction: false });

  return (
    <StyledItemView
      testid="PendingPostCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard>{content}</PostsCard>
      <CardActions sx={{ p: 2, pt: 0 }}>
        <Button
          component="h5"
          sx={{ width: '100px' }}
          variant="contained"
          size="small"
          onClick={actions.approvePendingPost}
        >
          {i18n.formatMessage({ id: 'approve' })}
        </Button>
        <Button
          component="h5"
          sx={{ width: '100px', mr: 1 }}
          variant="outlined"
          size="small"
          onClick={actions.declinePendingPost}
        >
          {i18n.formatMessage({ id: 'decline' })}
        </Button>

        <ItemActionMenu
          menuName="itemPendingActionMenu"
          identity={identity}
          state={state}
          handleAction={handleAction}
          size="small"
          color="primary"
          control={
            <StyledButtonMore variant="outlined" size="small">
              <LineIcon icon="ico-dottedmore-o" />
            </StyledButtonMore>
          }
        />
      </CardActions>
    </StyledItemView>
  );
};

export default withItemView({}, groupManagerActions)(PendingPostCard);
