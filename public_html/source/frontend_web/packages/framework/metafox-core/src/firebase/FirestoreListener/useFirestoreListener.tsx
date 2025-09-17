import { useEffect, useState } from 'react';
import { IConfig, ICustomDoc, Condition, DocListener } from './types';
import {
  collection,
  query,
  where,
  onSnapshot,
  QueryConstraint,
  orderBy,
  QueryDocumentSnapshot,
  limit
} from 'firebase/firestore';

const useFirestoreListener = <T, >(db, config: IConfig<T>) => {
  const [docState, setDocState] = useState<ICustomDoc<T>[]>([]);

  useEffect(() => {
    if (!db) return;

    let docListener: DocListener;
    try {
      const queryConditions: QueryConstraint[] = [];

      config.options?.conditions.forEach((condition: Condition<T>) => {
        const [field, operator, value] = condition;

        if (typeof field === 'string') {
          queryConditions.push(where(field, operator, value));
        } else throw new Error('Field must be a string.');
      });
      // cr => collection reference
      config.options?.orderBy?.forEach(order => {
        const { field, descending } = order;

        if (typeof field !== 'number' && typeof field !== 'symbol') {
          queryConditions.push(orderBy(field, descending ? 'desc' : 'asc'));
        } else throw new Error('Field must be a string.');
      });

      if (config.options?.limit) {
        queryConditions.push(limit(config.options?.limit));
      }

      const cr = query(collection(db, config.collection), ...queryConditions);

      docListener = onSnapshot(
        cr,
        async snapshots => {
          if (snapshots.empty) {
            setDocState([]);

            return;
          }

          if (config.dataMapping) {
            const newDocs: ICustomDoc<T>[] = [];

            for await (const doc of snapshots.docs) {
              const baseDocData: T = doc.data() as T;
              const docData: ICustomDoc<T> = {
                ...baseDocData,
                docId: doc.id,
                ref: doc.ref,
                metadata: doc.metadata
              };
              const docMapped = await config.dataMapping(docData);
              newDocs.push(docMapped);
            }
            setDocState(newDocs);

            return;
          }

          setDocState(
            snapshots.docs.map((snapshot: QueryDocumentSnapshot) => {
              const snapshotData: T = snapshot.data() as T;

              return {
                ...snapshotData,
                docId: snapshot.id,
                ref: snapshot.ref,
                metadata: snapshot.metadata
              };
            })
          );
        },
        err => {
          if (config.options?.enableLogging)
            console.error('useFirestoreListener: ', err);
        }
      );
    } catch (err) {
      if (config.options?.enableLogging)
        console.error('useFirestoreListener error: ', err);
    }

    return () => {
      docListener?.();
    };
  }, config.refresh ?? []);

  return docState;
};

export default useFirestoreListener;
