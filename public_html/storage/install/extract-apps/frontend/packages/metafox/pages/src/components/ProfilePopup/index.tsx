/**
 * @type: popover
 * name: page.popover.pageProfilePopup
 * path: /page/:id
 */
import { fetchDetail, useGlobal } from '@metafox/framework';
import React, { useEffect } from 'react';
import { actionCreators, connectItemView } from '../../hocs/connectPageItem';
import ItemView from './ProfilePopup';

const ConnectedView = connectItemView(ItemView, actionCreators);

const Popup = ({ id, ...rest }) => {
  const { dispatch, useGetItem } = useGlobal();
  const [loaded, setLoad] = React.useState(false);
  const identity = `page.entities.page.${id}`;
  const item = useGetItem(identity);

  useEffect(() => {
    setLoad(rest?.open && item?._loadedDetail);

    if (!rest?.open) return;

    if (!item?._loadedDetail) {
      dispatch(fetchDetail('/page/:id', { id }, () => setLoad(true)));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rest?.open, item?._loadedDetail, dispatch, id]);

  if (!loaded) return null;

  return <ConnectedView identity={identity} loaded={loaded} {...rest} />;
};

export default Popup;
