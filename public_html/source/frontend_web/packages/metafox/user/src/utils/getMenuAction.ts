import { getResourceMenuSelector, GlobalState } from '@metafox/framework';
import { APP_USER } from '@metafox/user/constant';
import { useSelector } from 'react-redux';

export const getMenuAction = (menuName: string) => {
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const { items: menus } = useSelector(
    (state: GlobalState) =>
      getResourceMenuSelector(state, APP_USER, APP_USER, menuName) || {}
  );

  return menus;
};
