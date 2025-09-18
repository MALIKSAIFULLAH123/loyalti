import { cloneDeep, isEmpty, uniqBy } from 'lodash';
import { APP_REACTION, RESOURCE_REACTION } from '../constants';
import { ItemReactionInformationShape } from '@metafox/ui';

export const getIdentityFromId = (id: any) => {
  return `${APP_REACTION}.entities.${RESOURCE_REACTION}.${id}`;
};

export const getIdFormIdentity = (identity: string) => {
  if (isEmpty(identity)) return undefined;

  return identity?.split('.')[3];
};

export const addOrUpdateItem = (data: any[], idNew: number) => {
  const arr: ItemReactionInformationShape[] = cloneDeep(data) || [];

  // eslint-disable-next-line eqeqeq
  const itemNew = arr.find(item => item.id == idNew);
  const indexTotal = arr.findIndex(item => item?.total_reaction === 1) || 0;

  if (!isEmpty(itemNew)) {
    itemNew.total_reaction += 1;
  } else {
    arr.splice(indexTotal, 0, { id: Number(idNew), total_reaction: 1 });
  }

  return uniqBy(arr, 'id');
};

export const moveOldOrPushItem = (
  data: any[],
  idNew: number,
  idOld: string
) => {
  if (isEmpty(data)) return data;

  let arr = cloneDeep(data) || [];
  // eslint-disable-next-line eqeqeq
  const itemNew = arr.find(item => item.id == idNew);
  // eslint-disable-next-line eqeqeq
  const indexOld = arr.findIndex(item => item.id == idOld);

  if (arr?.[indexOld]?.total_reaction === 1) {
    arr.splice(indexOld, 1);
  }

  if (itemNew?.total_reaction) {
    itemNew.total_reaction += 1;
  } else {
    arr = addOrUpdateItem(arr, idNew);
  }

  return arr;
};
