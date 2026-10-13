<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\ConstructionMilestone;
use App\Models\DeveloperPaymentPlan;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectUnit;
use Illuminate\Database\Seeder;

class DeveloperProjectSeeder extends Seeder
{
    public function run(): void
    {
        $agent = Agent::first();

        // Project 1: Bole Atlas Luxury Heights (Addis Ababa - Bole)
        $project1 = Project::updateOrCreate(
            ['slug' => 'bole-atlas-luxury-heights'],
            [
                'project_code' => 'ETH-DEV-AA-2026-ATLS',
                'name' => 'Bole Atlas Luxury Heights',
                'developer_name' => 'Flintstone Engineering & Developments',
                'developer_phone' => '+251 91 122 3344',
                'developer_email' => 'sales@flintstone-developments.et',
                'agent_id' => $agent?->id,
                'city' => 'Addis Ababa',
                'subcity' => 'Bole',
                'address' => 'Atlas Hotel Area, Cameroon Street, Bole',
                'project_type' => 'mixed_use',
                'construction_status' => 'under_construction',
                'overall_completion_percentage' => 58, // Exact SRS §93 example
                'expected_completion_date' => 'Q4 2027',
                'land_area_sqm' => 4500.00,
                'total_buildings_count' => 2,
                'total_units_count' => 48,
                'starting_price' => 12500000.00,
                'currency' => 'ETB',
                'description' => 'A landmark luxury development in the heart of Bole Atlas, featuring two 18-storey towers with panoramic views of Addis Ababa, underground secure multi-level parking, 3-phase electricity, dual backup generators, commercial shopping concourse, and dedicated resident health club.',
                'facilities' => [
                    '3-Phase Industrial Power & Dual 500kVA Standby Generators',
                    'Municipal Water Supply + 200,000L Underground Reservoir Tank',
                    '4 High-Speed OTIS Passenger Elevators & Service Lift',
                    'Heated Indoor Swimming Pool & Modern Gym',
                    '24/7 Gated Security, Biometric Access & CCTV Perimeter',
                    'Underground Assigned Resident & Visitor Parking',
                    'Commercial Plaza with Supermarket, Pharmacy & Bank ATM',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                ],
                'is_featured' => true,
                'status' => 'published',
            ]
        );

        // Buildings for Project 1 (SRS §89: Project -> Buildings -> Units)
        $buildingA = ProjectBuilding::updateOrCreate(
            ['project_id' => $project1->id, 'name' => 'Tower A - Atlas Skyline'],
            [
                'total_floors' => 'G+18',
                'units_count' => 24,
                'description' => '18-storey residential tower featuring 2-bed and 3-bed executive apartments.',
            ]
        );

        $buildingB = ProjectBuilding::updateOrCreate(
            ['project_id' => $project1->id, 'name' => 'Tower B - Bole Vista'],
            [
                'total_floors' => 'G+16',
                'units_count' => 24,
                'description' => '16-storey residential tower with premium duplexes and penthouses.',
            ]
        );

        // Units for Building A (SRS §91, §92: Available, Reserved, Sold)
        $unitsData = [
            [
                'unit_number' => 'A-401',
                'building_id' => $buildingA->id,
                'floor_number' => '4',
                'unit_type' => '2_bedroom',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqm' => 115.50,
                'price' => 12500000.00,
                'orientation' => 'East / Sunrise View',
                'parking_spaces' => 1,
                'status' => 'available',
            ],
            [
                'unit_number' => 'A-402',
                'building_id' => $buildingA->id,
                'floor_number' => '4',
                'unit_type' => '3_bedroom',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area_sqm' => 165.00,
                'price' => 17800000.00,
                'orientation' => 'South-West / Bole Road View',
                'parking_spaces' => 2,
                'status' => 'available',
            ],
            [
                'unit_number' => 'A-801',
                'building_id' => $buildingA->id,
                'floor_number' => '8',
                'unit_type' => '3_bedroom',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area_sqm' => 172.00,
                'price' => 18900000.00,
                'orientation' => 'North-East / Mountain & City View',
                'parking_spaces' => 2,
                'status' => 'reserved',
            ],
            [
                'unit_number' => 'B-1201',
                'building_id' => $buildingB->id,
                'floor_number' => '12',
                'unit_type' => 'penthouse',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'area_sqm' => 260.00,
                'price' => 32000000.00,
                'orientation' => 'Panoramic 360 Addis Skyline',
                'parking_spaces' => 3,
                'status' => 'available',
            ],
            [
                'unit_number' => 'B-1402',
                'building_id' => $buildingB->id,
                'floor_number' => '14',
                'unit_type' => 'penthouse',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'area_sqm' => 280.00,
                'price' => 35500000.00,
                'orientation' => 'Panoramic City View',
                'parking_spaces' => 3,
                'status' => 'sold',
            ],
        ];

        foreach ($unitsData as $unitItem) {
            ProjectUnit::updateOrCreate(
                ['project_id' => $project1->id, 'unit_number' => $unitItem['unit_number']],
                $unitItem
            );
        }

        // Construction Progress Milestones for Project 1 (SRS §93: Foundation 100%, Structure 70%, Masonry 45%, Finishing 20%, Overall 58%)
        $milestones = [
            ['milestone_name' => 'Excavation & Foundation', 'percentage' => 100, 'target_date' => 'Completed', 'status' => 'completed', 'order_index' => 1],
            ['milestone_name' => 'Substructure & Concrete Skeleton', 'percentage' => 70, 'target_date' => 'In Progress', 'status' => 'in_progress', 'order_index' => 2],
            ['milestone_name' => 'Block Masonry & Perimeter Walls', 'percentage' => 45, 'target_date' => 'Q1 2027', 'status' => 'in_progress', 'order_index' => 3],
            ['milestone_name' => 'MEP, Electrical & Plumbing', 'percentage' => 30, 'target_date' => 'Q2 2027', 'status' => 'in_progress', 'order_index' => 4],
            ['milestone_name' => 'Interior Finishing, Glazing & Lifts', 'percentage' => 20, 'target_date' => 'Q3 2027', 'status' => 'pending', 'order_index' => 5],
            ['milestone_name' => 'Landscaping, Quality Audit & Handover', 'percentage' => 0, 'target_date' => 'Q4 2027', 'status' => 'pending', 'order_index' => 6],
        ];

        foreach ($milestones as $m) {
            ConstructionMilestone::updateOrCreate(
                ['project_id' => $project1->id, 'milestone_name' => $m['milestone_name']],
                $m
            );
        }

        // Developer Payment Plan for Project 1 (SRS §94: 10% Reservation, 20% Foundation, 20% Structure, 20% Roofing, 20% Finishing, 10% Handover)
        $paymentPlans = [
            ['installment_name' => '10% Reservation & Booking Deposit', 'percentage' => 10.00, 'milestone_trigger' => 'Upon Contract Signing', 'order_index' => 1],
            ['installment_name' => '20% Foundation Completion', 'percentage' => 20.00, 'milestone_trigger' => 'Foundation Signed-Off by City Inspector', 'order_index' => 2],
            ['installment_name' => '20% Structural Skeleton Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Casting of 10th Floor Slab', 'order_index' => 3],
            ['installment_name' => '20% Masonry & Roofing Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Outer Facade & Roofing Completed', 'order_index' => 4],
            ['installment_name' => '20% Interior Finishing Milestone', 'percentage' => 20.00, 'milestone_trigger' => 'Tiles, Sanitaries & Electrical Installed', 'order_index' => 5],
            ['installment_name' => '10% Final Handover & Title Deed', 'percentage' => 10.00, 'milestone_trigger' => 'Municipal Key Handover & Deed Transfer', 'order_index' => 6],
        ];

        foreach ($paymentPlans as $plan) {
            DeveloperPaymentPlan::updateOrCreate(
                ['project_id' => $project1->id, 'installment_name' => $plan['installment_name']],
                $plan
            );
        }

        // Project 2: Kazanchis Financial Plaza Residences (Addis Ababa - Kirkos)
        $project2 = Project::updateOrCreate(
            ['slug' => 'kazanchis-financial-plaza-residences'],
            [
                'project_code' => 'ETH-DEV-AA-2026-KZCH',
                'name' => 'Kazanchis Financial Plaza Residences',
                'developer_name' => 'Sunshine Construction & Real Estate',
                'developer_phone' => '+251 91 177 8899',
                'developer_email' => 'info@sunshinerealestate.et',
                'agent_id' => $agent?->id,
                'city' => 'Addis Ababa',
                'subcity' => 'Kirkos',
                'address' => 'Adjacent to UNECA & Commercial Bank Headquarters, Kazanchis',
                'project_type' => 'apartments',
                'construction_status' => 'near_completion',
                'overall_completion_percentage' => 88,
                'expected_completion_date' => 'Q2 2027',
                'land_area_sqm' => 3200.00,
                'total_buildings_count' => 1,
                'total_units_count' => 36,
                'starting_price' => 15800000.00,
                'currency' => 'ETB',
                'description' => 'Ultra-modern business and diplomatic residences directly situated in the diplomatic quarter of Kazanchis. Built to international structural codes with European imported finishes, rooftop terrace lounge, and EV charging stations.',
                'facilities' => [
                    'Standby Generator with Auto-Mains Failure',
                    'Continuous Municipal Water Connection & 150kL Tanks',
                    '2 High-Speed Schindler Elevators',
                    'Rooftop Lounge with UNECA Skyline View',
                    'Smart Video Intercom to Every Unit',
                    'Central Solar Hot Water Heating System',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80',
                ],
                'is_featured' => true,
                'status' => 'published',
            ]
        );

        $buildingPlaza = ProjectBuilding::updateOrCreate(
            ['project_id' => $project2->id, 'name' => 'Main Diplomatic Tower'],
            [
                'total_floors' => 'G+15',
                'units_count' => 36,
                'description' => '15 floors of diplomatic grade 2 & 3 bedroom luxury units.',
            ]
        );

        ProjectUnit::updateOrCreate(
            ['project_id' => $project2->id, 'unit_number' => 'KZ-601'],
            [
                'building_id' => $buildingPlaza->id,
                'floor_number' => '6',
                'unit_type' => '2_bedroom',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqm' => 128.00,
                'price' => 15800000.00,
                'orientation' => 'UNECA Gardens View',
                'parking_spaces' => 1,
                'status' => 'available',
            ]
        );

        ProjectUnit::updateOrCreate(
            ['project_id' => $project2->id, 'unit_number' => 'KZ-902'],
            [
                'building_id' => $buildingPlaza->id,
                'floor_number' => '9',
                'unit_type' => '3_bedroom',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area_sqm' => 180.00,
                'price' => 22400000.00,
                'orientation' => 'Addis Financial District Skyline',
                'parking_spaces' => 2,
                'status' => 'available',
            ]
        );

        ConstructionMilestone::updateOrCreate(
            ['project_id' => $project2->id, 'milestone_name' => 'Civil & Structural Frame'],
            ['percentage' => 100, 'target_date' => 'Completed', 'status' => 'completed', 'order_index' => 1]
        );
        ConstructionMilestone::updateOrCreate(
            ['project_id' => $project2->id, 'milestone_name' => 'Glazing & Facade Installation'],
            ['percentage' => 100, 'target_date' => 'Completed', 'status' => 'completed', 'order_index' => 2]
        );
        ConstructionMilestone::updateOrCreate(
            ['project_id' => $project2->id, 'milestone_name' => 'Interior Carpentry & Tiling'],
            ['percentage' => 85, 'target_date' => 'In Progress', 'status' => 'in_progress', 'order_index' => 3]
        );
        ConstructionMilestone::updateOrCreate(
            ['project_id' => $project2->id, 'milestone_name' => 'Testing, Commissioning & Handover'],
            ['percentage' => 40, 'target_date' => 'Q2 2027', 'status' => 'in_progress', 'order_index' => 4]
        );

        DeveloperPaymentPlan::updateOrCreate(
            ['project_id' => $project2->id, 'installment_name' => '15% Advance Contract Booking'],
            ['percentage' => 15.00, 'milestone_trigger' => 'Signing of Sales Agreement', 'order_index' => 1]
        );
        DeveloperPaymentPlan::updateOrCreate(
            ['project_id' => $project2->id, 'installment_name' => '70% Semi-Annual Construction Milestones'],
            ['percentage' => 70.00, 'milestone_trigger' => 'Split across 4 milestones', 'order_index' => 2]
        );
        DeveloperPaymentPlan::updateOrCreate(
            ['project_id' => $project2->id, 'installment_name' => '15% Key & Title Deed Transfer'],
            ['percentage' => 15.00, 'milestone_trigger' => 'Final Handover', 'order_index' => 3]
        );
    }
}
