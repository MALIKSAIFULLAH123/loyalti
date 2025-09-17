/**
 * @type: service
 * name: normalization
 */
import { get, isArray, isObject, assign, difference } from 'lodash';

type NormalizeOutput<T = Record<string, any>> = {
  ids: string[];
  data: Record<string, { entities: Record<string, T> }>;
};

type SkipResources = Record<string, boolean>;

const SKIP_KEYS: Record<string, boolean> = {};
const ID_KEY = '_identity';
const MAX_LEVEL = 5;
const ID_ENTITY = 'entities';
const Schema = {};

const MorphMap = {
  like: {
    module_name: 'preaction',
    resource_name: 'like'
  },
  comment_sticker_set: {
    module_name: 'sticker',
    resource_name: 'sticker_set'
  },
  comment_sticker: {
    module_name: 'sticker',
    resource_name: 'sticker'
  },
  tag: {
    resource_name: 'tag',
    module_name: 'core'
  },
  group_member: {
    module_name: 'group',
    resource_name: 'group_member'
  },
  page_type: {
    module_name: 'page',
    resource_name: 'page_type'
  },
  feed: {
    module_name: 'feed',
    resource_name: 'feed'
  }
};

function fixModuleResource(app: string, resource: string): [string, string] {
  if (MorphMap[resource]) {
    app = MorphMap[resource].module_name;
    resource = MorphMap[resource].resource_name;
  } else if (!app) {
    app = app ?? resource.replace(/[-_](.+)$/, '');
  }

  return [app, resource];
}

function fixIdentity(app: string, resource: string, id: string): string {
  const arr = fixModuleResource(app, resource);

  return `${arr[0]}.${ID_ENTITY}.${arr[1]}.${id}`;
}

function isNormalizable(obj: any, skips: Record<string, boolean>): boolean {
  return obj.resource_name && obj.id && !skips[obj.resource_name];
}

function getResourceName(id: string) {
  const resource_name = id.split('.')[2];

  return Object.keys(Schema).includes(resource_name) && resource_name;
}

function isDeNormalizeObject(key: string, resource: string) {
  return Schema[resource].includes(key);
}

function normalizeObject(
  input: any,
  output: NormalizeOutput,
  queue: any[],
  hasExists: Record<string, string[]>,
  putToId: boolean,
  skips: SkipResources
) {
  // put this object to normalize at first.
  const id = fixIdentity(input.module_name, input.resource_name, input.id);

  if (!difference(Object.keys(input), hasExists[id]).length) {
    return;
  }

  hasExists[id] = Object.keys(input);
  input[ID_KEY] = id;

  if (input?.feed_param) {
    input = { ...input, ...input.feed_param };

    delete input.feed_param;
  }

  const [app, resource] = fixModuleResource(
    input.module_name,
    input.resource_name
  );

  input.module_name = app;
  input.resource_name = resource;

  if (!output.data[app]) {
    output.data[app] = { entities: {} };
  }

  if (!output.data[app][ID_ENTITY]) {
    output.data[app][ID_ENTITY] = {};
  }

  if (!output.data[app][ID_ENTITY][resource]) {
    output.data[app][ID_ENTITY][resource] = {};
  }

  if (output.data[app][ID_ENTITY][resource][input.id]) {
    input = assign(output.data[app][ID_ENTITY][resource][input.id], input);
    output.data[app][ID_ENTITY][resource][input.id] = input;
  } else {
    output.data[app][ID_ENTITY][resource][input.id] = input;
  }

  // push to all id ?
  if (putToId) {
    output.ids.push(id);
  }

  Object.keys(input).forEach(key => {
    if (null === input[key]) {
      // delete input[key];
    } else if (SKIP_KEYS[key]) {
      // skip key
    } else if (isObject(input[key]) && isNormalizable(input[key], skips)) {
      const childId = fixIdentity(
        input[key].module_name,
        input[key].resource_name,
        input[key].id
      );

      if (!hasExists[childId]) {
        queue.push(input[key]);
      }

      input[key] = childId;
    } else if (
      isArray(input[key]) &&
      0 < input[key].length &&
      isObject(input[key][0]) &&
      isNormalizable(input[key][0], skips)
    ) {
      const childIdList = [];

      input[key].forEach((child: any) => {
        if (child && isObject(child) && isNormalizable(child, skips)) {
          const id = fixIdentity(
            child.module_name,
            child.resource_name,
            child.id
          );

          childIdList.push(id);

          if (!hasExists[id]) queue.push(child);
        }
      });
      input[key] = childIdList;
    }
  });
}

function normalize(
  input: any,
  output: NormalizeOutput,
  queue: any[],
  hasExists: Record<string, string[]>,
  skips: SkipResources
) {
  if (Array.isArray(input)) {
    for (let i = 0; i < input.length; ++i) {
      normalizeObject(input[i], output, queue, hasExists, true, skips);
    }
  } else if (isObject(input) && isNormalizable(input, skips)) {
    normalizeObject(input, output, queue, hasExists, true, skips);
  }

  let runCount = 0;
  let min = 0;
  while (runCount++ < MAX_LEVEL && 0 < queue.length) {
    for (let i = min; i < queue.length; ++i) {
      normalizeObject(queue[i], output, queue, hasExists, false, skips);
    }
    min = queue.length;
  }
}

function deNormalizeObject(
  input: object, // data from input json
  idStr: string,
  output: any[],
  hasExists: Record<string, boolean>
) {
  if (hasExists[idStr]) {
    return;
  }

  hasExists[idStr] = true;

  const resourceName = getResourceName(idStr);

  if (resourceName) {
    const item = get(input, idStr);

    Object.keys(item).forEach(key => {
      if (
        isDeNormalizeObject(key, resourceName) &&
        ('string' === typeof item[key] || Array.isArray(item[key]))
      ) {
        const inputChild: NormalizeOutput = {
          ids: 'string' === typeof item[key] ? [item[key]] : item[key],
          data: { ...input }
        };
        const itemKeyOut = [];
        const hasChildExists = {};

        de_normalize(inputChild, itemKeyOut, hasChildExists);
        item[key] = 'string' === typeof item[key] ? itemKeyOut[0] : itemKeyOut;
      }
    });
    output.push(item);
  }
}

function de_normalize(
  input: NormalizeOutput,
  output: any[],
  hasExists: Record<string, boolean>
) {
  if (isObject(input)) {
    const { ids, data } = input;
    // check lever

    if (ids && Array.isArray(ids)) {
      for (let i = 0; i < ids.length; ++i) {
        deNormalizeObject(data, ids[i], output, hasExists);
      }
    }
  }
}

export type NormalizationProps = {};

export default class Normalization {
  options: NormalizationProps;
  readonly _skipResources: SkipResources = {};

  constructor(options: NormalizationProps) {
    this.options = options;
  }

  bootstrap() {}

  /**
   * @param {Object} map - See MorphMap.
   */
  morphMap(map: Record<string, Record<string, string>>) {
    Object.keys(map).forEach(key => {
      MorphMap[key] = map[key];
    });
  }

  morphed(type: string): Record<string, string> | undefined {
    return MorphMap[type];
  }

  skipResources(names: string[]) {
    names.forEach(x => {
      this._skipResources[x] = true;
    });
  }

  normalize(input: any): NormalizeOutput {
    const output = {
      ids: [],
      data: {}
    };

    const queue = [];
    const hasExists = {};
    // just normlize object
    normalize(input, output, queue, hasExists, this._skipResources);
    // swap queue.

    return output;
  }

  de_normalize(input: NormalizeOutput) {
    const output = [];
    const hasExists = {};

    de_normalize(input, output, hasExists);

    return output;
  }
}
