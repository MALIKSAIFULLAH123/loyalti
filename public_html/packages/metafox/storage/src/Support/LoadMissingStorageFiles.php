<?php

namespace MetaFox\Storage\Support;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Storage\Models\StorageFile;

class LoadMissingStorageFiles
{
    public function after(Reducer $reducer)
    {
        $ids  = $reducer->values('files');
        $key  = fn ($id) => sprintf('files::files(%s)', $id);
        $key2 = fn ($id) => sprintf('files::fileById(%s)', $id);

        if ($ids->isEmpty()) {
            return;
        }

        $data = $ids->reduce(function ($carry, $x) use ($key, $key2) {
            $carry[$key($x)]  = new Collection();
            $carry[$key2($x)] = null;

            return $carry;
        }, []);

        return StorageFile::query()
            ->whereIn('origin_id', $ids)
            ->get()
            ->reduce(function ($carry, $x) use ($key, $key2) {
                $c = $key($x->origin_id);
                if (array_key_exists($c, $carry)) {
                    $carry[$key($x->origin_id)]->add($x);
                }
                $carry[$key2($x->id)] = $x;

                return $carry;
            }, $data);
    }
}
