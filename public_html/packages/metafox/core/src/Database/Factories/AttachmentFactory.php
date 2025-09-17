<?php

namespace MetaFox\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use MetaFox\Core\Models\Attachment;
use MetaFox\Platform\Support\Factory\HasSetState;

/**
 * Class AttachmentFactory.
 * @method Attachment create($attributes = [], ?Model $parent = null)
 * @ignore
 * @codeCoverageIgnore
 */
class AttachmentFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => 1,
            'user_type' => 'user',
        ];
    }

    /**
     * @param string $itemType
     * @param string $fileName
     * @param bool   $isImage
     *
     * @return self
     */
    public function setData(string $itemType, string $fileName, bool $isImage = false): self
    {
        $now          = Carbon::now();
        $storagePath  = $itemType . DIRECTORY_SEPARATOR . $now->year . DIRECTORY_SEPARATOR . $now->month . DIRECTORY_SEPARATOR . $now->day;
        $fullFileName = $storagePath . DIRECTORY_SEPARATOR . $fileName;
        $file         = UploadedFile::fake()->create($fullFileName);

        if ($isImage) {
            $file = UploadedFile::fake()->image($fullFileName);
        }

        $data = [
            'item_type'     => $itemType,
            'file_name'     => mb_pathinfo($file->getFilename(), PATHINFO_FILENAME),
            'original_name' => mb_pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'dir_name'      => mb_pathinfo($fullFileName, PATHINFO_DIRNAME),
            'path'          => $fullFileName,
            'file_size'     => $file->getSize(),
            'extension'     => $file->getClientOriginalExtension(),
            'mime_type'     => $file->getMimeType(),
            'server_id'     => 'public',
        ];

        if ($isImage) {
            $data['is_image'] = true;
            $data['width']    = 10;
            $data['height']   = 10;
        }

        return $this->state(function () use ($data) {
            return $data;
        });
    }
}

// end
