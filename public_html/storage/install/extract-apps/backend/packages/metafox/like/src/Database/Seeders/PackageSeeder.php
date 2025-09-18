<?php

namespace MetaFox\Like\Database\Seeders;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Repositories\Eloquent\ReactionRepository;
use MetaFox\Platform\PackageManager;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PackageSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    private ReactionRepository $reactionRepository;

    /**
     * @param ReactionRepository $reactionRepository
     */
    public function __construct(ReactionRepository $reactionRepository)
    {
        $this->reactionRepository = $reactionRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws ValidatorException|FileNotFoundException
     */
    public function run()
    {
        $this->importReactions();
    }

    /**
     * @throws ValidatorException|FileNotFoundException
     */
    private function importReactions()
    {
        if ($this->reactionRepository->getModel()->newQuery()->exists()) {
            $this->handleIconFileId();
            return;
        }

        $reactions = Arr::get($this->getPredefinedReactions(), 'reactions', []);
        foreach ($reactions as $reaction) {
            $this->reactionRepository->create($reaction);
        }

        $this->handleIconFileId();
    }

    private function getPredefinedReactions()
    {
        $reactionPath = 'like/images';

        return [
            'name'      => 'Like',
            'reactions' => [
                [
                    'title'      => 'like::phrase.like__u',
                    'icon_path'  => "assets/{$reactionPath}/like.svg",
                    'ordering'   => 1,
                    'is_active'  => 1,
                    'is_default' => 1,
                    'color'      => '009fe2',
                    'server_id'  => 'asset',
                    'icon_font'  => 'ico-thumbup-o',
                ],
                [
                    'title'      => 'like::phrase.love__u',
                    'icon_path'  => "assets/{$reactionPath}/love.svg",
                    'ordering'   => 2,
                    'is_active'  => 1,
                    'is_default' => 0,
                    'color'      => 'ff314c',
                    'server_id'  => 'asset',
                    'icon_font'  => 'ico-thumbup-o',
                ],
                [
                    'title'      => 'like::phrase.haha__u',
                    'icon_path'  => "assets/{$reactionPath}/haha.svg",
                    'ordering'   => 1,
                    'is_active'  => 1,
                    'is_default' => 0,
                    'color'      => 'ffc84d',
                    'server_id'  => 'asset',
                    'icon_font'  => 'ico-thumbup-o',
                ],
                [
                    'title'      => 'like::phrase.wow__u',
                    'icon_path'  => "assets/{$reactionPath}/wow.svg",
                    'ordering'   => 1,
                    'is_active'  => 1,
                    'is_default' => 0,
                    'color'      => 'ffc84d',
                    'server_id'  => 'asset',
                    'icon_font'  => 'ico-thumbup-o',
                ],
                [
                    'title'      => 'like::phrase.sad__u',
                    'icon_path'  => "assets/{$reactionPath}/sad.svg",
                    'ordering'   => 1,
                    'is_active'  => 1,
                    'is_default' => 0,
                    'color'      => 'ffc84d',
                    'server_id'  => 'asset',
                    'icon_font'  => 'ico-thumbup-o',
                ],
                [
                    'title'      => 'like::phrase.angry__u',
                    'icon_path'  => "assets/{$reactionPath}/angry.svg",
                    'ordering'   => 1,
                    'is_active'  => 1,
                    'is_default' => 0,
                    'color'      => 'e95921',
                    'server_id'  => 'asset',
                    'icon_font'  => 'ico-thumbup-o',
                ],
            ],
        ];
    }

    private function handleIconFileId(): void
    {
        $storage     = app('storage');
        $assetId     = 'asset';
        $filename    = base_path('packages/metafox/like/resources/reactions.json');
        $collections = json_decode(mf_get_contents($filename), true);
        $directory   = base_path(PackageManager::getAssetPath('metafox/like'));

        foreach ($collections['items'] as $item) {
            $iconPath = 'assets/like/' . $item['path_svg'];
            $reaction = Reaction::query()
                ->where('icon_path', $this->reactionRepository->likeOperator(), '%' . $iconPath . '%')
                ->whereNull('icon_file_id')->first();

            if (!$reaction) {
                continue;
            }

            $localPath = $item['path_png'];
            $filename  = $directory . '/' . $localPath;
            $origin    = $storage->putFileAs($assetId, 'assets/images', $filename, $localPath);

            $reaction->update([
                'icon_file_id' => $origin->id,
                'image_path'   => $origin->path,
            ]);
        }
    }
}
