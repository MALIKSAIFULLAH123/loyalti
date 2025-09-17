import { GlobalProvider, Manager } from '@metafox/framework';
import { IntlProvider } from '@metafox/intl';
import { render } from '@testing-library/react';
import * as React from 'react';
import DraftFlag from './DraftFlag';

describe('DraftFlag', () => {
  const props = {
    variant: 'h3',
    component: 'span',
    sx: {
      verticalAlign: 'middle',
      fontWeight: 'normal'
    }
  };

  it('render', async () => {
    const usePreference = jest.fn().mockImplementation(() => ({}));
    const getConfig = jest.fn().mockImplementation(() => {});
    const manager = Manager.factory({}).use({ usePreference, getConfig });
    const { container } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <DraftFlag value {...props} />
        </IntlProvider>
      </GlobalProvider>
    );

    expect(container).toMatchSnapshot();
  });
  it('render null', async () => {
    const { container } = render(<DraftFlag value={false} {...props} />);

    expect(container).toMatchSnapshot();
  });
});
