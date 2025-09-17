import React from 'react';
import RootContainer from '../app/RootContainer';
import config from '@metafox/react-app/bundle-admincp/config';

type AppProps = {
  test?: boolean;
};

// chore: pre-commit-test 5
export default function AdminCP(props: AppProps) {
  return <RootContainer config={config} />;
}
