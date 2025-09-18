/**
 * @type: itemView
 * name: event.itemView.pendingPost
 * chunkName: event
 */

import { eventManageActions } from '@metafox/event';
import { useGlobal, withItemView } from '@metafox/framework';
import { ItemView } from '@metafox/ui';
import {
  Box,
  Button,
  CardActions,
  CardContent,
  Divider,
  styled
} from '@mui/material';
import { camelCase } from 'lodash';
import * as React from 'react';

const Description = styled('span')(({ theme }) => ({
  fontSize: theme.spacing(1.875),
  fontWeight: theme.typography.fontWeightMedium,
  lineHeight: theme.spacing(3),
  color: theme.palette.text.hint
}));

const PendingPostCard = ({ identity, wrapAs: WrapAs, wrapProps, actions }) => {
  const { jsxBackend, i18n } = useGlobal();

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = FeedContent
    ? withItemView({}, () => {})(FeedContent)
    : null;

  const content = ContentWrapper({ identity, isItemAction: false });

  return (
    <ItemView
      testid="event-pendingPostCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <CardContent
        data-testid={camelCase('event pending post content')}
        sx={{ padding: 0 }}
      >
        {content}
        <Divider />
      </CardContent>
      <Box
        data-testid={camelCase('event pending post description')}
        sx={{ px: 0, py: 2 }}
      >
        <Description>
          {i18n.formatMessage({ id: 'please_carefully_review_this_post' })}
        </Description>
      </Box>
      <CardActions
        data-testid={camelCase('event pending post action')}
        sx={{ padding: 0 }}
      >
        <Button
          data-testid={camelCase('event pending post approve')}
          component="h5"
          sx={{ width: '100px' }}
          variant="contained"
          size="small"
          onClick={actions.approvePendingPost}
        >
          {i18n.formatMessage({ id: 'approve' })}
        </Button>
        <Button
          data-testid={camelCase('event pending post delete')}
          component="h5"
          sx={{ width: '100px' }}
          variant="outlined"
          size="small"
          color="error"
          onClick={actions.declinePendingPost}
        >
          {i18n.formatMessage({ id: 'delete' })}
        </Button>
      </CardActions>
    </ItemView>
  );
};

export default withItemView({}, eventManageActions)(PendingPostCard);
