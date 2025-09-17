/**
 * @type: service
 * name: UserPreferenceProvider
 */
import {
  useGlobal,
  UserPreferenceConfig,
  UserPreferenceContext
} from '@metafox/framework';
import React from 'react';

export default function UserPreferenceProvider({
  children
}: {
  children: React.ReactNode;
}) {
  const { preferenceBackend, eventCenter } = useGlobal();
  const [data, setData] = React.useState<UserPreferenceConfig>(
    preferenceBackend.getAll()
  );

  React.useEffect(() => {
    const id = eventCenter.on('onUserPreferenceChanged', setData);

    return () => eventCenter.off('onEventCenter', id);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return <UserPreferenceContext.Provider value={data} children={children} />;
}
