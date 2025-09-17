import { GlobalProvider, Manager } from '@metafox/framework';
import { render, waitFor } from '@testing-library/react';
import * as React from 'react';
import createBlock from './createBlock';
import { IntlProvider } from '@metafox/intl';

describe('createBlock', () => {
  const Block = () => {
    return <div data-testid="myComponent" />;
  };

  Block.displayName = 'Block';

  const MyEmptyPrivacyPage = ({ title, description }) => {
    return (
      <div data-testid="MyEmptyPrivacyPage">
        {title}
        {description}
      </div>
    );
  };

  const ConnectedBlock = createBlock({
    name: 'ConnectedBlock',
    extendBlock: 'myBlock'
  });

  const views = {
    myBlock: Block,
    myEmptyPrivacyPage: MyEmptyPrivacyPage
  };
  const useLoggedIn = jest.fn().mockImplementation(() => false);
  const usePageParams = jest
    .fn()
    .mockImplementation(() => ({ identity: 'user.entities.user.1' }));
  const useGetItem = jest.fn().mockImplementation(() => ({
    name: 'admin'
  }));
  const getJsx = jest.fn().mockImplementation((name: string) => views[name]);

  const usePreference = jest
    .fn()
    .mockImplementation(() => ({ themeId: 'transparent' }));

  const manager = Manager.factory({
    views,
    messages: {
      content_private: '[content_private]',
      content_private_description: '[content_private_description]'
    }
  }).use({
    useGetItem,
    jsxBackend: { get: getJsx },
    usePreference,
    useLoggedIn,
    useFetchDetail: jest.fn().mockImplementation(() => [{}, false, false]),
    layoutBackend: { mergeBlockLayout: props => props },
    usePageParams
  });

  it('without authRequired', async () => {
    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <ConnectedBlock />
      </GlobalProvider>
    );

    expect(getJsx).toHaveBeenCalledTimes(1);
    expect(getJsx).toHaveBeenCalledWith('myBlock');
    expect(usePreference).toHaveBeenCalledTimes(1);
    expect(useLoggedIn).toHaveBeenCalledTimes(1);

    await waitFor(() => {
      expect(queryByTestId('myComponent')).toBeInTheDocument();
    });
  });

  /**
   * authRequired block return null with createBlock
   */
  it('with authRequired', async () => {
    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <ConnectedBlock
          authRequired
          blockLayout="[sample block layout]"
          gridLayout="[sample grid layout]"
        />
      </GlobalProvider>
    );

    expect(usePreference).toBeCalledTimes(1);

    await waitFor(() => {
      expect(queryByTestId('myComponent')).toBeNull();
    });
  });

  it('==========with authRequired + loggedIn', async () => {
    const useLoggedIn = jest.fn().mockImplementation(() => true);

    manager.use({ useLoggedIn });
    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <ConnectedBlock
          authRequired
          blockLayout="[sample block layout]"
          gridLayout="[sample grid layout]"
        />
      </GlobalProvider>
    );

    expect(useLoggedIn).toBeCalledTimes(1);
    expect(usePreference).toBeCalledTimes(1);

    await waitFor(() => {
      expect(queryByTestId('myComponent')).toBeInTheDocument();
    });
  });

  it('with showWhen correct', async () => {
    const useGetItem = jest.fn().mockImplementation(() => ({
      profile_menu_settings: { video: true }
    }));

    manager.use({ useGetItem });

    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <ConnectedBlock
          showWhen={['truthy', 'profile.profile_menu_settings.video']}
        />
      </GlobalProvider>
    );

    await waitFor(() => {
      expect(queryByTestId('myComponent')).toBeInTheDocument();
    });
  });

  it('==========with showWhen incorrect without privacyEmptyPage', async () => {
    const useGetItem = jest.fn().mockImplementation(() => ({
      profile_menu_settings: { video: true }
    }));

    manager.use({ useGetItem });

    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ConnectedBlock
            showWhen={['falsy', 'profile.profile_menu_settings.video']}
          />
        </IntlProvider>
      </GlobalProvider>
    );

    await waitFor(() => {
      expect(queryByTestId('myComponent')).toBeNull();
      expect(queryByTestId('MyEmptyPrivacyPage')).toBeNull();
    });
  });
  it('==========with showWhen incorrect with privacyEmptyPage', async () => {
    const useGetItem = jest.fn().mockImplementation(() => ({
      profile_menu_settings: { video: true }
    }));

    manager.use({ useGetItem });

    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <IntlProvider>
          <ConnectedBlock
            showWhen={['falsy', 'profile.profile_menu_settings.video']}
            privacyEmptyPage="myEmptyPrivacyPage"
          />
        </IntlProvider>
      </GlobalProvider>
    );

    await waitFor(() => {
      expect(queryByTestId('myComponent')).toBeNull();
      expect(queryByTestId('MyEmptyPrivacyPage')).toBeInTheDocument();
      expect(queryByTestId('MyEmptyPrivacyPage')).toHaveTextContent(
        'content_private'
      );
      expect(queryByTestId('MyEmptyPrivacyPage')).toHaveTextContent(
        'content_private_description'
      );
    });
  });

  it('========== with extendBlock with Component', () => {
    const ConnectedBlock = createBlock({
      extendBlock: Block
    });

    const { queryByTestId } = render(
      <GlobalProvider value={manager}>
        <ConnectedBlock />
      </GlobalProvider>
    );

    expect(queryByTestId('myComponent')).toBeInTheDocument();
  });
});
