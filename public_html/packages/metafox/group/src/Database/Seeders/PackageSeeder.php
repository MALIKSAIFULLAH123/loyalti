<?php

namespace MetaFox\Group\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Models\ExampleRule;
use MetaFox\Group\Repositories\Eloquent\CategoryRepository;
use MetaFox\Group\Repositories\Eloquent\ExampleRuleRepository;
use MetaFox\Profile\Models\Profile;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;
    /**
     * @var ExampleRuleRepository
     */
    private ExampleRuleRepository $groupRuleExampleRepository;

    /** @var array<int, mixed> */
    protected array $categories = [
        ['name' => 'group::phrase.category_entertainment'],
        ['name' => 'group::phrase.category_brand_or_product'],
        ['name' => 'group::phrase.category_local_business_or_place'],
        ['name' => 'group::phrase.category_company_organization_or_institution'],
        ['name' => 'group::phrase.category_artist_band_or_public_figure'],
        ['name' => 'group::phrase.category_sports'],
        ['name' => 'group::phrase.category_food'],
        ['name' => 'group::phrase.category_travel'],
        ['name' => 'group::phrase.category_photography'],
        ['parent_id' => '1', 'name' => 'group::phrase.category_album', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_amateur_sports_team', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_book', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_book_store', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_concert_tour', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_concert_venue', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_fictional_character', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_library', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_magazine', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_movie', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_movie_theater', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_music_award', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_music_chart', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_music_video', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_musical_instrument', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_playlist', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_professional_sports_team', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_tadio_station', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_record_label', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_school_sports_team', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_song', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_sports_league', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_sports_venue', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_studio', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_tv_channel', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_tv_network', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_tv_show', 'level' => 2],
        ['parent_id' => '1', 'name' => 'group::phrase.category_tv_movie_award', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_appliances', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_baby_goods_kids_goods', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_bags_luggage', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_building_materials', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_camera_photo', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_cars', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_clothing', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_commercial_equipment', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_computers', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_drugs', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_electronics', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_food_beverages', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_furniture', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_games_toys', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_health_beauty', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_home_decor', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_household_supplies', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_jewelry_watches', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_kitchen_cooking', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_movies_music', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_musical_instrument', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_office_supplies', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_outdoor_gear_sporting_goods', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_patio_garden', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_pet_supplies', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_product_service', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_software', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_tools_equipment', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_vitamins_supplements', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_website', 'level' => 2],
        ['parent_id' => '2', 'name' => 'group::phrase.category_wine_spirits', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_airport', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_arts_entertainment_nightlife', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_attractions_things_to_do', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_automotive', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_bank_financial_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_bar', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_book_store', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_business_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_church_religious_organization', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_club', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_community_government', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_concert_venue', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_education', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_event_planning_event_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_food_grocery', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_health_medical_pharmacy', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_home_improvement', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_hospital_clinic', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_hotel', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_landmark', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_library', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_local_business', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_movie_theater', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_museum_art_gallery', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_pet_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_professional_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_public_places', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_real_estate', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_restaurant_cafe', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_school', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_shopping_retail', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_spas_beauty_personal_care', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_sports_venue', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_sports_recreation_activities', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_tours_sightseeing', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_transit_stop', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_transportation', 'level' => 2],
        ['parent_id' => '3', 'name' => 'group::phrase.category_university', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_aerospace_defense', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_automobiles_and_parts', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_bank_financial_institution', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_biotechnology', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_cause', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_chemicals', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_church_religious_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_community_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_company', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_computers_technology', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_consulting_business_services', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_education', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_energy_utility', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_engineering_construction', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_farming_agriculture', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_food_beverages', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_government_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_health_beauty', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_health_medical_pharmaceuticals', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_industrials', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_insurance_company', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_internet_software', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_legal_law', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_media_news_publishing', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_mining_materials', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_non_governmental_organization_ngo', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_non_profit_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_political_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_political_party', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_retail_and_consumer_merchandise', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_school', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_small_business', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_telecommunication', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_transport_freight', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_travel_leisure', 'level' => 2],
        ['parent_id' => '4', 'name' => 'group::phrase.category_university', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_actor_director', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_artist', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_athlete', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_author', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_business_person', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_chef', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_coach', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_comedian', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_dancer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_doctor', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_editor', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_entertainer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_fictional_character', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_government_official', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_journalist', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_lawyer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_monarch', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_musician_band', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_news_personality', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_politician', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_producer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_public_figure', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_teacher', 'level' => 2],
        ['parent_id' => '5', 'name' => 'group::phrase.category_writer', 'level' => 2],
    ];

    /**
     * GroupDatabaseSeeder constructor.
     *
     * @param CategoryRepository    $categoryRepository
     * @param ExampleRuleRepository $groupRuleExampleRepository
     */
    public function __construct(
        CategoryRepository    $categoryRepository,
        ExampleRuleRepository $groupRuleExampleRepository
    )
    {
        $this->categoryRepository         = $categoryRepository;
        $this->groupRuleExampleRepository = $groupRuleExampleRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws ValidatorException
     */
    public function run()
    {
        $this->categorySeeder();
        $this->categoryRelationData();
        $this->ruleExampleSeeder();
        $this->customProfileGroup();
    }

    /**
     * @throws ValidatorException
     */
    public function categorySeeder(): void
    {
        $category = $this->categoryRepository->getModel()->newQuery()->exists();
        if ($category) {
            return;
        }

        foreach ($this->categories as $item) {
            $this->categoryRepository->create($item);
        }

    }

    /**
     * @throws ValidatorException
     */
    public function ruleExampleSeeder()
    {
        if (ExampleRule::query()->exists()) {
            return;
        }

        $data = [
            [
                'title'       => 'group::phrase.rule_be_polite_label',
                'description' => 'group::phrase.rule_be_polite_desc',
                'ordering'    => 1,
            ],
            [
                'title'       => 'group::phrase.rule_vulgar_language_label',
                'description' => 'group::phrase.rule_vulgar_language_desc',
                'ordering'    => 2,
            ],
            [
                'title'       => 'group::phrase.rule_no_ad_spam_label',
                'description' => 'group::phrase.rule_no_ad_spam_desc',
                'ordering'    => 3,
            ],
        ];

        ExampleRule::truncate();
        ExampleRule::insert($data);
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('group_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }

    protected function customProfileGroup(): void
    {
        if (!Profile::query()->where('user_type', 'group')->exists()) {
            Profile::query()->upsert([
                ['profile_type' => 'group', 'user_type' => 'group'],
            ], ['profile_type']);
        }
    }
}
