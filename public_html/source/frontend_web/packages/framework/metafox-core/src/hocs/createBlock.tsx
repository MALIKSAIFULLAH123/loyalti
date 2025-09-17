import { useGlobal } from '@metafox/framework';
import { BlockContext } from '@metafox/layout';
import { filterShowWhen, mergeObjectProps, when } from '@metafox/utils';
import { camelCase, isString, merge } from 'lodash';
import React from 'react';
import { BlockViewProps, CreateBlockParams } from '../types';

export default function createBlock<T extends BlockViewProps = BlockViewProps>({
  name,
  extendBlock,
  overrides = {},
  defaults = {},
  custom,
  customValidation
}: CreateBlockParams<T>) {
  const ConnectedBlock = (newProps: T) => {
    const {
      jsxBackend,
      usePreference,
      useLoggedIn,
      usePageParams,
      i18n,
      useGetItem,
      getSetting,
      layoutBackend,
      getAcl,
      useSession
    } = useGlobal();
    const { themeId } = usePreference();
    const loggedIn = useLoggedIn();
    const pageParams = usePageParams();
    const setting = getSetting();
    const acl = getAcl() as Object;
    const session = useSession();

    const { identity } = pageParams;
    const profile = useGetItem(identity);

    const BaseBlock = isString(extendBlock)
      ? jsxBackend.get(extendBlock)
      : extendBlock;

    let mergedProps = mergeObjectProps<T>(
      { themeId },
      {},
      defaults,
      newProps,
      undefined,
      overrides
    ) as T;

    if (!mergedProps.testid) {
      let testId = isString(extendBlock)
        ? extendBlock
        : extendBlock.displayName;

      if (testId === 'core.block.listview') {
        testId = `listOf ${mergedProps.title ?? ''}`;
      }

      if (!testId) {
        testId = name;
      }

      if (!testId) {
        testId = mergedProps.title;
      }

      mergedProps.testid = camelCase(`block ${testId}`);
    }

    const styled = layoutBackend.normalizeDisplayingPresets(mergedProps);

    mergedProps = merge(mergedProps, styled);

    // authRequired
    if (mergedProps.authRequired && !loggedIn) {
      return null;
    }

    const privacyWhen = mergedProps?.privacyWhen;

    const show = filterShowWhen([mergedProps], {
      profile,
      pageParams,
      setting,
      acl,
      session
    }).length;

    // condition show Privacy block base on privacyWhen if key exist, else base on showWhen
    const showPrivacy = privacyWhen
      ? when(
          {
            profile,
            pageParams,
            setting,
            acl
          },
          privacyWhen
        )
      : !show;

    if (showPrivacy && mergedProps.privacyEmptyPage) {
      const propsPrivacy = mergedProps.privacyEmptyPageProps || {};

      return jsxBackend.render({
        component: mergedProps.privacyEmptyPage,
        props: {
          title: i18n.formatMessage({ id: 'content_private' }),
          description: i18n.formatMessage({
            id: 'content_private_description'
          }),
          ...propsPrivacy
        }
      });
    }

    if (!show) {
      return null;
    }

    mergedProps.compose = fn => fn(mergedProps as any);

    return React.createElement(
      BlockContext.Provider,
      { value: mergedProps },
      React.createElement(BaseBlock, mergedProps as any)
    );
  };

  ConnectedBlock.editorConfig = {
    extendBlock,
    overrides,
    defaults,
    custom,
    customValidation
  };

  const baseName = isString(extendBlock)
    ? extendBlock
    : extendBlock.displayName;

  ConnectedBlock.displayName = `createBlock(${name ? name : baseName})`;

  return ConnectedBlock;
}
