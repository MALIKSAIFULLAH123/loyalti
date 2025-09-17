import { Manager } from '@metafox/framework';
import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import RestClient from './RestClient';

const MockAxios = new MockAdapter(axios, { delayResponse: 0 });

describe('RestClient', () => {
  const client = new RestClient({});
  const cookieGet = jest.fn().mockImplementation(() => '[access_token]');
  const manager = Manager.factory({}).use({
    cookieBackend: { get: cookieGet },
    apiClient: RestClient
  });

  it('+constructor', () => {
    expect(client).toBeDefined();
  });

  it('+manager', async () => {
    expect(manager.apiClient).toBeDefined();
  });

  it('+get 200', async () => {
    MockAxios.onGet('/users').replyOnce(200, {
      data: { user: { id: 1 } }
    });

    const res = await manager.apiClient.get('/users');

    expect(res.status).toEqual(200);
    expect(res.data?.data?.user?.id).toEqual(1);
  });

  it('+get 500 service unavailable', async () => {
    MockAxios.onGet('/user/1').reply(500, {
      data: { error_description: 'service unavailable' }
    });

    const onError = jest.fn();

    // try with catch on error
    await manager.apiClient.get('/user/1').catch(onError);

    expect(onError).toBeCalledTimes(1);

    // try with catch
  });
});
