import { useDraftEditorConfig, useGlobal } from '@metafox/framework';
import { DialogActions } from '@metafox/dialog';
import composerConfig from '@metafox/feed/composerConfig';
import useComposerContext from '@metafox/feed/hooks/useComposerContext';
import React from 'react';
import Control from './Control';
import MuiButton from '@mui/lab/LoadingButton';
import { styled } from '@mui/material';

const ActionStyled = styled('div')(({ theme }) => ({
  flex: 1,
  [theme.breakpoints.down('sm')]: {
    '& span': {
      marginRight: theme.spacing(1.5)
    }
  }
}));
interface Props {
  submitting: boolean;
  onSubmit: () => void;
  disabledSubmit: boolean;
  parentIdentity?: string;
  parentType?: string;
  isShare?: boolean;
}

const ComposerAction = ({
  submitting,
  onSubmit,
  disabledSubmit,
  parentIdentity,
  parentType,
  isShare
}: Props) => {
  const { jsxBackend, i18n, useGetItem, useSession } = useGlobal();
  const parentUser = useGetItem(parentIdentity);
  const { user: authUser } = useSession();
  const {
    composerRef,
    classes,
    editorRef,
    condition,
    strategy,
    isEdit,
    isEditSchedule,
    asPage,
    composerState
  } = useComposerContext();

  const [, , , attachers] = useDraftEditorConfig(composerConfig, condition);

  let updateBtnLabel = i18n.formatMessage({
    id: isEdit ? 'save' : isShare ? 'share' : 'post'
  });

  if (composerState?.schedule_time?.value) {
    updateBtnLabel = i18n.formatMessage({
      id: isEditSchedule ? 'save' : 'schedule'
    });
  }

  return (
    <DialogActions className={classes.dialogActions}>
      <MuiButton
        variant="contained"
        data-testid="submit"
        onClick={onSubmit}
        disabled={disabledSubmit}
        color="primary"
        className={classes.btnShare}
      >
        {updateBtnLabel}
      </MuiButton>
      <ActionStyled>
        {attachers.map(item =>
          jsxBackend.render({
            component: item.as,
            props: {
              disabled: item.disabled,
              key: item.as,
              strategy,
              control: Control,
              composerRef,
              editorRef,
              parentIdentity,
              parentType,
              userId: asPage ? parentUser?.id : authUser?.id,
              userIdentity: asPage ? parentUser?._identity : authUser?._identity
            }
          })
        )}
      </ActionStyled>
    </DialogActions>
  );
};

export default ComposerAction;
