/**
 * @type: service
 * name: ReactionActButton
 */

import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { connect } from 'react-redux';
import Button from '../components/ReactionActButton';

const mapStateToProps = (state: GlobalState, props: any) => ({
  reactedItem: get(state, props.reacted),
  unreactedItem: state.preaction.data.unreactedItem
});
export default connect(mapStateToProps)(Button);
