/**
 * @type: ui
 * name: statusComposer.plugin.mention
 * lazy: false
 */
import MentionsPlugin from './MentionsPlugin';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { compactData, getImageSrc } from '@metafox/utils';
import React from 'react';
import MentionSuggestionEntry from './MentionSuggestionEntry';
import { debounce } from 'lodash';

export default function MentionPlugin(plugins) {
  plugins.push(AsMention);
}

function AsMention(props) {
  return <Suggestion {...props} As={MentionsPlugin} />;
}

const MAX_DISPLAY = 5;

const APP_FRIEND = 'friend';

function Suggestion({ As, parentUser, configMention, userId }) {
  const { apiClient } = useGlobal();
  const configDefault = useResourceAction(
    APP_FRIEND,
    APP_FRIEND,
    'getForMention'
  );
  const config = configMention || configDefault;

  const ownerId = parentUser?.id;
  const onSearchChange = React.useCallback(
    ({ value }: { value: string }, cb) => {
      if (!config?.apiUrl || !config?.apiMethod || !config?.apiParams) return;

      apiClient
        .request({
          method: config.apiMethod,
          url: config.apiUrl,
          params: compactData(config.apiParams, {
            q: value || undefined,
            owner_id: ownerId,
            user_id: userId,
            limit: 10
          })
        })
        .then(res => (res.data?.data?.length ? res.data?.data : []))
        .then(items => {
          return items.map((item, index) => {
            if (index >= MAX_DISPLAY) return false;

            return {
              avatar: getImageSrc(item.avatar),
              name: item.full_name,
              link: `@mention/${item.resource_name}/${item.id}`,
              moduleName: item?.module_name,
              statistic: item?.statistic,
              type: item?.type
            };
          });
        })
        .then(cb);
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [config]
  );

  // eslint-disable-next-line react-hooks/exhaustive-deps
  const debounceSearch = React.useCallback(debounce(onSearchChange, 500), []);

  return (
    <As
      onSearchChange={debounceSearch}
      entryComponent={MentionSuggestionEntry}
    />
  );
}
