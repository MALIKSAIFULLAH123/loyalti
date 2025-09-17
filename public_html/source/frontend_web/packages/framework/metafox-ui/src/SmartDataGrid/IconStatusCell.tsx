/**
 * @type: ui
 * name: dataGrid.cell.IconStatusCell
 */
import React from 'react';
import { Box, styled, Tooltip } from '@mui/material';
import { get } from 'lodash';
import { LineIcon } from '@metafox/ui';
import { keyframes } from '@emotion/react';
import { useGlobal } from '@metafox/framework';
import useDataGridContext from './useDataGridContext';

const spinnerKeyFrame = keyframes`
    0% {transform: rotate(0deg);}
    100% {transform: rotate(360deg);}
`;

const name = 'IconCell';
const IconWrapper = styled('div', {
  name,
  slot: 'uiChatMsgSet',
  shouldForwardProp: prop => prop !== 'isOwner' && prop !== 'spinner'
})<{ spinner?: boolean }>(({ theme, spinner }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  ...(spinner && { animation: `${spinnerKeyFrame} 1s linear infinite` })
}));

export default function IconCell({
  id,
  row,
  colDef: { field, iconConfig, iconDefault, pollingConfig }
}) {
  const content = get(row, field, null);
  const { handleReloadRow } = useDataGridContext();
  const key = content?.toString();
  const { apiClient, handleActionError } = useGlobal();
  const pollingRef = React.useRef(false);
  const timeoutRef = React.useRef<any>();
  const dataSource = key && pollingConfig ? pollingConfig[key]?.dataSource : '';

  const cancelController = new AbortController();

  React.useEffect(() => {
    if (dataSource && !pollingRef.current) {
      pollingRef.current = true;
      fetchOrRetry();
    }

    return () => {
      clearTimeout(timeoutRef.current);
      cancelController.abort();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key]);

  const updateRow = React.useCallback((res = {}) => {
    handleReloadRow('row/updateData', { id, ...res });
  }, []);

  const fetchOrRetry = React.useCallback(() => {
    apiClient
      .request({
        url: dataSource.apiUrl,
        method: dataSource?.apiMethod ?? 'GET',
        signal: cancelController.signal
      })
      .then(res => {
        if (get(res, 'data.data.retry')) {
          timeoutRef.current = setTimeout(() => fetchOrRetry(), 5e3);
        } else {
          const payload = get(res, 'data.data') || {};
          updateRow(payload);
        }
      })
      .catch(err => {
        if (err.code === 'ERR_CANCELED') {
          // cancel requesr
          return;
        }

        updateRow();

        handleActionError(err);
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cancelController]);

  if (key === '') return null;

  const {
    icon,
    color,
    spinner,
    label,
    hidden = false,
    asText
  } = iconConfig[key] || iconDefault || {};
  const sx = get(row, 'sx');
  const sxProps = get(sx, field);

  if (hidden) return null;

  if (!icon)
    return (
      <Box component={'span'} sx={sxProps}>
        {key}
      </Box>
    );

  return (
    <Box component={'span'} color={color} sx={sxProps}>
      <Tooltip title={label || key}>
        {asText ? (
          <Box component={'span'}>{asText}</Box>
        ) : (
          <IconWrapper spinner={spinner}>
            <LineIcon sx={{ fontSize: '18px' }} icon={icon} />
          </IconWrapper>
        )}
      </Tooltip>
    </Box>
  );
}
