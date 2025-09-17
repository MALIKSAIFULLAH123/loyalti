<?php

namespace MetaFox\Core\Repositories\Eloquent;

use MetaFox\Core\Models\AttachmentFileType;
use MetaFox\Core\Repositories\AttachmentFileTypeRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Contracts\User;

/**
 * @method AttachmentFileType getModel()
 * @method AttachmentFileType find($id, $columns = ['*'])
 */
class AttachmentFileTypeRepository extends AbstractRepository implements AttachmentFileTypeRepositoryInterface
{
    public function model()
    {
        return AttachmentFileType::class;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewFileTypes(User $context, array $attributes = []): Paginator
    {
        $search   = Arr::get($attributes, 'q');
        $isActive = Arr::get($attributes, 'is_active');
        $limit    = Arr::get($attributes, 'limit');

        $query = $this->getModel()->newModelQuery();

        if ($search) {
            $searchScope = new SearchScope($search, ['extension', 'mime_type']);
            $query       = $query->addScope($searchScope);
        }

        if (null !== $isActive) {
            $query->where('is_active', $isActive);
        }

        return $query->orderBy('extension')->paginate($limit);
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createFileType(User $context, array $attributes = []): AttachmentFileType
    {
        $extension = Arr::get($attributes, 'extension') ?? '';
        $mimeType  = Arr::get($attributes, 'mime_type') ?? '';

        $existedFileType = $this->getModel()->newModelQuery()
            ->where('extension', $extension)
            ->orWhere('mime_type', $mimeType)
            ->first();

        if ($existedFileType instanceof AttachmentFileType) {
            return $existedFileType;
        }

        $typeParams = [
            'extension' => parse_input()->clean($extension, true, true),
            'mime_type' => parse_input()->clean($mimeType, true, true),
            'is_active' => Arr::get($attributes, 'is_active') ?? 1,
        ];

        $fileType = new AttachmentFileType();
        $fileType->fill($typeParams);
        $fileType->save();

        return $fileType;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateFileType(User $context, int $id, array $attributes = []): AttachmentFileType
    {
        $fileType = $this->find($id);

        $extension = Arr::get($attributes, 'extension') ?? '';
        $mimeType  = Arr::get($attributes, 'mime_type') ?? '';

        $typeParams = [
            'extension' => parse_input()->clean($extension, true, true),
            'mime_type' => parse_input()->clean($mimeType, true, true),
            'is_active' => Arr::get($attributes, 'is_active') ?? 1,
        ];

        $fileType->update($typeParams);
        $fileType->refresh();

        return $fileType;
    }

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteFileType(User $context, int $id): bool
    {
        $fileType = $this->find($id);

        return (bool) $fileType->delete();
    }
}
