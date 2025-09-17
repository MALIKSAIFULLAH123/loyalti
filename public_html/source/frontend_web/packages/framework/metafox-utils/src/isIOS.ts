import { detect } from 'detect-browser';

const { name: browserName } = detect() || {};
const iOS = ['ios', 'crios'].includes(browserName);
export default iOS;
