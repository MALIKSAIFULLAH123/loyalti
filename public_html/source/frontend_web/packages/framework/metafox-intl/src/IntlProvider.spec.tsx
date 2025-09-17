import { GlobalProvider, Manager } from '@metafox/framework';
import { render, waitFor } from '@testing-library/react';
import React from 'react';
import IntlProvider from './IntlProvider';
import messages from './__mocks__/messages.json';

describe('IntlProvider', () => {
  const MockComponent = (): JSX.Element => {
    return <div data-testid="myComponent"></div>;
  };

  it('+IntlShape pass', async () => {
    const usePreference = jest.fn().mockImplementation(() => ({}));
    const useIntlMessages = jest.fn().mockImplementation(() => messages);

    const manager = Manager.factory({
      messages
    }).use({ usePreference, useIntlMessages });
    const { getByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <MockComponent />
        </IntlProvider>
      </GlobalProvider>
    );

    await waitFor(() => {
      expect(getByTestId('myComponent')).toBeInTheDocument();
    });

    const { i18n } = manager;

    expect(usePreference).toBeCalledTimes(1);
    expect(useIntlMessages).toBeCalledTimes(1);
    expect(i18n).toBeDefined();
    expect(i18n.formatMessage).toBeDefined();
    expect(i18n.$t).toBeDefined();
    expect(i18n.formatPlural).toBeDefined();
    expect(i18n.formatDate).toBeDefined();

    expect(i18n.formatMessage({ id: 'hello_world' })).toEqual(
      messages.hello_world
    );
    expect(
      i18n.formatMessage({ id: 'hello_user' }, { user: 'MetaFox' })
    ).toEqual('Hello MetaFox!');
    expect(i18n.formatMessage({ id: 'total_like' })).toEqual(
      messages.total_like
    );
    expect(i18n.formatMessage({ id: 'total_chip' })).toEqual(
      messages.total_chip
    );

    expect(i18n.formatMessage({ id: 'total_chip' }, { value: 2 })).toEqual(
      'two chips'
    );

    expect(i18n.formatMessage({ id: 'total_chip' }, { value: 3 })).toEqual(
      '3 chips'
    );

    expect(i18n.formatMessage({ id: 'test_with_link' })).toEqual(
      '<b>MetaFox<b> is the best social network platform'
    );
  });
});
