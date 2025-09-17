import {
  WhereFilterOp,
  DocumentReference,
  SnapshotMetadata
} from 'firebase/firestore';

type Field<T> = keyof T;
type Operator = WhereFilterOp;

/*
interface Condition {
  field: Field
  operator: Operator
  value: any
}
*/
type Condition<T> = [Field<T>, Operator, any];

type OrderBy<T> = {
  field: Field<T>;
  descending: boolean;
};

interface IDoc {
  ref: DocumentReference;
  docId: string;
  metadata: SnapshotMetadata;
}

type ICustomDoc<T> = T & IDoc;

type DocListener = undefined | (() => void);

interface IConfig<T> {
  collection: string;
  dataMapping?:
    | ((data: ICustomDoc<T>) => ICustomDoc<T>)
    | ((data: ICustomDoc<T>) => Promise<ICustomDoc<T>>);
  refresh?: any[];
  options?: {
    conditions: Condition<T>[];
    orderBy?: OrderBy<T>[];
    limit?: number;
    enableLogging?: boolean;
  };
}

export { IConfig, IDoc, ICustomDoc, Condition, DocListener };
