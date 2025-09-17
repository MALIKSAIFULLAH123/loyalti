/**
 * @type: ui
 * name: dialog.itemProvider
 */
import { useGlobal } from '@metafox/framework';
import { isFunction } from 'lodash';
import React, { useCallback } from 'react';
import DialogContext from './DialogContext';
import { ConfirmParams, TDialogProvider, UserConfirmCallback } from './types';

export default function DialogProvider({
  testid,
  content,
  resolve,
  onClose,
  isLast,
  onEnter,
  onEntering,
  onEntered,
  onExiting,
  onExited,
  type,
  open,
  forceClose,
  backdropClose
}: TDialogProvider) {
  const { jsxBackend, dialogBackend, useTheme, i18n } = useGlobal();
  const theme = useTheme();
  const valueRef = React.useRef<any>();
  const confirmRef = React.useRef<UserConfirmCallback>();
  const disableBackdropCloseRef = React.useRef<boolean>(backdropClose);

  const setDialogValue = React.useCallback(
    (value: any) => {
      valueRef.current = value;
      forceClose();
    },
    [forceClose]
  );

  const setDialogResolveValue = React.useCallback(
    (value: any) => {
      valueRef.current = value;
      resolve(valueRef.current);
    },
    [resolve]
  );

  const disableBackdropClick = React.useCallback((enabled: boolean) => {
    disableBackdropCloseRef.current = enabled;
  }, []);

  const confirmValue = isFunction(confirmRef.current)
    ? confirmRef.current()
    : confirmRef.current;

  const handleUserConfirm = React.useCallback(
    (evt, reason) => {
      if (disableBackdropCloseRef.current && reason === 'backdropClick') {
        return;
      }

      const confirmValue = isFunction(confirmRef.current)
        ? confirmRef.current()
        : confirmRef.current;

      (async () => {
        if (!confirmValue) {
          return onClose();
        }

        const {
          message = i18n.formatMessage({
            id: 'the_change_you_made_will_not_be_saved'
          }),
          title = i18n.formatMessage({ id: 'discard_change' })
        } = confirmValue as ConfirmParams;

        const result = await dialogBackend.confirm({ title, message });

        if (result) {
          onClose();
        }
      })();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [confirmValue]
  );

  const isMobile = theme.breakpoints.values.sm >= window.screen.width;

  const setUserConfirm = useCallback((callback: UserConfirmCallback) => {
    confirmRef.current = callback;
  }, []);

  const dialogProps = React.useMemo(() => {
    return {
      type,
      dialogProps: {
        open,
        fullScreen: isMobile,
        onClose: handleUserConfirm,
        fullWidth: true,
        TransitionProps: {
          onEnter,
          onEntered,
          onEntering,
          onExit: () => resolve(valueRef.current),
          onExiting,
          onExited
        },
        className: isLast ? 'isLastDialog' : 'notLastDialog',
        'data-testid': testid ?? 'dialogPopup'
      },
      closeDialog: handleUserConfirm,
      disableBackdropClick,
      setDialogValue,
      setUserConfirm,
      setDialogResolveValue,
      forceClose
    };
  }, [
    testid,
    type,
    open,
    isMobile,
    handleUserConfirm,
    onEnter,
    onEntered,
    onEntering,
    onExiting,
    onExited,
    isLast,
    setDialogValue,
    setDialogResolveValue,
    setUserConfirm,
    forceClose,
    disableBackdropClick,
    resolve
  ]);

  return (
    <DialogContext.Provider value={dialogProps}>
      {jsxBackend.render(content)}
    </DialogContext.Provider>
  );
}
