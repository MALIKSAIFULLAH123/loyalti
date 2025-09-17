import { BlockViewProps } from '@metafox/framework';
import { BlockContext } from '@metafox/layout';
import { merge } from 'lodash';
import React from 'react';
import useGlobal from '../hooks/useGlobal';

export default function withBlockConfig<
  T extends BlockViewProps = BlockViewProps
>(Base: React.FC<T>, config: Partial<T>): React.FC<T> {
  function WithBlockConfig(mergedProps: T) {
    const { layoutBackend } = useGlobal();

    const { blockLayout, gridLayout } = config;

    if (blockLayout) {
      const styleProps = layoutBackend.getBlockPreset(blockLayout);

      if (styleProps) {
        mergedProps = merge(mergedProps, styleProps);
      }
    }

    if (gridLayout) {
      const styleProps = layoutBackend.getGridPreset(gridLayout);

      if (styleProps) {
        mergedProps = merge(mergedProps, styleProps);
      }
    }

    mergedProps.compose = fn => fn(mergedProps as any);

    return React.createElement(
      BlockContext.Provider,
      { value: mergedProps },
      React.createElement(Base, mergedProps)
    );
  }

  WithBlockConfig.displayName = `withBlockConfig(${Base.displayName})`;

  return WithBlockConfig;
}
