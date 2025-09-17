import { useGlobal } from '@metafox/framework';
import {
  HIDE_ALL_OWNER,
  HIDE_ALL_SHARED_OWNER,
  HIDE_ALL_SHARED_USER,
  HIDE_ALL_USER,
  HIDE_ITEM,
  SNOOZE_OWNER,
  SNOOZE_SHARED_OWNER,
  SNOOZE_SHARED_USER,
  SNOOZE_USER
} from '@metafox/feed/constant';
import { LineIcon } from '@metafox/ui';
import { Button, styled } from '@mui/material';
import * as React from 'react';

const hiddenDescription = is_hidden_type => {
  switch (is_hidden_type) {
    case HIDE_ITEM:
      return ['post_hidden', 'you_wont_see_this_post_in_your_feed'];
    case HIDE_ALL_USER:
    case HIDE_ALL_OWNER:
    case HIDE_ALL_SHARED_USER:
    case HIDE_ALL_SHARED_OWNER:
      return [
        'all_post_hidden',
        'you_wont_see_post_from_this_user_page_group_on_your_news_feed_again'
      ];
    case SNOOZE_USER:
    case SNOOZE_OWNER:
    case SNOOZE_SHARED_USER:
    case SNOOZE_SHARED_OWNER:
      return [
        'all_post_snoozed',
        'you_wont_see_post_from_this_user_page_group_on_your_news_feed_30_days'
      ];
  }
};

const hiddenAction = (is_hidden_type, item) => {
  switch (is_hidden_type) {
    case HIDE_ITEM:
      return ['undoHideItem', null];
    case HIDE_ALL_USER:
      return ['undoUnfollowPoster', item.user_full_name];
    case HIDE_ALL_OWNER:
      return ['undoUnfollowOwner', item.owner_full_name];
    case HIDE_ALL_SHARED_USER:
      return ['undoUnfollowSharedPoster', item?.shared_user_full_name];
    case HIDE_ALL_SHARED_OWNER:
      return ['undoUnfollowSharedOwner', item?.shared_owner_full_name];
    case SNOOZE_USER:
      return ['undoSnoozePoster', item.user_full_name];
    case SNOOZE_OWNER:
      return ['undoSnoozeOwner', item.owner_full_name];
    case SNOOZE_SHARED_USER:
      return ['undoSnoozeSharedPoster', item?.shared_user_full_name];
    case SNOOZE_SHARED_OWNER:
      return ['undoSnoozeSharedOwner', item?.shared_owner_full_name];
  }
};

const HiddenDescription = styled('div', { name: 'HiddenDescription' })(
  ({ theme }) => ({
    paddingLeft: theme.spacing(2)
  })
);

export default function FeedItemHiddenView({ item, classes, handleAction }) {
  const { i18n } = useGlobal();
  const { is_hidden_type } = item;

  const [title, text] = hiddenDescription(is_hidden_type);
  const [action, user] = hiddenAction(is_hidden_type, item);

  return (
    <div className={classes.hiddenFeed}>
      <div className={classes.hiddenText}>
        <LineIcon icon="ico-eye-off-o" className={classes.hiddenIcon} />
        <HiddenDescription>
          <div className={classes.hiddenTitle}>
            <div>{i18n.formatMessage({ id: title })} </div>
          </div>
          <div className={classes.hiddenSubtitle}>
            {i18n.formatMessage(
              {
                id: text
              },
              {
                value: user
              }
            )}
          </div>
        </HiddenDescription>
      </div>
      <div className={classes.hiddenAction}>
        <Button
          onClick={() => handleAction(action)}
          size="small"
          variant="outlined"
          color="default"
        >
          {i18n.formatMessage({ id: 'undo' })}
        </Button>
      </div>
    </div>
  );
}
