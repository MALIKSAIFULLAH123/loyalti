/**
 * @type: itemView
 * name: group.itemView.myPendingPost
 * chunkName: group
 */

import { useGlobal, withItemView } from '@metafox/framework';
import { GroupItemProps } from '@metafox/group';
import { ButtonList, ItemView, LineIcon } from '@metafox/ui';
import {
  Button,
  CardActions,
  CardContent,
  styled,
  Typography
} from '@mui/material';
import * as React from 'react';
import { LoadingSkeleton } from '../LoadingSkeleton';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: 0
}));

const StyledTypography = styled(Typography)(({ theme }) => ({
  color: 'text.hint',
  display: 'flex',
  alignItems: 'center'
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

const PendingPostCard = ({
  identity,
  wrapAs: WrapAs,
  wrapProps,
  actions,
  state,
  handleAction
}: GroupItemProps) => {
  const { jsxBackend, i18n, dispatch } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(
    FeedContent as React.FC<any>
  );

  const content = ContentWrapper({ identity, isItemAction: false });

  const editPost = () => {
    dispatch({ type: 'editFeedItem', payload: { identity } });
  };

  const deletePost = () => {
    dispatch({ type: 'deleteItem', payload: { identity } });
  };

  return (
    <StyledItemView
      testid="pendingPostCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <PostsCard>{content}</PostsCard>
      <CardActions sx={{ p: 2, pt: 0, pb: 2 }}>
        <StyledTypography variant="body1">
          <LineIcon icon="ico-clock-o" sx={{ pr: 1 }} />
          {i18n.formatMessage({ id: 'this_post_is_waiting_for_an_approval' })}
        </StyledTypography>
      </CardActions>
      <ButtonList sx={{ p: 0, pl: 2, pb: 2 }}>
        <Button size="small" variant="contained" onClick={editPost}>
          {i18n.formatMessage({ id: 'edit_post' })}
        </Button>
        <StyledButtonDelete
          size="small"
          color="error"
          variant="outlined"
          onClick={deletePost}
        >
          {i18n.formatMessage({ id: 'delete_post' })}
        </StyledButtonDelete>
      </ButtonList>
    </StyledItemView>
  );
};

PendingPostCard.LoadingSkeleton = LoadingSkeleton;

export default PendingPostCard;
