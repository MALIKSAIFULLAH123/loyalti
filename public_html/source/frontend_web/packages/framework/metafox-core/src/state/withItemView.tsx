import { useActionControl } from '@metafox/framework';
import React from 'react';

export default function withItemView(getState = {}, actionCreators) {
  return (BaseView: React.FC<any>) => {
    const WithItemView = (props: any) => {
      const initial = React.useMemo(() => {
        return 'function' === typeof getState ? getState(props) : getState;
      }, [props]);

      const [handleAction, state, setState, actions] = useActionControl<
        unknown,
        unknown
      >(props.identity, initial, actionCreators);

      return (
        <BaseView
          {...props}
          state={state}
          actions={actions}
          setState={setState}
          handleAction={handleAction}
        />
      );
    };

    return WithItemView;
  };
}
