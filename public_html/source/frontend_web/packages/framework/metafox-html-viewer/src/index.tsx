import loadable from '@loadable/component';

export {
  convertNodeToElement,
  htmlparser2,
  processNodes
} from 'react-html-parser';

const HtmlViewer = loadable(
  () =>
    import(
      /* webpackChunkName: "first" */
      './HtmlViewer'
    )
);

export default HtmlViewer;
