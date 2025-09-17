import { Manager } from '@metafox/framework';
import RouteBackend from './RouteBackend';

describe('@metafox/path-resolver', () => {
  jest.setTimeout(1000);
  it('+constructor', async () => {
    const backend = new RouteBackend({
      cache: true,
      pageNotFound: '/not-found'
    });
    expect(backend).toBeDefined();
    expect(backend.cancel()).toBeUndefined();
  });

  it('+manager, cache = false', async () => {
    const manager = Manager.factory({
      routeBackend: {
        cache: false,
        pageNotFound: '/not-found',
        apiUrl: '/core/find-path'
      }
    });

    const mockGet = jest.fn();
    const onBefore = jest.fn();

    manager.use({
      apiClient: {
        get: mockGet
      }
    });

    expect(manager.routeBackend).toBeDefined();

    const backend = manager.routeBackend;

    mockGet.mockImplementationOnce(
      () =>
        new Promise(resolve => {
          resolve({ data: null });
        })
    );

    const result = await backend.findPath('/any', onBefore);

    expect(onBefore).toBeCalledTimes(1);

    expect(result.to).toEqual('/not-found');
    expect(result.from).toEqual('/any');

    mockGet.mockReset();

    mockGet.mockImplementationOnce(
      () =>
        new Promise(resolve => {
          resolve({ data: { data: { path: '/user/2' } } });
        })
    );

    const result2 = await backend.findPath('/profile-2', onBefore);

    expect(mockGet).toHaveBeenCalled();

    expect(onBefore).toBeCalledTimes(2);

    expect(result2.to).toEqual('/user/2');
    expect(result2.from).toEqual('/profile-2');

    mockGet.mockReset();
    mockGet.mockImplementationOnce(
      () =>
        new Promise(resolve => {
          resolve({ data: { data: { path: '/user/2' } } });
        })
    );

    const result3 = await backend.findPath('/profile-2', onBefore);

    expect(onBefore).toBeCalledTimes(3);

    expect(result3.to).toEqual('/user/2');
    expect(result3.from).toEqual('/profile-2');

    mockGet.mockReset();
    mockGet.mockImplementationOnce(
      () =>
        new Promise(resolve => {
          throw new Error('invalid url');
        })
    );

    const result4 = await backend.findPath('/profile-2');

    expect(onBefore).toBeCalledTimes(3);

    expect(result4.to).toEqual('/not-found');
    expect(result4.from).toEqual('/profile-2');
  });

  it('+manager, cache=true', async () => {
    const manager = Manager.factory({
      routeBackend: {
        cache: true,
        pageNotFound: '/not-found',
        apiUrl: '/core/find-path'
      }
    });

    const mockGet = jest.fn();
    const onBefore = jest.fn();

    manager.use({
      apiClient: {
        get: mockGet
      }
    });

    const backend = manager.routeBackend;

    mockGet.mockImplementationOnce(
      () =>
        new Promise(resolve => {
          resolve({ data: { data: { path: '/user/2' } } });
        })
    );

    const result2 = await backend.findPath('/profile-2', onBefore);

    expect(onBefore).toBeCalledTimes(1);

    expect(result2.to).toEqual('/user/2');
    expect(result2.from).toEqual('/profile-2');

    mockGet.mockReset();
    mockGet.mockImplementationOnce(
      () =>
        new Promise(resolve => {
          resolve({ data: { path: '/user/2' } });
        })
    );

    const result3 = await backend.findPath('/profile-2', onBefore);

    expect(onBefore).toBeCalledTimes(1);

    expect(result3.to).toEqual('/user/2');
    expect(result3.from).toEqual('/profile-2');

    const result4 = await backend.findPath('/profile-2', onBefore);

    expect(onBefore).toBeCalledTimes(1);

    expect(result4.to).toEqual('/user/2');
    expect(result4.from).toEqual('/profile-2');
  });
});
