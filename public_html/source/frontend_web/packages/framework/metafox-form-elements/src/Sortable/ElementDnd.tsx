import { Element } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, CircularProgress, styled } from '@mui/material';
import { get, isEqual } from 'lodash';
import React from 'react';
import ElementRowDnD from './ElementRowDnD';

const ContainerStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(0, 1.5)
}));

const WrapperIcon = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  width: 32,
  minWidth: 32,
  padding: theme.spacing(1.25, 0.5),
  textAlign: 'left',
  justifyContent: 'left',
  overflow: 'hidden',

  marginBottom: theme.spacing(1)
}));

const SortIcon = ({ config }: any) => {
  const sx = get(config, 'sxSort');
  const icon = get(config, 'iconSort') || 'ico-arrows-move';

  return (
    <WrapperIcon sx={sx}>
      <LineIcon icon={icon} />
    </WrapperIcon>
  );
};

export default function ElementDnd({ elements, formik, orderAction }: any) {
  const { dispatch } = useGlobal();
  const [updating, setUpdating] = React.useState(false);
  const [elementsOrder, setElementsOrder] = React.useState(
    Object.keys(elements)
  );

  React.useEffect(() => {
    setElementsOrder(Object.keys(elements));
  }, [elements]);

  const moveRow = (dragIndex: number, hoverIndex: number) => {
    setElementsOrder(prevOrderIds => {
      const clone = [...prevOrderIds];
      clone.splice(hoverIndex, 0, clone.splice(dragIndex, 1)[0]);

      return clone;
    });
  };

  const handleDrop = () => {
    const prevIds = Object.keys(elements);

    if (!isEqual(prevIds, elementsOrder) && orderAction) {
      setUpdating(true);
      dispatch({
        type: orderAction,
        payload: { ids: elementsOrder },
        meta: { onSuccess: onUpdatedApi }
      });
    }
  };

  const onUpdatedApi = () => {
    setUpdating(false);
  };

  const renderRow = (key, index) => {
    const config = elements[key];

    if (!config || !key) return null;

    return updating ? (
      <ContainerStyled>
        <SortIcon config={config} />
        <Element formik={formik} key={key.toString()} config={config} />
      </ContainerStyled>
    ) : (
      <ElementRowDnD
        key={key}
        index={index}
        id={key}
        moveRow={moveRow}
        handleDrop={handleDrop}
      >
        <ContainerStyled>
          <SortIcon config={config} />
          <Element formik={formik} key={key.toString()} config={config} />
        </ContainerStyled>
      </ElementRowDnD>
    );
  };

  return (
    <Box>
      <Box sx={{ opacity: updating ? 0.7 : 1 }}>
        {elementsOrder.map((key, index) => renderRow(key, index))}
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
}
