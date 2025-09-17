import { RefOf } from '@metafox/framework';
import clsx from 'clsx';
import React from 'react';

interface Props {
  open: boolean;
  classes: any;
  px: number;
  py: number;
}

function TaggedBox(
  { classes, px, py, open }: Props,
  ref: RefOf<HTMLDivElement>
) {
  const style = React.useMemo(() => {
    return { left: `${px}%`, top: `${py}%` };
  }, [px, py]);

  return (
    <div
      onClick={e => e.stopPropagation()}
      className={clsx(classes.taggedBox, open ? '' : 'srOnly')}
      style={style}
      ref={ref}
    >
      <div className={classes.whoIsThis} />
    </div>
  );
}

export default React.forwardRef<HTMLDivElement, Props>(TaggedBox);
