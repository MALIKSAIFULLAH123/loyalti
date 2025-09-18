import React from 'react';

export interface AddStoryContextProps {
  status: any;
  setStatus: any;
  setFilePhoto: any;
  filePhoto: any;
  setUploading: any;
  uploading: boolean;
  setInit?: any;
  init?: boolean;
  checkSetStatus?: any;
  setIsDirty?: any;
  isDirty?: boolean;
  isSubmitting?: boolean;
  setIsSubmitting?: any;
}

const initialDefault = {
  status: null,
  setStatus: () => {},
  setFilePhoto: () => {},
  filePhoto: null,
  setUploading: () => {},
  uploading: false,
  setIsDirty: () => {},
  isDirty: false,
  isSubmitting: false,
  setIsSubmitting: () => {}
};

const AddFormContext =
  React.createContext<AddStoryContextProps>(initialDefault);

export default AddFormContext;
