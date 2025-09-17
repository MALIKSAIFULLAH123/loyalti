/**
 * @type: service
 * name: useContentParams
 */
import * as React from 'react';
import ContentParamsContext from './ContentParamsContext';

const useContentParams = () => React.useContext(ContentParamsContext) || {};

export default useContentParams;
