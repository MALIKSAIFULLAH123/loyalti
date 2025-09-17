/**
 * create paging action
 * @param appName
 */

type PagingActionKey =
  | 'PAGING_SUCCESS'
  | 'PAGING_START'
  | 'UPDATE_ENTITY'
  | 'RESET_ENTITY'
  | 'REMOVE_ENTITY'
  | 'PATCH_ENTITY';

export default function createAppAction(
  appName: string
): Record<PagingActionKey, string> {
  return {
    PAGING_SUCCESS: `@${appName}/paging/SUCCESS`,
    PAGING_START: `@${appName}/paging/START`,
    UPDATE_ENTITY: `@${appName}/entity/UPDATE`,
    RESET_ENTITY: `@${appName}/entity/RESET`,
    REMOVE_ENTITY: `@${appName}/entity/REMOVE`,
    PATCH_ENTITY: `@${appName}/entity/PATCH`
  };
}
