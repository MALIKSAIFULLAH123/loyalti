/**
 * @type: ui
 * name: StatusComposerControlSchedulePlace
 */
import { StatusComposerControlProps, useGlobal } from '@metafox/framework';
import React from 'react';
import { FormatDate, LineIcon } from '@metafox/ui';
import { Box } from '@mui/material';

type Props = StatusComposerControlProps;

export default function StatusComposerControlSchedulePlace(props: Props) {
  const { dispatch } = useGlobal();
  const { composerRef, value } = props;
  const { setScheduleTime } = composerRef.current;

  if (!value) return null;

  const handleClick = () => {
    dispatch({
      type: 'statusComposer/openScheduleFeed',
      payload: {
        value,
        setScheduleTime
      }
    });
  };

  return (
    <Box
      px={2}
      py={1}
      sx={{
        background: theme =>
          (theme.palette.mode === 'dark'
            ? theme.palette.grey['900']
            : theme.palette.grey['100']),
        display: 'flex',
        alignItems: 'center',
        cursor: 'pointer'
      }}
      onClick={handleClick}
    >
      <LineIcon icon={'ico-clock-o'} sx={{ mr: 0.5 }} />
      <FormatDate
        phrase={'will_send_on_time'}
        data-testid="publishedDate"
        value={value}
        format="llll"
      />
    </Box>
  );
}
