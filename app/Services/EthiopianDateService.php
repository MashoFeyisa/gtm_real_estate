<?php

namespace App\Services;

use Carbon\Carbon;
use DateTimeInterface;

class EthiopianDateService
{
    /**
     * Julian Day Number for Ethiopian Calendar Epoch (Meskerem 1, 1 E.C. = August 29, 8 AD).
     */
    protected const JD_ETHIOPIAN_EPOCH = 1724221;

    /**
     * Month names in Amharic (አማርኛ).
     *
     * @var array<int, string>
     */
    public const MONTHS_AM = [
        1 => 'መስከረም',
        2 => 'ጥቅምት',
        3 => 'ህዳር',
        4 => 'ታህሳስ',
        5 => 'ጥር',
        6 => 'የካቲት',
        7 => 'መጋቢት',
        8 => 'ሚያዝያ',
        9 => 'ግንቦት',
        10 => 'ሰኔ',
        11 => 'ሐምሌ',
        12 => 'ነሐሴ',
        13 => 'ጳጉሜ',
    ];

    /**
     * Month names in Afan Oromo.
     *
     * @var array<int, string>
     */
    public const MONTHS_OM = [
        1 => 'Fulbaana',
        2 => 'Onkololeessa',
        3 => 'Sadaasa',
        4 => 'Muddee',
        5 => 'Amajjii',
        6 => 'Guraandhala',
        7 => 'Bitootessa',
        8 => 'Ebla',
        9 => 'Caamsaa',
        10 => 'Waxabajjii',
        11 => 'Adooleessa',
        12 => 'Hagayya',
        13 => 'Qaammee',
    ];

    /**
     * Month names in English (Phonetic Transliteration).
     *
     * @var array<int, string>
     */
    public const MONTHS_EN = [
        1 => 'Meskerem',
        2 => 'Tikimt',
        3 => 'Hidar',
        4 => 'Tahsas',
        5 => 'Tir',
        6 => 'Yekatit',
        7 => 'Megabit',
        8 => 'Miyazya',
        9 => 'Ginbot',
        10 => 'Sene',
        11 => 'Hamle',
        12 => 'Nehase',
        13 => 'Pagumen',
    ];

    /**
     * Convert Gregorian date to Julian Day Number.
     */
    public static function gregorianToJd(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;

        return $day + intdiv(153 * $m + 2, 5) + 365 * $y + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;
    }

    /**
     * Convert Julian Day Number to Gregorian date array [year, month, day].
     *
     * @return array{0: int, 1: int, 2: int}
     */
    public static function jdToGregorian(int $jd): array
    {
        $l = $jd + 68569;
        $n = intdiv(4 * $l, 146097);
        $l = $l - intdiv(146097 * $n + 3, 4);
        $i = intdiv(4000 * ($l + 1), 1461001);
        $l = $l - intdiv(1461 * $i, 4) + 31;
        $j = intdiv(80 * $l, 2447);
        $d = $l - intdiv(2447 * $j, 80);
        $l = intdiv($j, 11);
        $m = $j + 2 - 12 * $l;
        $y = 100 * ($n - 49) + $i + $l;

        return [$y, $m, $d];
    }

    /**
     * Convert Gregorian date to Ethiopian date representation.
     *
     * @return array{year: int, month: int, day: int, month_name: string, formatted: string}
     */
    public function toEthiopian(Carbon|DateTimeInterface|string $date, string $locale = 'am'): array
    {
        $cDate = is_string($date) ? Carbon::parse($date) : Carbon::instance($date);

        $jd = self::gregorianToJd($cDate->year, $cDate->month, $cDate->day);
        $r = ($jd - self::JD_ETHIOPIAN_EPOCH) % 1461;
        $n = ($r % 365) + 365 * intdiv($r, 1460);

        $ethYear = 4 * intdiv($jd - self::JD_ETHIOPIAN_EPOCH, 1461) + intdiv($r, 365) - intdiv($r, 1460) + 1;
        $ethMonth = intdiv($n, 30) + 1;
        $ethDay = ($n % 30) + 1;

        $monthName = match ($locale) {
            'om' => self::MONTHS_OM[$ethMonth] ?? 'Fulbaana',
            'en' => self::MONTHS_EN[$ethMonth] ?? 'Meskerem',
            default => self::MONTHS_AM[$ethMonth] ?? 'መስከረም',
        };

        return [
            'year' => $ethYear,
            'month' => $ethMonth,
            'day' => $ethDay,
            'month_name' => $monthName,
            'formatted' => sprintf('%s %d, %d', $monthName, $ethDay, $ethYear),
        ];
    }

    /**
     * Convert Ethiopian date (Year, Month, Day) to Gregorian Carbon instance.
     */
    public function toGregorian(int $ethYear, int $ethMonth, int $ethDay): Carbon
    {
        $jd = (self::JD_ETHIOPIAN_EPOCH - 1) + 365 * ($ethYear - 1) + intdiv($ethYear, 4) + 30 * ($ethMonth - 1) + $ethDay;
        [$gYear, $gMonth, $gDay] = self::jdToGregorian($jd);

        return Carbon::createFromDate($gYear, $gMonth, $gDay)->startOfDay();
    }

    /**
     * Format a Gregorian date as an Ethiopian date string.
     */
    public function formatEthiopian(Carbon|DateTimeInterface|string $date, string $locale = 'am'): string
    {
        $converted = $this->toEthiopian($date, $locale);

        return $converted['formatted'];
    }

    /**
     * Retrieve localized month names catalog.
     *
     * @return array<int, string>
     */
    public function getMonthNames(string $locale = 'am'): array
    {
        return match ($locale) {
            'om' => self::MONTHS_OM,
            'en' => self::MONTHS_EN,
            default => self::MONTHS_AM,
        };
    }
}
