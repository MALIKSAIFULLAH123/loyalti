import { GlobalState } from '@metafox/framework';
import { get } from 'lodash';
import { connect } from 'react-redux';
import Base from '../components/ReactionIcon';

const mapStateToProps = (state: GlobalState, props: any) =>
  get(state, props.identity) || {};

export default connect(mapStateToProps)(Base);
