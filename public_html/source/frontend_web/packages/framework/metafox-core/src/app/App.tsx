import React from 'react';
import RootContainer from './RootContainer';
import config from '@metafox/react-app/bundle-web/config';

type AppProps = {
  test?: boolean;
  template?: React.FC<{}>;
};

// chore: pre-commit-test 5
export default function App(props: AppProps) {
  return <RootContainer config={config} {...props} />;
}
