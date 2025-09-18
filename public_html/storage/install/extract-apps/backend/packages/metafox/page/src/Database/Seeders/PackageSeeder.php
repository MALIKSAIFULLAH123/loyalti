<?php

namespace MetaFox\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Repositories\Eloquent\PageCategoryRepository;
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
    protected PageCategoryRepository $categoryRepository;

    /**
     * PageTypeTableSeeder constructor.
     *
     * @param PageCategoryRepository $categoryRepository
     */
    public function __construct(
        PageCategoryRepository $categoryRepository
    )
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws ValidatorException
     */
    public function run()
    {
        $this->pageCategorySeeder();
        $this->categoryRelationData();
        $this->customProfilePage();
    }

    protected array $pageCategories = [
        ['name' => 'page::phrase.category_entertainment'],
        ['name' => 'page::phrase.category_brand_or_product'],
        ['name' => 'page::phrase.category_local_business_or_place'],
        ['name' => 'page::phrase.category_company_organization_or_institution'],
        ['name' => 'page::phrase.category_artist_band_or_public_figure'],
        ['name' => 'page::phrase.category_sports'],
        ['name' => 'page::phrase.category_food'],
        ['name' => 'page::phrase.category_travel'],
        ['name' => 'page::phrase.category_photography'],
        ['parent_id' => '1', 'name' => 'page::phrase.category_album', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_amateur_sports_team', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_book', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_book_store', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_concert_tour', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_concert_venue', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_fictional_character', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_library', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_magazine', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_movie', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_movie_theater', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_music_award', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_music_chart', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_music_video', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_musical_instrument', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_playlist', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_professional_sports_team', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_tadio_station', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_record_label', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_school_sports_team', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_song', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_sports_league', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_sports_venue', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_studio', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_tv_channel', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_tv_network', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_tv_show', 'level' => 2],
        ['parent_id' => '1', 'name' => 'page::phrase.category_tv_movie_award', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_appliances', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_baby_goods_kids_goods', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_bags_luggage', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_building_materials', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_camera_photo', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_cars', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_clothing', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_commercial_equipment', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_computers', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_drugs', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_electronics', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_food_beverages', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_furniture', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_games_toys', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_health_beauty', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_home_decor', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_household_supplies', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_jewelry_watches', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_kitchen_cooking', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_movies_music', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_musical_instrument', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_office_supplies', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_outdoor_gear_sporting_goods', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_patio_garden', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_pet_supplies', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_product_service', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_software', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_tools_equipment', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_vitamins_supplements', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_website', 'level' => 2],
        ['parent_id' => '2', 'name' => 'page::phrase.category_wine_spirits', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_airport', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_arts_entertainment_nightlife', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_attractions_things_to_do', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_automotive', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_bank_financial_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_bar', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_book_store', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_business_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_church_religious_organization', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_club', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_community_government', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_concert_venue', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_education', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_event_planning_event_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_food_grocery', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_health_medical_pharmacy', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_home_improvement', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_hospital_clinic', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_hotel', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_landmark', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_library', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_local_business', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_movie_theater', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_museum_art_gallery', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_pet_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_professional_services', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_public_places', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_real_estate', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_restaurant_cafe', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_school', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_shopping_retail', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_spas_beauty_personal_care', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_sports_venue', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_sports_recreation_activities', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_tours_sightseeing', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_transit_stop', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_transportation', 'level' => 2],
        ['parent_id' => '3', 'name' => 'page::phrase.category_university', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_aerospace_defense', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_automobiles_and_parts', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_bank_financial_institution', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_biotechnology', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_cause', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_chemicals', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_church_religious_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_community_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_company', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_computers_technology', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_consulting_business_services', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_education', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_energy_utility', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_engineering_construction', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_farming_agriculture', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_food_beverages', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_government_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_health_beauty', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_health_medical_pharmaceuticals', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_industrials', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_insurance_company', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_internet_software', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_legal_law', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_media_news_publishing', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_mining_materials', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_non_governmental_organization_ngo', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_non_profit_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_political_organization', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_political_party', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_retail_and_consumer_merchandise', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_school', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_small_business', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_telecommunication', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_transport_freight', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_travel_leisure', 'level' => 2],
        ['parent_id' => '4', 'name' => 'page::phrase.category_university', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_actor_director', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_artist', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_athlete', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_author', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_business_person', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_chef', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_coach', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_comedian', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_dancer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_doctor', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_editor', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_entertainer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_fictional_character', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_government_official', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_journalist', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_lawyer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_monarch', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_musician_band', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_news_personality', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_politician', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_producer', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_public_figure', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_teacher', 'level' => 2],
        ['parent_id' => '5', 'name' => 'page::phrase.category_writer', 'level' => 2],
    ];

    /**
     * @throws ValidatorException
     */
    public function pageCategorySeeder(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        foreach ($this->pageCategories as $item) {
            $this->categoryRepository->create($item);
        }
    }

    protected function categoryRelationData(): void
    {
        if (!Schema::hasTable('page_category_relations')) {
            return;
        }

        if ($this->categoryRepository->getRelationModel()->newQuery()->exists()) {
            return;
        }

        $this->categoryRepository->createCategoryRelation();
    }

    protected function customProfilePage(): void
    {
        if (!Profile::query()->where('user_type', 'page')->exists()) {
            Profile::query()->upsert([
                ['profile_type' => 'page', 'user_type' => 'page'],
            ], ['profile_type']);
        }
    }
}
