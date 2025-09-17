import { GlobalState, ItemViewBaseProps, useGlobal } from '@metafox/framework';
import { ItemUserShape } from '@metafox/ui';
import { isEmpty } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';
import {
  getItemSelector,
  getProfileActionMenuSelector,
  getProfileMenuSelector,
  getUserSelector
} from '../selectors';

const handleMenuProfile = (menuItem: any, menuSelector: any) => {
  if (isEmpty(menuItem)) return menuSelector;

  try {
    let menu = menuSelector.items;

    const menuItemActive = [];

    Object.keys(menuItem).forEach(key => {
      if (menuItem[key]) {
        menuItemActive.push(key);
      }
    });

    // sort menu from profile_menu_settings
    if (!isEmpty(menuItemActive)) {
      menu = menuSelector.items
        .filter(item => menuItemActive.includes(item.name))
        .sort(
          (prev, next) =>
            menuItemActive.indexOf(prev.name) -
            menuItemActive.indexOf(next.name)
        );
    }

    return { ...menuSelector, items: menu };
  } catch (error) {
    return menuSelector;
  }
};

export default function connectProfileView(
  BaseView: ItemViewBaseProps,
  actionCreators: any
) {
  const Enhancer = (props: any) => {
    const { useActionControl } = useGlobal();
    const { identity } = props;

    const item = useSelector<GlobalState, any>(state =>
      getItemSelector(state, identity)
    );

    let profileMenu = useSelector<GlobalState>(state =>
      getProfileMenuSelector(
        state,
        item?.module_name || item?.resource_name,
        item?.resource_name
      )
    );

    profileMenu = handleMenuProfile(item?.profile_menu_settings, profileMenu);

    const profileActionMenu = useSelector<GlobalState>(state =>
      getProfileActionMenuSelector(
        state,
        item?.module_name || item?.resource_name,
        item?.resource_name
      )
    );

    const user = useSelector<GlobalState>(state =>
      getUserSelector(state, item?.user)
    ) as ItemUserShape;

    const [handleAction, state, setState, actions] = useActionControl<
      unknown,
      unknown
    >(identity, {}, actionCreators);

    return (
      <BaseView
        {...props}
        identity={identity}
        item={item}
        user={user}
        profileMenu={profileMenu}
        profileActionMenu={profileActionMenu}
        state={state}
        actions={actions}
        setState={setState}
        handleAction={handleAction}
      />
    );
  };

  Enhancer.LoadingSkeleton = BaseView.LoadingSkeleton;
  Enhancer.displayName = `Connected_${BaseView.displayName}`;

  return Enhancer;
}
