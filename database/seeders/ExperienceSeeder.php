<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hostUser = User::query()->where('role', 'host')->first() ?? User::query()->first();

        if (! $hostUser) {
            return;
        }

        $experiences = [
            [
                'title' => 'Traditional Ethiopian Coffee Ceremony & Roasting Masterclass',
                'slug' => 'traditional-ethiopian-coffee-ceremony-roasting',
                'city' => 'Addis Ababa',
                'location_address' => 'Kazanchis, Near ECA Headquarters',
                'category' => 'coffee_ceremony',
                'description' => "Experience the birthplace of Arabica coffee through an intimate 3-hour journey.\n\nLearn how to wash, roast over open charcoal, grind with traditional wooden mortar (mukecha), and brew three ceremonial rounds (Abol, Tona, Baraka) accompanied by freshly popped corn and frankincense aroma.",
                'price_etb' => 1500.00,
                'price_usd' => 25.00,
                'duration_hours' => 3.0,
                'max_participants' => 8,
                'languages' => ['en', 'am'],
                'included_items' => ['Specialty Yirgacheffe beans tasting', 'Traditional snack platter', 'Take-home freshly roasted batch'],
                'meeting_point' => 'Bole Medhanialem or Kazanchis Hotel pick-up',
                'latitude' => 9.0163,
                'longitude' => 38.7652,
                'cover_image' => '/images/luxury/hero-skyline.jpg',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => 'Historic Harar Jugol Walled City & Hyena Feeding Encounter',
                'slug' => 'historic-harar-jugol-hyena-feeding-encounter',
                'city' => 'Harar',
                'location_address' => 'Harar Jugol UNESCO Heritage Gate',
                'category' => 'historical',
                'description' => "Explore the 4th holiest city in Islam with its 82 mosques and labyrinthine alleyways.\n\nAt nightfall, witness the legendary Harar Hyena Man calling wild spotted hyenas by name and feeding them from mouth-held wooden sticks—a centuries-old peaceful coexistence tradition.",
                'price_etb' => 3500.00,
                'price_usd' => 45.00,
                'duration_hours' => 5.0,
                'max_participants' => 12,
                'languages' => ['en', 'am', 'om'],
                'included_items' => ['Licensed local Harari guide', 'Hyena feeding ceremony entry', 'Traditional Harari home dinner'],
                'meeting_point' => 'Shoa Gate, Old City Wall',
                'latitude' => 9.3139,
                'longitude' => 42.1182,
                'cover_image' => '/images/luxury/hero-skyline.jpg',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => 'Mount Entoto Scenic Ridge Hike & Menelik Imperial Palace',
                'slug' => 'mount-entoto-scenic-ridge-hike-menelik-palace',
                'city' => 'Addis Ababa',
                'location_address' => 'Entoto Natural Park Ridge',
                'category' => 'nature',
                'description' => "Breathe crisp eucalyptus mountain air at 3,200 meters overlooking the entire Addis Ababa skyline.\n\nVisit Emperor Menelik II’s 19th-century palace compound, St. Mary church museum, and trek pristine pine trails accompanied by local naturalist guides.",
                'price_etb' => 2200.00,
                'price_usd' => 30.00,
                'duration_hours' => 4.5,
                'max_participants' => 15,
                'languages' => ['en', 'am'],
                'included_items' => ['Entoto Park entrance ticket', 'Guided mountain trek', 'Eucalyptus honey tea & pastries'],
                'meeting_point' => 'Entoto Park Main Gate',
                'latitude' => 9.0750,
                'longitude' => 38.7450,
                'cover_image' => '/images/luxury/hero-skyline.jpg',
                'status' => 'published',
                'is_featured' => false,
            ],
            [
                'title' => 'Lake Hawassa Morning Fisherman Harbor & Birdwatching',
                'slug' => 'lake-hawassa-fisherman-harbor-birdwatching',
                'city' => 'Hawassa',
                'location_address' => 'Amora Gedel Waterfront',
                'category' => 'nature',
                'description' => "Greet sunrise on the Great Rift Valley shores as colorful wooden skiffs return with daily tilapia catches.\n\nWatch giant Marabou storks, white pelicans, and colobus monkeys in the lush acacia canopy, followed by a piping-hot fresh fish soup breakfast.",
                'price_etb' => 1800.00,
                'price_usd' => 25.00,
                'duration_hours' => 3.5,
                'max_participants' => 10,
                'languages' => ['en', 'am'],
                'included_items' => ['Boat ride around Lake Hawassa', 'Birdwatching field binoculars', 'Fresh fish culinary tasting'],
                'meeting_point' => 'Haile Resort Waterfront Pier',
                'latitude' => 7.0504,
                'longitude' => 38.4955,
                'cover_image' => '/images/luxury/hero-skyline.jpg',
                'status' => 'published',
                'is_featured' => true,
            ],
        ];

        foreach ($experiences as $data) {
            Experience::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['host_user_id' => $hostUser->id])
            );
        }
    }
}
