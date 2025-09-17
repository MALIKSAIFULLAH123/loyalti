import React, { useCallback, useMemo, useState } from 'react';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { styled, Box } from '@mui/material';

const name = 'EditorRichTextModal';

const DialogContentStyled = styled(DialogContent, { name, slot: 'root' })(
  ({ theme }) => ({
    padding: '0 !important',
    overflowY: 'visible',
    display: 'flex'
  })
);

export default function useModal(): [
  JSX.Element | null,
  (title: string, showModal: (onClose: () => void) => JSX.Element) => void
] {
  const [modalContent, setModalContent] = useState<null | {
    content: JSX.Element;
    title: string;
    props: Record<string, any>;
  }>(null);
  const { i18n } = useGlobal();

  const onClose = useCallback(() => {
    setModalContent(null);
  }, []);

  const modal = useMemo(() => {
    if (modalContent === null) {
      return null;
    }

    const { title, content, props = {} } = modalContent;

    return (
      <Dialog
        maxWidth="xs"
        fullWidth
        scroll="body"
        onClose={onClose}
        open
        {...props}
      >
        {title ? (
          <DialogTitle onCancel={onClose}>
            {i18n.formatMessage({ id: title })}
          </DialogTitle>
        ) : null}
        <DialogContentStyled>
          <Box p={2} sx={{ width: '100%' }}>
            {content}
          </Box>
        </DialogContentStyled>
      </Dialog>
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [modalContent, onClose]);

  const showModal = useCallback(
    (
      title: string,
      // eslint-disable-next-line no-shadow
      getContent: (onClose: () => void) => JSX.Element,
      props?: Record<string, any>
    ) => {
      setModalContent({
        content: getContent(onClose),
        title,
        props
      });
    },
    [onClose]
  );

  return [modal, showModal];
}
