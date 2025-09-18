/**
 * @type: itemView
 * name: event.itemView.myPendingPost
 * chunkName: event
 */

import { useGlobal, withItemView } from '@metafox/framework';
import { ItemView, LineIcon } from '@metafox/ui';
import { CardActions, CardContent, styled, Typography } from '@mui/material';
import * as React from 'react';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: 0
}));

const PendingPostCard = ({ identity, wrapAs: WrapAs, wrapProps }) => {
  const { jsxBackend, i18n } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(
    FeedContent as React.FC<any>
  );

  const content = ContentWrapper({ identity });

  return (
    <StyledItemView
      testid="pendingPostCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <CardContent sx={{ pt: 2 }}>{content}</CardContent>
      <CardActions sx={{ pt: 0, pb: 2 }}>
        <Typography variant="body1" sx={{ color: 'text.hint', px: 1 }}>
          <LineIcon icon="ico-clock-o" sx={{ pr: 1 }} />
          {i18n.formatMessage({
            id: 'this_post_is_waiting_for_an_approval_from_host'
          })}
        </Typography>
      </CardActions>
    </StyledItemView>
  );
};

export default PendingPostCard;
