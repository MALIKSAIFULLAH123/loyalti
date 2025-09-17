import React from 'react';
import RootContainer from '@metafox/framework/app/RootContainer';
import config from '@metafox/react-app/bundle-installation/config';

type AppProps = {
  test?: boolean;
};

// chore: pre-commit-test 5
export default function SetupWizard(props: AppProps) {
  return (
    <RootContainer
      config={config}
      themeId="installation"
      bootstrapName={'SetupWizard@bootstrap'}
    />
  );
}
