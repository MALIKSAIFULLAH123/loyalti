<?php

namespace MetaFox\Video\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Factory\HasSetState;
use MetaFox\Video\Models\Video;

class VideoFactory extends Factory
{
    use HasSetState;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $text  = $this->faker->text;

        return [
            'in_process'      => 0,
            'is_stream'       => 0,
            'is_featured'     => 0,
            'is_sponsor'      => 0,
            'is_approved'     => 1,
            'sponsor_in_feed' => 0,
            'group_id'        => 0,
            'asset_id'        => '',
            'privacy'         => MetaFoxPrivacy::EVERYONE,
            'user_id'         => 1,
            'user_type'       => 'user',
            'owner_id'        => 1,
            'owner_type'      => 'user',
            'image_file_id'   => $this->sampleFile('jpeg')?->id,
            'video_file_id'   => $this->sampleFile('mp4')?->id,
            'title'           => $this->faker->words(4, true),
            'text'            => $text,
            'video_url'       => 'https://demo.metafox.app/storage/video/2023/2-15/fd175c7b-3837-4b9b-b92b-7de22c4ffb14.MP4',
            'embed_code'      => Str::random(6),
            'file_ext'        => 'mp4',
            'duration'        => '01:00:00',
            'resolution_x'    => '500',
            'resolution_y'    => '600',
            'total_like'      => 0,
            'total_share'     => 0,
            'total_comment'   => 0,
            'total_view'      => 0,
        ];
    }
}
