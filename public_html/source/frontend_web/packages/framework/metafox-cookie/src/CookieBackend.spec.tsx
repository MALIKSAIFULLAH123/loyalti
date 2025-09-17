import CookieBackend from '@metafox/cookie';
import { Manager } from '@metafox/framework';
import JsCookie from 'js-cookie';

describe('@metafox/cookie/CookieBackend', () => {
  it('constructor', () => {
    const backend = new CookieBackend({});
    expect(backend).toBeDefined();
  });
  it('+manager', () => {
    const manager = Manager.factory({
      root: {
        cookie: {
          prefix: 'test-site_'
        }
      }
    });

    manager.use({ cookieBackend: CookieBackend });

    expect(manager.cookieBackend).toBeDefined();
  });

  it('+get, + set', () => {
    const manager = Manager.factory({
      root: {
        cookie: {
          prefix: 'test-site_'
        }
      }
    });

    manager.use({ cookieBackend: CookieBackend });

    expect(manager.cookieBackend).toBeDefined();

    const backend: CookieBackend = manager.cookieBackend;

    const mockGet = jest.fn();
    const mockGetJSON = jest.fn();
    const mockSet = jest.fn();
    const mockRemove = jest.fn();

    JsCookie.get = mockGet;
    JsCookie.getJSON = mockGetJSON;
    JsCookie.set = mockSet;
    JsCookie.remove = mockRemove;

    mockGet.mockImplementationOnce(() => '');

    expect(backend.get('key_1')).toEqual('');

    expect(mockGet).toBeCalledTimes(1);

    JsCookie.get = mockGet.mockImplementationOnce(() => '3');

    expect(backend.getInt('key_1')).toEqual(3);

    expect(mockGet).toBeCalledTimes(2);

    expect(backend.set('key_1', 'value_1', {})).toBeUndefined();

    expect(mockSet).toBeCalledTimes(1);

    expect(backend.remove('key_1')).toBeUndefined();

    expect(mockRemove).toBeCalledTimes(1);

    mockGetJSON.mockImplementationOnce(() => ({ x: 2 }));

    expect(backend.getJSON<{ x: number }>('key_1').x).toEqual(2);

    expect(mockGetJSON).toBeCalledTimes(1);

    expect(backend.toKey('key_1')).toEqual('test-site_key_1');
    expect(mockGetJSON).toBeCalledWith('test-site_key_1');

    expect(backend.set('key_1', 'value_1')).toBeUndefined();
  });
});
