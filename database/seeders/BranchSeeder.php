<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Branch;
use App\Models\Property;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hq = Branch::firstOrCreate(
            ['code' => 'BR-BOL-HQ'],
            [
                'name' => 'Seyid Real Estate HQ - Bole Medhanialem',
                'city' => 'Addis Ababa',
                'subcity' => 'Bole',
                'address' => 'Medhanialem Tower, 8th Floor, Bole Road',
                'manager_name' => 'Seyid Mohammed (Managing Director)',
                'phone' => '+251 91 100 2000',
                'email' => 'hq@seyidrealestate.com',
                'status' => 'active',
                'is_headquarters' => true,
            ]
        );

        $kazanchis = Branch::firstOrCreate(
            ['code' => 'BR-KAZ-02'],
            [
                'name' => 'Seyid Kazanchis Financial District',
                'city' => 'Addis Ababa',
                'subcity' => 'Kirkos',
                'address' => 'ECA Commercial Plaza, Suite 402',
                'manager_name' => 'Amina Al-Sayed',
                'phone' => '+251 92 200 3000',
                'email' => 'kazanchis@seyidrealestate.com',
                'status' => 'active',
                'is_headquarters' => false,
            ]
        );

        $cmc = Branch::firstOrCreate(
            ['code' => 'BR-CMC-03'],
            [
                'name' => 'Seyid CMC Luxury Center',
                'city' => 'Addis Ababa',
                'subcity' => 'Yeka',
                'address' => 'CMC Sunshine Commercial Hub, Ground Floor',
                'manager_name' => 'Dawit Tadesse',
                'phone' => '+251 93 300 4000',
                'email' => 'cmc@seyidrealestate.com',
                'status' => 'active',
                'is_headquarters' => false,
            ]
        );

        $hawassa = Branch::firstOrCreate(
            ['code' => 'BR-HAW-04'],
            [
                'name' => 'Seyid Hawassa Lakeside Office',
                'city' => 'Hawassa',
                'subcity' => 'Menaheria',
                'address' => 'Lake View Commercial Complex',
                'manager_name' => 'Bethlehem Haile',
                'phone' => '+251 94 400 5000',
                'email' => 'hawassa@seyidrealestate.com',
                'status' => 'active',
                'is_headquarters' => false,
            ]
        );

        // Assign existing agents and properties to branches if not assigned yet
        $agents = Agent::all();
        if ($agents->isNotEmpty()) {
            foreach ($agents as $idx => $agent) {
                if (! $agent->branch_id) {
                    $targetBranch = match ($idx % 3) {
                        0 => $hq,
                        1 => $kazanchis,
                        default => $cmc,
                    };
                    $agent->update(['branch_id' => $targetBranch->id]);
                }
            }
        }

        $properties = Property::all();
        if ($properties->isNotEmpty()) {
            foreach ($properties as $idx => $property) {
                if (! $property->branch_id) {
                    $targetBranch = match ($idx % 4) {
                        0 => $hq,
                        1 => $kazanchis,
                        2 => $cmc,
                        default => $hawassa,
                    };
                    $property->update(['branch_id' => $targetBranch->id]);
                }
            }
        }
    }
}
