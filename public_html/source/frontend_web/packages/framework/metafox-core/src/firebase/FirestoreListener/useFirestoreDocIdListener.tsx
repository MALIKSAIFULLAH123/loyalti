import { useEffect, useState } from 'react';
import { DocListener } from './types';
import { onSnapshot, doc } from 'firebase/firestore';

interface IConfig {
  collection: string;
  docID: string;
}
interface IDoc {
  docId: string;
}
type DocumentData<T> = T & IDoc;

const useFirestoreDocIdListener = <T, >(db, config: IConfig) => {
  const [docState, setDocState] = useState<DocumentData<T>>();

  useEffect(() => {
    if (!db || !config?.collection || !config?.docID) return;

    let docListener: DocListener;
    try {
      const cr = doc(db, config.collection, config.docID);

      docListener = onSnapshot(cr, async snapshot => {
        setDocState(snapshot.data() as DocumentData);
      });
    } catch (err) {}

    return () => {
      docListener?.();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return docState;
};

export default useFirestoreDocIdListener;
