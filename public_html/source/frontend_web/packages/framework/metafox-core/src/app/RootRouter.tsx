/**
 * @type: service
 * name: RootRouter
 */
import React from 'react';
import { BrowserRouter, MemoryRouter } from 'react-router-dom';

export default function RootRouter({ children, test, routerProps }) {
  const Router: any = test ? MemoryRouter : BrowserRouter;

  const basename = '/admincp';

  return (
    <Router basename={basename} {...routerProps}>
      {children}
    </Router>
  );
}
