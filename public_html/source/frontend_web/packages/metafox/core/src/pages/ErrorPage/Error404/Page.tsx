/**
 * @type: route
 * name: core.error404
 * path: /page-not-found
 */
import { Page } from '@metafox/layout';
import * as React from 'react';

export default function ErrorPage(props) {
  return <Page {...props} pageName="core.error404" />;
}
