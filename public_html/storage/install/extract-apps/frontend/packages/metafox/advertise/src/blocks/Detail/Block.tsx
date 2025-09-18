/**
 * @type: block
 * name: advertise.block.detailAdvertise
 * title: Detail advertise
 * keywords: advertise
 * description: Display advertise detail
 * thumbnail:
 * experiment: true
 */

import {
  connectItemView,
  connectSubject,
  createBlock,
  connect,
  GlobalState
} from '@metafox/framework';
import Base, { Props } from './Base';

const BaseConnect = connect((state: GlobalState) => ({
  detailActionMenu:
    state._resourceMenus.advertise.advertise.detailActionMenu.items
}))(Base);

const Enhance = connectSubject(connectItemView(BaseConnect, () => {}, {}));

export default createBlock<Props>({
  extendBlock: Enhance
});
