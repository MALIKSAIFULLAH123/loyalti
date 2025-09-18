/**
 * @type: block
 * name: reaction.block.PeopleReactions
 * title: People Reactions
 * keywords: reaction
 * description: Display people eeactions
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'reaction'
  },
  defaults: {
    itemView: 'reaction.itemView.reactedUser',
    blockLayout: 'Block Full Main',
    gridLayout: 'Friend - Small List',
    itemLayout: 'Friend - Small List'
  }
});
