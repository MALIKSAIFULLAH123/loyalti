import HtmlViewer from '@metafox/html-viewer';
import { render, waitFor } from '@testing-library/react';
import React from 'react';
import { MemoryRouter } from 'react-router';

/* eslint-disable no-multi-str */
const html =
  "Leading news sites in U.S. Europe Asia.\
   <a data-testid='myLink' href='/jorge21' target='_blank'>Jade McCullough V</a> \
  \nSearch and Browse archive.. See Early Warning. \n\nQuality sites in U.S. Europe, \
  Asia. See Renewal America, Renewal Europe. \nSearch and browse";

/* eslint-disable no-multi-str*/
describe('HtmlViewer', () => {
  it('+render', async () => {
    const { asFragment, queryByTestId } = render(
      <MemoryRouter>
        <HtmlViewer
          html={html}
          component={({ children }) => (
            <div data-testid="myComponent">{children}</div>
          )}
        />
      </MemoryRouter>
    );

    await waitFor(() => expect(queryByTestId('myComponent')));

    expect(asFragment()).toMatchSnapshot();
  });
});
