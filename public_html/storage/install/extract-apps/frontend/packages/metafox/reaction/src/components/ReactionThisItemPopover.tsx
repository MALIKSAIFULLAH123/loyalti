import { useGlobal } from '@metafox/framework';
import {
  Box,
  ClickAwayListener,
  Grow,
  Paper,
  Popper,
  Tooltip
} from '@mui/material';
import React from 'react';
import { useReactions } from '../hooks';
import { ReactionItem, ReactionThisItemPopoverProps } from '../types';

const Item = ({ item, onClick }: { item: ReactionItem; onClick?: any }) => {
  return (
    <Tooltip title={item.title}>
      <img
        role="button"
        onClick={onClick}
        aria-label={item.title}
        data-testid="itemReaction"
        src={item.src}
        draggable={false}
        width="44px"
        height="44px"
        alt={item.title}
      />
    </Tooltip>
  );
};

export default function ReactionThisItemPopover({
  open,
  anchorEl,
  identity
}: ReactionThisItemPopoverProps) {
  const { usePopover, popoverBackend, dispatch, useIsMobile } = useGlobal();
  const { closePopover } = usePopover();
  const reactions = useReactions();
  const isMobile = useIsMobile();

  React.useEffect(() => {
    if (!isMobile) return;

    const onScroll = () => {
      closePopover();
    };

    window.addEventListener('scroll', onScroll);

    return () => window.removeEventListener('scroll', onScroll);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onClick = (reaction_id: number) => {
    dispatch({ type: 'reactionItem', payload: { identity, reaction_id } });
    closePopover();
  };

  if (!reactions) return null;

  return (
    <ClickAwayListener onClickAway={closePopover} touchEvent={false}>
      <Popper
        style={{ zIndex: 1301 }}
        placement="top-start"
        open={open}
        anchorEl={anchorEl}
        onMouseEnter={popoverBackend.onEnterContent}
        onMouseLeave={popoverBackend.onLeaveContent}
        transition
      >
        {({ TransitionProps }) => (
          <Grow {...TransitionProps} in={open} timeout={250}>
            <Paper
              data-testid="ReactionPickerPopup"
              sx={{
                borderRadius: '24px',
                marginLeft: '-8px',
                padding: '2px',
                zIndex: 1300
              }}
            >
              <Box
                sx={{
                  display: 'flex',
                  '&>img': {
                    padding: '4px',
                    transition: 'transform 180ms',
                    transformOrigin: 'center bottom'
                  },
                  '&>img:hover': { transform: 'scale(1.22)' }
                }}
              >
                {reactions.map(item => (
                  <Item
                    onClick={() => onClick(item?.id)}
                    item={item}
                    key={item?.id}
                  />
                ))}
              </Box>
            </Paper>
          </Grow>
        )}
      </Popper>
    </ClickAwayListener>
  );
}
