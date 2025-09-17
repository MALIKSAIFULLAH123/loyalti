import { useGlobal, PAGINATION_REFRESH, useLocation } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, Box, styled } from '@mui/material';
import * as React from 'react';
import useGetFeedPaging from '@metafox/feed/hooks/useGetFeedPaging';

export type Props = {
  pagingId: string;
};

const name = 'FeedNotificationNews';

const Wrapper = styled(Box, { name, slot: 'wrapperStyled' })(({ theme }) => ({
  position: 'fixed',
  top: theme.spacing(9),
  display: 'inline-flex',
  left: '50%',
  transform: 'translateX(-50%)',
  zIndex: theme.zIndex.appBar + 10
}));

export default function FeedNotificationNews() {
  const { useSession, i18n, dispatch, getSetting, triggerScrollTop } =
    useGlobal();
  const { user: authUser } = useSession();
  const { key } = useLocation();
  const [isActive, setIsActive] = React.useState<boolean>(false);
  const timer = React.useRef<number>();
  const checkNewInMinutes = getSetting('activity.feed.check_new_in_minutes');
  const interval = parseFloat(checkNewInMinutes) * 60000;
  const ready = authUser && !!checkNewInMinutes;
  const disabled = 0 >= interval;
  const pagingFeed = useGetFeedPaging();
  const pagingId = pagingFeed.active;
  const handleTriggerScrollTop = React.useCallback(() => {
    if (triggerScrollTop) {
      triggerScrollTop();
    }
    // eslint-disable-next-line
  }, []);

  const handleClick = () => {
    setIsActive(false);
    handleTriggerScrollTop();
    dispatch({
      type: PAGINATION_REFRESH,
      payload: { pagingId }
    });
  };

  React.useEffect(() => {
    if (!ready || disabled) return;

    if (timer?.current) {
      clearInterval(timer.current);
    }

    timer.current = window.setInterval(() => {
      if (isActive) return;

      dispatch({
        type: 'feed/checkNews',
        payload: { pagingId },
        meta: { onSuccess: () => setIsActive(true) }
      });
    }, interval);

    return () => clearInterval(timer.current);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pagingId, isActive, ready]);

  React.useEffect(() => {
    setIsActive(false);
  }, [key]);

  if (!ready || !isActive || disabled) {
    return null;
  }

  return (
    <Wrapper>
      <Button
        data-testid="buttonFetchNewFeed"
        role="button"
        id="buttonFetchNewFeed"
        autoFocus
        color="primary"
        size="smaller"
        variant="contained"
        onClick={handleClick}
        sx={{
          fontWeight: '400 !important',
          fontSize: '15px !important',
          borderRadius: '999px',
          padding: '16px 24px !important'
        }}
        endIcon={
          <LineIcon sx={{ fontSize: '16px !important' }} icon=" ico-arrow-up" />
        }
      >
        {i18n.formatMessage({ id: 'new_updates' })}
      </Button>
    </Wrapper>
  );
}
