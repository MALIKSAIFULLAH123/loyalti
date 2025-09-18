import { useActionControl, useGlobal } from '@metafox/framework';
import { StoryContextProps } from '@metafox/story/context/StoryViewContext';
import { useStory } from '@metafox/story/hooks';
import { PauseStatus } from '@metafox/story/types';
import { ItemActionMenu, LineIcon } from '@metafox/ui';
import { IconButton, Tooltip, styled, Box } from '@mui/material';
import React from 'react';
import { DeleteSuccessType } from './HeaderStory';

interface PropsItemAction {
  identity: string;
  contextStory: StoryContextProps;
  onDeleteSuccess: ({ nextStory }: DeleteSuccessType) => void;
}

const IconButtonStyled = styled(IconButton)(({ theme }) => ({
  color: '#fff'
}));

export const ItemActionItem = React.memo(
  ({ identity, contextStory, onDeleteSuccess: onSuccess }: PropsItemAction) => {
    const { i18n, dispatch } = useGlobal();
    const {
      pauseStatus,
      openStoryDetail,
      openViewComment,
      openActionItem,
      fire
    } = contextStory;

    const { handleNext, handlePrev, hasNext } = useStory();

    const [handleActionLocal] = useActionControl<unknown, unknown>(
      identity,
      {}
    );

    const handleAction = (type: string, payload?: unknown, meta?: unknown) => {
      if (/deleteItem|mute/.test(type)) {
        dispatch({
          type,
          payload: { identity },
          meta: {
            onSuccess: () => onSuccess({ nextStory: !!/mute/.test(type) })
          }
        });

        return;
      }

      handleActionLocal(type, payload, meta);
    };

    const [openMenu, setOpenMenu] = React.useState(false);

    const triggerOpenMenu = value => {
      setOpenMenu(value);
    };

    React.useEffect(() => {
      if (pauseStatus === PauseStatus.Force || openActionItem) return;

      fire({
        type: 'setForcePause',
        payload: openMenu ? PauseStatus.Pause : PauseStatus.No
      });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [openMenu, openActionItem]);

    React.useEffect(() => {
      fire({ type: 'setForcePause', payload: PauseStatus.No });
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [identity]);

    React.useEffect(() => {
      if (openActionItem || openMenu || openStoryDetail || openViewComment)
        return;

      const downHandler = e => {
        const { keyCode } = e;

        if (keyCode === 37) {
          handlePrev();

          return;
        }

        if (hasNext && keyCode === 39) {
          handleNext();
        }

        if (keyCode === 32) {
          fire({
            type: 'setForcePause',
            payload:
              pauseStatus === PauseStatus.No
                ? PauseStatus.Pause
                : PauseStatus.No
          });
        }
      };

      window.addEventListener('keydown', downHandler);

      return () => {
        window.removeEventListener('keydown', downHandler);
      };
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [
      hasNext,
      handlePrev,
      handleNext,
      pauseStatus,
      openStoryDetail,
      openViewComment,
      openMenu,
      openActionItem
    ]);

    return (
      <>
        <Box
          sx={
            openMenu
              ? { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0 }
              : {}
          }
        />
        <ItemActionMenu
          triggerOpen={triggerOpenMenu}
          identity={identity}
          handleAction={handleAction}
          tabIndex={1}
          control={
            <Tooltip
              title={i18n.formatMessage({ id: 'more_options' })}
              placement="bottom"
            >
              <IconButtonStyled size="medium" disableRipple disableFocusRipple>
                <LineIcon icon="ico-dottedmore" />
              </IconButtonStyled>
            </Tooltip>
          }
        />
      </>
    );
  },
  (prev, next) =>
    prev?.identity === next?.identity &&
    prev?.contextStory?.pauseStatus === next?.contextStory?.pauseStatus &&
    prev?.contextStory === next?.contextStory
);
