/**
 * @type: service
 * name: ItemDetailInteraction
 */

import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { connect } from 'react-redux';
import BaseView from '../components/ItemDetailInteraction/ItemDetailInteraction';
import withCommentItem from './withCommentItem';

const mapStateToProps = (state: GlobalState, { identity }: any) =>
  get(state, identity) || {};

export default connect(mapStateToProps)(withCommentItem(BaseView));
