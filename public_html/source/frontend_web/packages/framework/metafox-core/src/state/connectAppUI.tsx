import { usePageParams } from '@metafox/layout';
import React from 'react';
import { connect } from 'react-redux';

const mapStateToProps = (state, { appName }) => {
  return state[appName]?.uiConfig || {};
};

export default function connectUI(Base) {
  const Connected = connect(mapStateToProps)(Base);

  function Enhance(props: any) {
    const { appName } = usePageParams();
    const app = props.appName || appName;

    return <Connected appName={app} {...props} />;
  }

  return Enhance;
}
