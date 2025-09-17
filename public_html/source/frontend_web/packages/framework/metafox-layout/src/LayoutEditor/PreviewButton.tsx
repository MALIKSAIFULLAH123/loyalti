import { useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import React from 'react';

function PreviewButton({ disabled, onClick, ...props }) {
  const { eventCenter, layoutBackend } = useGlobal();
  const [dirty, setThemeDirty] = React.useState<boolean>(
    layoutBackend.isDirty()
  );

  React.useEffect(() => {
    const token = eventCenter.on('layout.dirty.changed', setThemeDirty);

    return () => eventCenter.off('layout.dirty.changed', token);
  }, [eventCenter]);

  return <Button disabled={disabled || !dirty} onClick={onClick} {...props} />;
}

export default PreviewButton;
