/**
 * @type: itemView
 * name: user.itemView.reviewPosts
 * chunkName: user
 */

import { useGlobal, withItemView } from '@metafox/framework';
import { ButtonList, ItemView } from '@metafox/ui';
import { Button, CardContent, styled } from '@mui/material';
import * as React from 'react';

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

const ReviewPostsCard = ({ identity, wrapAs: WrapAs, wrapProps }) => {
  const { jsxBackend, i18n, dispatch } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(
    FeedContent as React.FC<any>
  );

  const content = ContentWrapper({
    identity,
    menuName: 'itemActionMenuForProfile'
  });

  const allowPost = () => {
    dispatch({ type: 'setting/reviewPosts/allowed', payload: { identity } });
  };

  const hidePost = () => {
    dispatch({ type: 'setting/reviewPosts/hideItem', payload: { identity } });
  };

  return (
    <StyledItemView
      testid="ReviewPostsCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard>{content}</PostsCard>
      <ButtonList sx={{ p: 2, pt: 0 }}>
        <StyledButton variant="contained" onClick={allowPost}>
          {i18n.formatMessage({ id: 'allow' })}
        </StyledButton>
        <StyledButton variant="outlined" onClick={hidePost}>
          {i18n.formatMessage({ id: 'hide' })}
        </StyledButton>
      </ButtonList>
    </StyledItemView>
  );
};

export default ReviewPostsCard;
