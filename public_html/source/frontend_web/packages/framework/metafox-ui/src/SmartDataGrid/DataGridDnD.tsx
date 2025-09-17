import React from 'react';
import DataGridRowDnD from './DataGridRowDnD';
import { useGlobal } from '@metafox/framework';
import { isEqual } from 'lodash';
import { CircularProgress, Box } from '@mui/material';

export interface ContainerState {
  rows: Record<string, any>[];
  component: any;
  meta: Record<string, any>;
}

const Container = ({ rows, component, meta }: ContainerState) => {
  const { dispatch } = useGlobal();
  const [orderIds, setOrderIds] = React.useState(
    rows.map((row: Record<string, any>) => row.id)
  );
  const [updating, setUpdating] = React.useState(false);

  React.useEffect(() => {
    setOrderIds(rows.map((row: Record<string, any>) => row.id));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rows.length]);

  const moveRow = (dragIndex: number, hoverIndex: number) => {
    setOrderIds(prevOrderIds => {
      const clone = [...prevOrderIds];
      clone.splice(hoverIndex, 0, clone.splice(dragIndex, 1)[0]);

      return clone;
    });
  };

  const handleDrop = () => {
    const prevIds = rows.map((row: Record<string, any>) => row.id);

    if (!isEqual(prevIds, orderIds)) {
      setUpdating(true);
      dispatch({
        type: 'row/sortable',
        payload: { order_ids: orderIds },
        meta: { ...meta, onSuccess: onUpdatedApi }
      });
    }
  };

  const onUpdatedApi = () => {
    setUpdating(false);
  };

  const renderRow = (id: number, index: number) => {
    const row = rows.find(x => x.id === id);

    if (!row) return null;

    return updating ? (
      <Box key={row.id}>{component({ row })}</Box>
    ) : (
      <DataGridRowDnD
        key={row.id}
        index={index}
        id={row.id}
        moveRow={moveRow}
        handleDrop={handleDrop}
      >
        {component({ row })}
      </DataGridRowDnD>
    );
  };

  return (
    <Box sx={{ position: 'relative', minHeight: '80px' }}>
      <Box sx={{ opacity: updating ? 0.7 : 1 }}>
        {orderIds.map((orderId, i) => renderRow(orderId, i))}
      </Box>
      {updating ? (
        <Box
          sx={{
            position: 'absolute',
            left: 0,
            right: 0,
            top: 0,
            bottom: 0,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: 'rgba(255,255,255,0.5)'
          }}
        >
          <CircularProgress />
        </Box>
      ) : null}
    </Box>
  );
};

export default Container;
