import { ItemView } from '@metafox/ui';
import React from 'react';
// hooks

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return <ItemView testid="skeleton" wrapAs={wrapAs} wrapProps={wrapProps} />;
}
