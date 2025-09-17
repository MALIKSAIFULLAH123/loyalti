import {
  getItemSelector,
  GlobalState,
  ItemViewBaseProps,
  useGlobal
} from '@metafox/framework';
import React from 'react';
import { useSelector } from 'react-redux';

export default function connectItemView<ItemShape = any>(
  BaseView: ItemViewBaseProps,
  actionCreators: any
) {
  const ConnectItemView = (props: any) => {
    const { useActionControl } = useGlobal();
    const { identity } = props;

    const item = useSelector<GlobalState>(state =>
      getItemSelector(state, identity)
    ) as ItemShape;

    const [handleAction, state, setState, actions] = useActionControl<
      unknown,
      unknown
    >(item?.item, {}, actionCreators);

    return (
      <BaseView
        {...props}
        identity={identity}
        item={item}
        state={state}
        actions={actions}
        setState={setState}
        handleAction={handleAction}
      />
    );
  };

  ConnectItemView.displayName = `ConnectItemView(${BaseView.displayName})`;

  return ConnectItemView;
}
