import { useGlobal } from '@metafox/framework';
import { DialogTitle } from '@metafox/dialog';
import React from 'react';

interface Props {
  title: string;
  closeDialog?: () => void;
}

const ComposerHeader = ({ title, closeDialog }: Props) => {
  const { i18n } = useGlobal();

  return (
    <DialogTitle data-testid="PopupTitle" onCancel={closeDialog}>
      {i18n.formatMessage({ id: `${title}` })}
    </DialogTitle>
  );
};

export default ComposerHeader;
