/**
 * @type: itemView
 * name: user.itemView.schedulePosts
 * chunkName: feed
 */

import { useGetItem, useGlobal, withItemView } from '@metafox/framework';
import { ButtonList, ItemView } from '@metafox/ui';
import { Button, CardContent, styled } from '@mui/material';
import * as React from 'react';
import { APP_FEED, RESOURCE_SCHEDULE } from '@metafox/feed';
import { filterShowWhen, withDisabledWhen } from '@metafox/utils';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: theme.spacing(0)
}));
const StyledButton = styled(Button)(({ theme }) => ({
  height: theme.spacing(4)
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

const FeedContent = props => {
  const { jsxBackend } = useGlobal();

  return jsxBackend.render({ component: 'feed.itemView.content', props });
};

const ContentWrapper = withItemView({}, () => {})(FeedContent as React.FC<any>);

const ReviewPostsCard = ({ identity, wrapAs: WrapAs, wrapProps }) => {
  const { dispatch, useResourceMenu, getAcl, getSetting } = useGlobal();
  const menuInline = useResourceMenu(
    APP_FEED,
    RESOURCE_SCHEDULE,
    'scheduledItemMenu'
  );
  const acl = getAcl();
  const setting = getSetting();
  const item = useGetItem(identity);
  const condition = { item, acl, setting };
  const menus = withDisabledWhen(
    filterShowWhen(menuInline.items, condition),
    condition
  );

  if (!identity) return null;

  const handleAction = type => {
    dispatch({ type, payload: { identity } });
  };

  return (
    <StyledItemView
      testid="ReviewPostsCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard>
        <ContentWrapper identity={identity} menuName="itemActionMenu" />
      </PostsCard>
      {menus.length > 0 ? (
        <ButtonList sx={{ p: 2, pt: 0 }}>
          {menus.map((menuItem, index) => {
            const { label, value, buttonProps, disabled } = menuItem || {};

            return (
              <StyledButton
                key={`k${index}`}
                variant="contained"
                {...buttonProps}
                onClick={() => handleAction(value)}
                disabled={disabled}
              >
                {label}
              </StyledButton>
            );
          })}
        </ButtonList>
      ) : null}
    </StyledItemView>
  );
};

export default ReviewPostsCard;
