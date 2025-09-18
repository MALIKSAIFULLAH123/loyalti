import { ItemView } from '@metafox/ui';
import React from 'react';
// hooks

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return <ItemView testid="loading" wrapAs={wrapAs} wrapProps={wrapProps} />;
}
