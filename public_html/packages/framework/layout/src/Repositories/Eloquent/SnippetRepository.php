<?php

namespace MetaFox\Layout\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use MetaFox\Layout\Models\Revision;
use MetaFox\Layout\Models\Snippet;
use MetaFox\Layout\Repositories\SnippetRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User;
use ZipArchive;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SnippetRepository.
 */
class SnippetRepository extends AbstractRepository implements SnippetRepositoryInterface
{
    public function model()
    {
        return Snippet::class;
    }

    public function saveTheme(
        User $user,
        string $theme,
        string $variant,
        array $data,
        bool $active
    ) {
        /** @var Snippet $item */
        $item = $this->firstOrNew([
            'snippet' => $theme,
        ]);
        $name = sprintf('%s modified', $user->display_name);

        $item->fill([
            'type'    => 'theme',
            'theme'   => $theme,
            'variant' => $variant,
            'name'    => $name,
        ]);

        if (!$item->is_active && $active) {
            $item->is_active = true;
        }

        $item->data = $data;
        $item->save();

        // create snippet

        $revision = Revision::create([
            'snippet_id' => $item->id,
            'name'       => $name,
            'data'       => $data,
        ]);

        $item->revision_id = $revision->id;
        $item->save();

        return $item;
    }

    public function saveVariant(
        User $user,
        string $theme,
        string $variant,
        array $data,
        bool $active
    ) {
        // save theme first.
        /** @var Snippet $item */
        $item = $this->firstOrNew([
            'snippet' => $variant,
        ]);
        $name = sprintf('%s modified', $user->display_name);

        $item->fill([
            'type'    => 'variant',
            'theme'   => $theme,
            'variant' => $variant,
            'name'    => $name,
        ]);

        if (!$item->is_active && $active) {
            $item->is_active = true;
        }

        $item->data = $data;
        $item->save();

        $revision = Revision::create([
            'snippet_id' => $item->id,
            'name'       => $name,
            'data'       => $data,
        ]);

        $item->revision_id = $revision->id;

        return $item;
    }

    public function purge(): void
    {
        $this->getModel()->newQuery()->delete();
    }

    private function attachActiveTheme(\ArrayObject $data, ZipArchive $zip)
    {
        /** @var Collection<Snippet> $snippets */
        $snippets = $this->getModel()
            ->newQuery()
            ->where('type', '=', 'theme')
            ->where('is_active', '=', 1)
            ->get();

        foreach ($snippets as $snippet) {
            foreach ($snippet->data as $item) {
                if (!isset($item['filename']) || !isset($item['content'])) {
                    continue;
                }
                $content = json_encode($item['content']);
                $zip->addFromString($item['filename'], $content);
                Log::channel('dev')->info(sprintf('Attached file "%s"', $item['filename']));
            }
        }
    }

    private function attachActiveVariant(\ArrayObject $data, ZipArchive $zip)
    {
        /** @var Collection<Snippet> $snippets */
        $snippets = $this->getModel()
            ->newQuery()
            ->where('type', '=', 'variant')
            ->where('is_active', '=', 1)
            ->get();

        foreach ($snippets as $snippet) {
            foreach ($snippet->data as $item) {
                if (!isset($item['filename']) || !isset($item['content'])) {
                    continue;
                }
                $content = json_encode($item['content']);
                $zip->addFromString($item['filename'], $content); // allow to overwrite ?
                Log::channel('dev')->info(sprintf('Attached file "%s"', $item['filename']));
            }
        }
    }

    public function attachBuildArchive(\ArrayObject $data, ZipArchive $zip, ?string $reason=null): void
    {
        $this->attachActiveTheme($data, $zip);
        $this->attachActiveVariant($data, $zip);
    }
}
