/**
 * @type: block
 * name: forum_thread.block.threadView
 * title: Thread Detail
 * keywords: forum
 * description: Display thread detail
 */

import { connectSubject, createBlock } from '@metafox/framework';
import { actionCreators, connectItemView } from '../../hocs/connectForumThread';
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
        sx: {
          mb: 2,
          mt: 2
        }
      },
      contentStyle: {
        sx: {
          borderRadius: 1,
          bgcolor: 'background.paper',
          pt: 0,
          pb: 0
        }
      }
    }
  }
});
