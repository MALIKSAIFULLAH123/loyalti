import { useGlobal } from '@metafox/framework';
import React from 'react';

export default function withManager<T = {}, S = {}>(
  Wrapper: React.Component<T, S>
): React.FC<T> {
  function WithManagerComponent(props: T) {
    const manager = useGlobal();

    return <Wrapper {...props} manager={manager} />;
  }

  WithManagerComponent.displayName = `withManager(${Wrapper.displayName})`;

  return WithManagerComponent;
}
