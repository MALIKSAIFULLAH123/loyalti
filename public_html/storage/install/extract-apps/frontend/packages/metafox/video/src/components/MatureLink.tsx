import * as React from 'react';
import { useGlobal, LinkProps, Link } from '@metafox/framework';
import { ImageMatureState } from '@metafox/ui';

const MatureLink = (props: LinkProps) => {
  const { identity, ...rest } = props;
  const { dispatch, useGetItem, dialogBackend, i18n } = useGlobal();

  const item = useGetItem(identity);
  const { mature: matureDefault, _mature, extra, mature_config } = item || {};

  const mature = extra?.can_view_mature ? 0 : _mature ?? matureDefault ?? 0;

  const handleConfirmWarningMature = React.useCallback(
    async e => {
      e.preventDefault();
      e.stopPropagation();
      const ok = await dialogBackend.confirm({
        message: i18n.formatMessage({
          id: mature_config?.message ?? 'video_mature_warning_desc'
        }),
        title: i18n.formatMessage({
          id: mature_config?.title ?? 'video_mature_warning_title'
        })
      });

      if (ok) {
        if (identity) {
          dispatch({
            type: 'editItemLocal',
            payload: { identity, data: { _mature: 0 } }
          });
        }
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [mature_config]
  );

  if (mature && mature === ImageMatureState.Warning) {
    return (
      <Link
        {...rest}
        identityTracking={undefined}
        onClick={handleConfirmWarningMature}
      />
    );
  }

  return <Link {...rest} />;
};

export default MatureLink;
