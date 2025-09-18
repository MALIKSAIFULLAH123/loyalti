/**
 * @type: ui
 * name: advertise.ui.inViewImpression
 * chunkName: advertise
 */

import { useGlobal } from '@metafox/framework';
import { isEmpty } from 'lodash';
import React from 'react';
import { useInView } from 'react-intersection-observer';

const InViewImpression = ({ item, identity }: any) => {
  const { dispatch, getSetting } = useGlobal();

  const timeout = React.useRef<any>();
  const end = React.useRef(false);
  // get second delay
  const delay_time_to_count_sponsor_view = getSetting(
    'advertise.delay_time_to_count_sponsor_view',
    0
  );
  const time = delay_time_to_count_sponsor_view * 1000;

  const [refScrollInView, inView] = useInView({
    threshold: 0
  });

  React.useEffect(() => {
    if (end?.current || item?._sponsorship_viewed || isEmpty(item)) return;

    if (inView) {
      timeout.current = setTimeout(() => {
        end.current = true;
        dispatch({ type: 'advertise/updateImpression', payload: item });
      }, time);
    } else {
      if (timeout.current) {
        clearTimeout(timeout.current);
      }
    }

    // clean
    return () => {
      if (timeout.current) {
        clearTimeout(timeout.current);
      }
    };

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [inView]);

  return <span ref={refScrollInView} />;
};

export default InViewImpression;
