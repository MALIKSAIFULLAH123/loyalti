/**
 * @type: popover
 * name: user.popover.UserProfilePopup
 * path: /user/:id
 */
import { fetchDetail, useGlobal } from '@metafox/framework';
import React, { useEffect } from 'react';
import { actionCreators, connectItemView } from '../../hocs/connectUserItem';
import ItemView from './ProfilePopup';

const ConnectedView = connectItemView(ItemView, actionCreators);

const Popup = ({ id, ...rest }) => {
  const { dispatch, useGetItem } = useGlobal();
  const [loaded, setLoad] = React.useState(false);
  const identity = `user.entities.user.${id}`;
  const item = useGetItem(identity);

  useEffect(() => {
    setLoad(rest?.open && item?._loadedDetail);

    if (!rest?.open) return;

    if (!item?._loadedDetail) {
      dispatch(fetchDetail('/user/:id', { id }, () => setLoad(true)));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rest?.open, item?._loadedDetail, dispatch, id]);

  if (!loaded) return null;

  return <ConnectedView identity={identity} loaded={loaded} {...rest} />;
};

export default Popup;
