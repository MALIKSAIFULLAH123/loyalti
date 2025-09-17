import { useGlobal } from '@metafox/framework';
import React from 'react';

export default function connectSubject(DetailView: React.FC<any>) {
  const ConnectSubject = (props: any) => {
    const { usePageParams } = useGlobal();
    const { identity } = usePageParams();

    return <DetailView identity={identity} {...props} />;
  };

  return ConnectSubject;
}
