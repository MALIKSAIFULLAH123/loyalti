/**
 * @type: dialog
 * name: feed.status.statusComposerDialog
 * chunkName: dialog.StatusComposer
 */
import { Dialog } from '@metafox/dialog';
import {
  StatusComposerState,
  useGlobal,
  useLocation
} from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { isEmpty, isEqual, get, isNil } from 'lodash';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import useStatusComposer from '../../hooks/useStatusComposer';
import ComposerAction from './ComposerAction';
import ComposerContent from './ComposerContent';
import ComposerHeader from './ComposerHeader';
import useStyles from './styles';
import { REGEX_LENGTH_TEXT } from '@metafox/feed/constant';
import BlockLoading from './BlockLoading';
import { customExtractLinks } from '@metafox/feed/utils';
import { transform } from '@metafox/lexical';
import { $getRoot } from 'lexical';
import composerHtmlToTextData from '@metafox/feed/utils/composerHtmlToTextData';

export const DialogStatusComposer = styled(Box)({
  position: 'relative'
});

export const ComposerContext = React.createContext(undefined);

export type StatusComposerDialogProps = {
  data: Partial<StatusComposerState>;
  editor?: {
    status_text?: string;
    status_background_id?: string;
  };
  id?: string;
  isEdit?: boolean;
  isEditSchedule?: boolean;
  parentIdentity?: string;
  title?: string;
  parentType?: string;
  hidePrivacy?: boolean;
  disabledPrivacy?: boolean;
  enableLeaveConfirm?: boolean;
  pageParams?: Record<string, any>;
};

const strategy = 'dialog';

// deepcompare prevent loop useEffect
// Should track and improve
function useDeepEffect(fn, deps) {
  const isFirst = useRef(true);
  const prevDeps = useRef(deps);

  useEffect(() => {
    const isFirstEffect = isFirst.current;
    const isSame = prevDeps.current.every((obj, index) =>
      isEqual(obj, deps[index])
    );

    isFirst.current = false;
    prevDeps.current = deps;

    if (isFirstEffect || !isSame) {
      return fn();
    }
  }, deps);
}

const StatusComposerDialog = ({
  data = {},
  editor,
  id,
  isEdit,
  isEditSchedule,
  parentIdentity,
  parentType,
  hidePrivacy,
  disabledPrivacy,
  title = 'create_post',
  pageParams,
  enableLeaveConfirm = true
}: StatusComposerDialogProps) => {
  const isEditState = isEdit || isEditSchedule;
  const classes = useStyles();
  const location = useLocation();
  const {
    useDialog,
    dispatch,
    i18n,
    getSetting,
    getAcl,
    setNavigationConfirm,
    useSession,
    useGetItem
  } = useGlobal();
  const { user: authUser } = useSession();
  const parentId = parentIdentity ? parentIdentity.split('.')[3] : '';
  const item = useGetItem(parentIdentity);
  const isUserProfileOther =
    parentType === 'user' && parentId && authUser?.id !== parseInt(parentId);
  const isUserProfileOwner =
    parentType === 'user' && parentId && authUser?.id === parseInt(parentId);
  const [composerState, , composerRef] = useStatusComposer(data);
  const { dialogProps, setUserConfirm, forceClose } = useDialog();

  const editorRef = React.useRef({});
  const isFirstRun = useRef<boolean>(true);

  const [disabledSubmit, setDisabled] = useState<boolean>();
  const [loadingPreviewLink, setLoadingPreviewLink] = useState<boolean>(false);
  const [submitting, setSubmitting] = useState<boolean>(false);
  const [asPage, setAsPage] = React.useState<boolean>(false);
  const isShare = get(composerState, 'attachments.shareItem.value');

  const getTextSimple = () => {
    if (editorRef.current) {
      const editor = editorRef.current?.editor;

      if (!editor) return;

      const stringifiedEditorState = JSON.stringify(
        editor.getEditorState().toJSON()
      );
      const parsedEditorState = editor.parseEditorState(stringifiedEditorState);

      return parsedEditorState.read(() => $getRoot().getTextContent());
    }
  };

  const [editorState, setEditorState] = React.useState<string>(
    transform(editor?.status_text || '')
  );

  const SETTING_MAX_LENGTH_COMPOSER: number =
    getSetting('activity.feed.maximum_characters_for_post_status') || 0;

  const setting = getSetting() as Object;
  const acl = getAcl() as Object;

  const getCurrenTextData = React.useCallback(() => {
    return composerHtmlToTextData(editorState).trim();
  }, [editorState]);

  const condition = React.useMemo(() => {
    const currentText = getTextSimple();
    const lengthText = currentText
      ? currentText.replace(REGEX_LENGTH_TEXT, '$3')?.trim()?.length
      : 0;

    return {
      strategy,
      item,
      attachmentType: composerState.attachmentType,
      attachments: composerState.attachments,
      lengthText,
      parentType,
      textLines: getCurrenTextData()?.split(/\r\n|\r|\n/).length,
      isEdit,
      data,
      setting,
      acl,
      isUserProfileOther,
      isUserProfileOwner
    };
  }, [
    item,
    composerState.attachmentType,
    composerState.attachments,
    parentType,
    isEdit,
    data,
    setting,
    acl,
    isUserProfileOther,
    isUserProfileOwner,
    getCurrenTextData
  ]);

  const leaveConfirm = React.useMemo(() => {
    return {
      message: i18n.formatMessage({
        id: 'you_did_not_share_your_post'
      }),
      title: i18n.formatMessage({
        id: 'leave_page'
      }),
      negativeButton: {
        label: i18n.formatMessage({
          id: 'keep_editing'
        })
      },
      positiveButton: {
        label: i18n.formatMessage({
          id: 'leave'
        })
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleAfterForceClose = useCallback(() => {
    forceClose();
    dispatch({
      type: 'formValues/onDestroy',
      payload: {
        formName: 'dialogStatusComposer'
      }
    });
  }, [dispatch, forceClose]);

  useEffect(() => {
    if (!isEditState) {
      setNavigationConfirm(
        !disabledSubmit && enableLeaveConfirm,
        leaveConfirm,
        () => {
          setEditorState('');
          handleAfterForceClose();
        }
      );
    } else {
      // alert when close popup
      setUserConfirm(() => {
        if (!disabledSubmit) {
          return {
            message: i18n.formatMessage({
              id: 'the_change_you_made_will_not_be_saved'
            }),
            title: i18n.formatMessage({
              id: 'unsaved_changes'
            })
          };
        }
      });
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [disabledSubmit]);

  useEffect(() => {
    if (isFirstRun.current) {
      isFirstRun.current = false;

      return;
    }

    forceClose();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location]);

  useEffect(() => {
    if (isEdit || isNil(get(composerState, 'attachments.link.value'))) return;

    const isHasLinkText = customExtractLinks({
      text: getCurrenTextData(),
      recognizeMail: false
    });

    const hasBackgroundStatus = Boolean(
      get(composerState, 'attachments.statusBackground.value.id')
    );

    if (hasBackgroundStatus) {
      if (!isHasLinkText) {
        composerRef.current.removeAttachmentLink();
      }

      return;
    }

    if (
      (editorState.trim() === '' || !isHasLinkText) &&
      composerState.attachments?.['link']?.value?.is_preview_hidden
    ) {
      composerRef.current.removeAttachmentLink();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    editorState,
    composerState.attachments,
    composerState.attachmentType,
    composerRef.current
  ]);

  useDeepEffect(() => {
    const lengthText = [
      ...(getTextSimple()?.replace(REGEX_LENGTH_TEXT, '$3')?.trim() || '')
    ]?.length;

    const isHasTag =
      !isEmpty(composerState.tags?.place?.value) ||
      !isEmpty(composerState.tags?.friends?.value);
    let disabled =
      editorState.trim() === '' &&
      (composerState.attachments?.['link']?.value?.is_preview_hidden ||
        !(
          Object.keys(composerState.attachments || {})?.length ||
          ['poll', 'photo'].includes(composerState.attachmentType)
        )) &&
      !isHasTag;
    const isDirty =
      composerState.editing || editorState !== (editor?.status_text || '');

    if (
      SETTING_MAX_LENGTH_COMPOSER > 0 &&
      lengthText > SETTING_MAX_LENGTH_COMPOSER
    ) {
      disabled = true;
    }

    if (isEditState && !disabled) {
      disabled = !isDirty;
    }

    if (composerState.disabled !== disabled) {
      composerRef.current.setDisabled(disabled);
    }

    setDisabled(submitting || loadingPreviewLink || disabled);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    submitting,
    editorState,
    composerState,
    composerRef,
    editor,
    loadingPreviewLink
  ]);

  const handleSubmit = useCallback(() => {
    setSubmitting(true);
    dispatch({
      type: 'statusComposer/SUBMIT',
      payload: {
        initValue: data,
        composerState: composerRef.current.state,
        text: getCurrenTextData(),
        isEdit,
        isEditSchedule,
        id,
        parentIdentity,
        parentUser: data?.parentUser
      },
      meta: {
        onSuccess: handleAfterForceClose,
        onFailure: () => setSubmitting(false)
      }
    });
  }, [
    dispatch,
    data,
    composerRef,
    isEdit,
    id,
    parentIdentity,
    handleAfterForceClose,
    isEditSchedule,
    getCurrenTextData
  ]);

  const handleClose = (e, reason) => {
    if (submitting) return;

    // update form value to reducers
    !isEditState &&
      dispatch({
        type: 'formValues/onChange',
        payload: {
          formName: 'dialogStatusComposer',
          data: getTextSimple() || ''
        }
      });

    dialogProps.onClose && dialogProps.onClose(e, reason);
  };

  return (
    <Dialog
      {...dialogProps}
      data-testid="dialogStatusComposer"
      fullWidth
      onClose={handleClose}
      sx={{
        '& .MuiPaper-root': {
          maxWidth: theme => theme.middleBlock.maxWidth || '720px'
        }
      }}
    >
      <DialogStatusComposer>
        <ComposerContext.Provider
          value={{
            data,
            classes,
            composerState,
            composerRef,
            editorState,
            condition,
            setEditorState,
            isEdit,
            isEditSchedule,
            strategy,
            editor,
            editorRef,
            pageParams,
            asPage,
            setAsPage,
            getTextSimple,
            getCurrenTextData
          }}
        >
          <ComposerHeader title={title} closeDialog={handleClose} />
          <ComposerContent
            hidePrivacy={hidePrivacy}
            parentIdentity={parentIdentity}
            parentType={parentType}
            disabledPrivacy={disabledPrivacy}
            setLoadingPreviewLink={setLoadingPreviewLink}
            loadingPreviewLink={loadingPreviewLink}
            submitting={submitting}
          />
          <ComposerAction
            submitting={submitting}
            onSubmit={handleSubmit}
            disabledSubmit={disabledSubmit}
            parentIdentity={parentIdentity}
            parentType={parentType}
            isShare={isShare}
          />
        </ComposerContext.Provider>
        {submitting ? <BlockLoading /> : null}
      </DialogStatusComposer>
    </Dialog>
  );
};

export default StatusComposerDialog;
