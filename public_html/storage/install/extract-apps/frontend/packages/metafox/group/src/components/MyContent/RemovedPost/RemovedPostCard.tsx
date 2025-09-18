/**
 * @type: itemView
 * name: group.itemView.myRemovedPost
 * chunkName: group
 */

import { useGlobal, withItemView } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group';
import { ItemView } from '@metafox/ui';
import { Button, CardActions, CardContent, styled } from '@mui/material';
import * as React from 'react';

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

const RemovedPostCard = ({
  identity,
  wrapAs: WrapAs,
  wrapProps
}: GroupItemProps) => {
  const { jsxBackend, i18n, dispatch } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(
    FeedContent as React.FC<any>
  );

  const content = ContentWrapper({ identity, isItemAction: false });

  const handleDelete = () => {
    dispatch({ type: 'deleteItem', payload: { identity } });
  };

  return (
    <StyledItemView
      testid="removedPostCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard sx={{ pt: 2 }}>{content}</PostsCard>
      <CardActions sx={{ p: 0, pl: 2, pb: 2 }}>
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

export default RemovedPostCard;
