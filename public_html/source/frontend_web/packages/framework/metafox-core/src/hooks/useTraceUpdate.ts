/* eslint-disable no-console */
import { isObject } from 'lodash';
import * as React from 'react';

export default function useTraceUpdate(
  label: string,
  props: any,
  when?: () => boolean
) {
  const prev = React.useRef(props);

  React.useEffect(() => {
    const changedProps = isObject(props)
      ? Object.entries(props).reduce((ps, [k, v]) => {
          if (!prev.current) {
            ps[k] = [undefined, v];
          } else if (prev.current[k] !== v) {
            ps[k] = [prev.current[k], v];
          }

          return ps;
        }, {})
      : [];

    if (
      (when === undefined || when()) &&
      isObject(changedProps) &&
      0 < Object.keys(changedProps).length
    ) {
      console.info(`${label} updated:`, changedProps);
    }

    prev.current = props;
  });
}
