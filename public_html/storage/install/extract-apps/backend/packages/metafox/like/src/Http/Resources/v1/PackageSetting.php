<?php

namespace MetaFox\Like\Http\Resources\v1;

use MetaFox\Like\Http\Resources\v1\Reaction\ReactionItemCollection;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\MetaFox;

class PackageSetting
{
    public function getMobileSettings(): array
    {
        $reactionList         = $this->getReactions();
        $inactiveReactionList = $this->getInactiveReactions();

        /**@deprecated v1.9 remove check "version_compare" */
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.8', '<')) {
            $reactionList         = $this->handleMapOldVersion($reactionList);
            $inactiveReactionList = $this->handleMapOldVersion($inactiveReactionList);
        }

        return [
            'reaction_list'          => $reactionList,
            'inactive_reaction_list' => $inactiveReactionList,
        ];
    }

    public function getWebSettings(): array
    {
        return [
            'reaction_list'          => $this->getReactions(),
            'inactive_reaction_list' => $this->getInactiveReactions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getReactions(): array
    {
        $repository = resolve(ReactionRepositoryInterface::class);
        $reactions  = $repository->getReactionsForConfig();

        return (new ReactionItemCollection($reactions))->toArray(request());
    }

    public function getInactiveReactions(): array
    {
        $repository = resolve(ReactionRepositoryInterface::class);
        $reactions  = $repository->getReactionsForConfig(0);

        return (new ReactionItemCollection($reactions))->toArray(request());
    }

    public function handleMapOldVersion(array $reactionList): array
    {
        $reactionLists = [];
        foreach ($reactionList as $index => $item) {
            $item['id']      = $index + 1;
            $reactionLists[] = $item;
        }

        return $reactionLists;
    }
}
