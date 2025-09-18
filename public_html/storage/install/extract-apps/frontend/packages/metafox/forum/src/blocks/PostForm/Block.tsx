/**
 * @type: block
 * name: forum_post.block.addForm
 * title: Add New Forum
 * keywords: forum
 * description: Group Setting Info
 * thumbnail:
 * experiment: true
 */
import {
  connect,
  connectSubject,
  createBlock,
  GlobalState
} from '@metafox/framework';
import Base from './Base';

const Enhancer = connect((state: GlobalState) => ({
  dataSource: state._actions.forum.forum_post.addItem
}))(connectSubject(Base));

export default createBlock<any>({
  extendBlock: Enhancer,
  defaults: {
    blockLayout: 'Blocker'
  }
});
