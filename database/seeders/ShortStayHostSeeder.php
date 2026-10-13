<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\GuestServiceRequest;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShortStayHostSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $receptionist = User::where('role', 'receptionist')->first();
        $guestUser = User::where('role', 'user')->first() ?? $receptionist;

        // 1. Create short-stay properties
        $bolePenthouse = Property::updateOrCreate(
            ['slug' => 'bole-medhanealem-luxury-penthouse-suite'],
            [
                'title' => 'Bole Medhanealem Luxury Penthouse Suite',
                'type' => 'stay',
                'property_category' => 'serviced_apartment',
                'city' => 'Addis Ababa',
                'address' => 'Bole Medhanealem, Atlas Road',
                'price' => 4500,
                'cleaning_fee' => 600,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area' => 115,
                'check_in_time' => '14:00',
                'check_out_time' => '11:00',
                'is_available' => true,
                'available_from' => $today->copy()->subDays(10),
                'available_to' => $today->copy()->addMonths(6),
                'house_rules' => "1. Valid Ethiopian National ID or International Passport required at check-in.\n2. Strictly no indoor smoking (balcony smoking permitted).\n3. Quiet hours observed between 22:00 and 07:00.\n4. No unregistered overnight visitors.\n5. Pets permitted upon prior confirmation with the front desk.",
                'description' => 'Luxury 2-bedroom executive short-stay serviced apartment in Bole Medhanealem. Features high-speed fiber internet, master ensuite with walk-in shower, fully equipped kitchen, 24/7 backup generator, and round-the-clock front desk reception security.',
                'status' => 'published',
                'is_active' => true,
                'host_id' => $receptionist?->id,
            ]
        );

        $kazanchisSuite = Property::updateOrCreate(
            ['slug' => 'kazanchis-executive-suite-guest-house'],
            [
                'title' => 'Kazanchis Executive Suite & Guest House',
                'type' => 'stay',
                'property_category' => 'guest_house',
                'city' => 'Addis Ababa',
                'address' => 'Kazanchis, Near ECA & UNECA HQ',
                'price' => 3200,
                'cleaning_fee' => 400,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area' => 65,
                'check_in_time' => '13:00',
                'check_out_time' => '11:00',
                'is_available' => true,
                'available_from' => $today->copy()->subDays(5),
                'available_to' => $today->copy()->addMonths(4),
                'house_rules' => "1. Front desk check-in open until 23:00.\n2. Room key cards must be deposited at the front desk when leaving.\n3. Outside catering allowed.",
                'description' => 'Comfortable furnished guest suite in Kazanchis diplomatic district. Ideal for business travelers, UN consultants, and short-stay visitors.',
                'status' => 'published',
                'is_active' => true,
                'host_id' => $receptionist?->id,
            ]
        );

        $villaSuite = Property::updateOrCreate(
            ['slug' => 'old-airport-boutique-villa-suite'],
            [
                'title' => 'Old Airport Boutique Villa Suite',
                'type' => 'stay',
                'property_category' => 'villa',
                'city' => 'Addis Ababa',
                'address' => 'Old Airport, Near Golf Club & ICS',
                'price' => 6800,
                'cleaning_fee' => 900,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'area' => 190,
                'check_in_time' => '15:00',
                'check_out_time' => '12:00',
                'is_available' => true,
                'available_from' => $today->copy(),
                'available_to' => $today->copy()->addMonths(6),
                'house_rules' => "1. Maximum 6 adult guests.\n2. No commercial parties or filming without prior management clearance.\n3. Security deposit collected upon arrival.",
                'description' => 'Exclusive 3-bedroom furnished villa suite with private garden, barbecue terrace, and dedicated staff support.',
                'status' => 'published',
                'is_active' => true,
                'host_id' => $receptionist?->id,
            ]
        );

        // Ensure receptionist users have an assigned short-stay property
        User::where('role', 'receptionist')->each(function (User $u) use ($bolePenthouse) {
            if (empty($u->assigned_property_id)) {
                $u->update(['assigned_property_id' => $bolePenthouse->id]);
            }
        });

        // 2. Seed Bookings across the lifecycle

        // A. PENDING Booking (Awaiting Host Acceptance!)
        Booking::updateOrCreate(
            ['booking_reference' => 'STAY-PENDING-001'],
            [
                'user_id' => $guestUser?->id,
                'property_id' => $bolePenthouse->id,
                'guest_name' => 'Almaz Tadesse',
                'guest_email' => 'almaz.tadesse@example.com',
                'guest_phone' => '+251911445566',
                'check_in_date' => $today->copy()->addDay()->toDateString(),
                'check_out_date' => $today->copy()->addDays(4)->toDateString(),
                'total_nights' => 3,
                'guests_count' => 2,
                'nightly_price' => 4500,
                'cleaning_fee' => 600,
                'accommodation_total' => 13500,
                'total_amount' => 14100,
                'currency' => 'ETB',
                'status' => 'pending',
                'payment_status' => 'pending',
                'room_number' => 'Suite 204',
            ]
        );

        // B. EXPECTED ARRIVAL TODAY (Awaiting Check-in!)
        $arrivalBooking = Booking::updateOrCreate(
            ['booking_reference' => 'STAY-ARRIV-002'],
            [
                'user_id' => $guestUser?->id,
                'property_id' => $bolePenthouse->id,
                'guest_name' => 'Dr. Samuel Kebede',
                'guest_email' => 'samuel.kebede@example.com',
                'guest_phone' => '+251912334455',
                'check_in_date' => $today->toDateString(),
                'check_out_date' => $today->copy()->addDays(3)->toDateString(),
                'total_nights' => 3,
                'guests_count' => 1,
                'nightly_price' => 4500,
                'cleaning_fee' => 600,
                'accommodation_total' => 13500,
                'total_amount' => 14100,
                'currency' => 'ETB',
                'status' => 'confirmed',
                'payment_status' => 'pending',
                'room_number' => 'Penthouse 501',
                'guest_id_number' => 'ET-NAT-882910',
            ]
        );

        // C. IN-HOUSE GUEST (Currently checked-in & staying!)
        $inHouseBooking = Booking::updateOrCreate(
            ['booking_reference' => 'STAY-INHOUSE-003'],
            [
                'user_id' => $guestUser?->id,
                'property_id' => $bolePenthouse->id,
                'guest_name' => 'Marta Haile',
                'guest_email' => 'marta.haile@example.com',
                'guest_phone' => '+251913998877',
                'check_in_date' => $today->copy()->subDays(2)->toDateString(),
                'check_out_date' => $today->copy()->addDays(2)->toDateString(),
                'total_nights' => 4,
                'guests_count' => 2,
                'nightly_price' => 4500,
                'cleaning_fee' => 600,
                'accommodation_total' => 18000,
                'total_amount' => 18600,
                'currency' => 'ETB',
                'status' => 'checked_in',
                'payment_status' => 'paid',
                'room_number' => 'Suite 302',
                'checked_in_at' => $today->copy()->subDays(2)->setHour(14),
                'guest_id_number' => 'PASSPORT-ET92019',
            ]
        );

        // Payment for In-House Guest
        Payment::updateOrCreate(
            ['payable_id' => $inHouseBooking->id, 'payable_type' => Booking::class],
            [
                // DatabaseSeeder runs with WithoutModelEvents, so the Payment
                // creating hook that generates this reference never fires.
                'transaction_number' => 'TXN-ETH-'.date('Y').'-'.strtoupper(substr(uniqid(), -6)),
                'property_id' => $bolePenthouse->id,
                'user_id' => $guestUser?->id,
                'payer_name' => 'Marta Haile',
                'payer_email' => 'marta.haile@example.com',
                'payment_type' => 'booking',
                'currency' => 'ETB',
                'amount' => 18600,
                'net_amount' => 18600,
                'payment_method' => 'telebirr',
                'bank_transaction_reference' => 'TB-'.strtoupper(Str::random(8)),
                'status' => 'confirmed',
                'verified_at' => now(),
            ]
        );

        // D. EXPECTED DEPARTURE TODAY (Checked-in, checking out today!)
        $departureBooking = Booking::updateOrCreate(
            ['booking_reference' => 'STAY-DEPART-004'],
            [
                'user_id' => $guestUser?->id,
                'property_id' => $bolePenthouse->id,
                'guest_name' => 'Henok Tesfaye',
                'guest_email' => 'henok.tesfaye@example.com',
                'guest_phone' => '+251914556677',
                'check_in_date' => $today->copy()->subDays(3)->toDateString(),
                'check_out_date' => $today->toDateString(),
                'total_nights' => 3,
                'guests_count' => 1,
                'nightly_price' => 4500,
                'cleaning_fee' => 600,
                'accommodation_total' => 13500,
                'total_amount' => 14100,
                'currency' => 'ETB',
                'status' => 'checked_in',
                'payment_status' => 'paid',
                'room_number' => 'Suite 104',
                'checked_in_at' => $today->copy()->subDays(3)->setHour(15),
                'guest_id_number' => 'ET-KEBELE-19284',
            ]
        );

        // 3. Seed Cleaning / Housekeeping Requests
        GuestServiceRequest::updateOrCreate(
            ['room_number' => 'Suite 104', 'service_type' => 'room_cleaning'],
            [
                'booking_id' => $departureBooking->id,
                'property_id' => $bolePenthouse->id,
                'guest_name' => 'Host Housekeeping',
                'priority' => 'urgent',
                'description' => 'Turnover cleaning required following Henok checkout: deep sanitization, replace duvet and bedsheets, restock toiletries.',
                'status' => 'pending',
                'handled_by' => $receptionist?->id,
            ]
        );

        GuestServiceRequest::updateOrCreate(
            ['room_number' => 'Suite 302', 'service_type' => 'extra_towels'],
            [
                'booking_id' => $inHouseBooking->id,
                'property_id' => $bolePenthouse->id,
                'guest_name' => 'Marta Haile',
                'priority' => 'normal',
                'description' => 'Guest requested two extra bath towels and fresh shower gel.',
                'status' => 'in_progress',
                'handled_by' => $receptionist?->id,
            ]
        );

        // 4. Seed Guest Reviews (One with Host response, one awaiting host response!)
        PropertyReview::updateOrCreate(
            ['review_title' => 'Exceptional Penthouse & Attentive Desk Staff'],
            [
                'property_id' => $bolePenthouse->id,
                'user_id' => $guestUser?->id,
                'booking_id' => $inHouseBooking->id,
                'rating' => 5,
                'cleanliness_rating' => 5,
                'accuracy_rating' => 5,
                'communication_rating' => 5,
                'location_rating' => 5,
                'value_rating' => 5,
                'review_body' => 'Spotless apartment with fast internet and stunning mountain views. Front desk receptionist handled check-in effortlessly and answered all questions about Addis.',
                'is_verified_stay' => true,
                'status' => 'published',
                'host_reply' => null, // Awaiting host response!
            ]
        );

        PropertyReview::updateOrCreate(
            ['review_title' => 'Peaceful stay in the heart of Bole'],
            [
                'property_id' => $bolePenthouse->id,
                'user_id' => $guestUser?->id,
                'booking_id' => $departureBooking->id,
                'rating' => 5,
                'cleanliness_rating' => 5,
                'accuracy_rating' => 5,
                'communication_rating' => 5,
                'location_rating' => 5,
                'value_rating' => 4,
                'review_body' => 'Great location near Bole restaurants and supermarkets. Generator kicked in seamlessly during power outage.',
                'is_verified_stay' => true,
                'status' => 'published',
                'host_reply' => 'Thank you Henok! We are thrilled you enjoyed the stay and the convenience of our Bole location. Safe travels and we hope to host you again soon.',
                'host_replied_at' => now()->subDay(),
            ]
        );

        // 5. Seed Guest Inquiries
        Inquiry::updateOrCreate(
            ['email' => 'elias.tourist@example.com', 'subject' => 'Late check-out & airport shuttle inquiry'],
            [
                'name' => 'Elias Kassa',
                'phone' => '+251920112233',
                'message' => 'Hello Front Desk! I am arriving late at Bole International Airport (23:30). Can I request a late check-in and an airport pickup shuttle for 2 guests?',
                'status' => 'unread',
            ]
        );
    }
}
