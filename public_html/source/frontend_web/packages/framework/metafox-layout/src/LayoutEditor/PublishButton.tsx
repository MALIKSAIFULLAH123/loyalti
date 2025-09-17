import { useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import React from 'react';

function PublishButton({ onClick, ...props }) {
  const { eventCenter, layoutBackend } = useGlobal();
  const [canPublish, setCanPublish] = React.useState<boolean>(
    layoutBackend.isDirty()
  );

  React.useEffect(() => {
    const token = eventCenter.on('layout.canPublish.changed', setCanPublish);

    return () => eventCenter.off('layout.canPublish.changed', token);
  }, [eventCenter]);

  return <Button disabled={!canPublish} onClick={onClick} {...props} />;
}

export default PublishButton;
