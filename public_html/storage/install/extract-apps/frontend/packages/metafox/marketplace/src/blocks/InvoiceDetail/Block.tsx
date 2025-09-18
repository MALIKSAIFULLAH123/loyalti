/**
 * @type: block
 * name: marketplace_invoice.block.detailView
 * title: Marketplace Detail
 * keywords: marketplace
 * description: Display marketplace detail
 */

import { connectSubject, createBlock } from '@metafox/framework';
import { connectItemView, actionCreators } from '../../hocs/connectInvoice';
import Base, { Props } from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators, {}));

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    blockProps: {
      titleComponent: 'h2',
      titleVariant: 'subtitle1',
      titleColor: 'textPrimary',
      noFooter: true,
      noHeader: true,
      blockStyle: {
        pl: 2,
        pt: 3,
        pr: 2,
        pb: 3,
        mt: 0,
        mb: 0,
        sx: {
          bgcolor: 'background.paper',
          borderRadius: 0
        }
      }
    }
  }
});
