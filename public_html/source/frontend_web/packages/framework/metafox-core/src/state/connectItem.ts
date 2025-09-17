import { connect } from 'react-redux';
import mapStateToProps from './mapStateToProps';

const simpleConnector = connect(mapStateToProps);

export default function connectItem(
  BaseView: any,
  connectFunction = simpleConnector
) {
  return connectFunction(BaseView);
}
