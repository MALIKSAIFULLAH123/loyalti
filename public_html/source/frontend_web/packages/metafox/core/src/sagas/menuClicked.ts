/**
 * @type: saga
 * name: menu.clicked
 */
import { getGlobalContext, LocalAction } from '@metafox/framework';
import { MenuItemShape } from '@metafox/ui';
import { takeEvery, put } from 'redux-saga/effects';
import { isExternalLink } from '@metafox/utils';

function* menuClicked({
  payload,
  meta
}: LocalAction<
  {
    closePopover?: () => void;
    closeMenu?: () => void;
    item: MenuItemShape;
    onClick: (evt: React.MouseEvent) => void;
    handleAction?: (type: string, value: any) => void;
  },
  {
    evt?: React.MouseEvent;
    location: string;
  }
>) {
  const { location, evt } = meta || {};
  const { onClick, handleAction, item, closePopover, closeMenu } = payload;

  const { navigate } = yield* getGlobalContext();

  // fix: chatplus app toolbar action menu work correct?
  if (evt) evt.stopPropagation();

  if (onClick) {
    onClick(evt);
  } else if (item?.onClick) {
    const { onClick } = item;
    onClick();
  } else if (handleAction && item?.value) {
    handleAction(item.value, { ...item.params, location });
  } else if (item?.value) {
    yield put({ type: item.value, payload: { ...item.params, location } });
  } else if (isExternalLink(item?.to)) {
    window.open(item.to, item.target);
  } else if (item?.to && item?.target) {
    if (item.to === '/admincp') {
      // fix issue admincp.
      window.open(process.env.MFOX_ADMINCP_URL, item.target);
    } else {
      window.open(item.to, item.target);
    }
  } else if (item.to) {
    navigate(item.to);
  }

  if (closePopover) {
    closePopover();
  }

  if (!item.preventClose && closeMenu) {
    closeMenu();
  }

  yield;
}

const sagas = [takeEvery('menu/clicked', menuClicked)];

export default sagas;
