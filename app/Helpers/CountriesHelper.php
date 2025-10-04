<?php

namespace App\Helpers;

class CountriesHelper
{
    /**
     * Get list of countries with Turkey as default.
     */
    public static function getCountries(): array
    {
        return [
            'Turkey' => 'Turkey',
            'Canada' => 'Canada',
            'United States' => 'United States',
            'United Kingdom' => 'United Kingdom',
            'Australia' => 'Australia',
            'Germany' => 'Germany',
            'France' => 'France',
            'Italy' => 'Italy',
            'Spain' => 'Spain',
            'Netherlands' => 'Netherlands',
            'Sweden' => 'Sweden',
            'Norway' => 'Norway',
            'Denmark' => 'Denmark',
            'Finland' => 'Finland',
            'Switzerland' => 'Switzerland',
            'Austria' => 'Austria',
            'Belgium' => 'Belgium',
            'Ireland' => 'Ireland',
            'New Zealand' => 'New Zealand',
            'Japan' => 'Japan',
            'South Korea' => 'South Korea',
            'Singapore' => 'Singapore',
            'Malaysia' => 'Malaysia',
            'India' => 'India',
            'China' => 'China',
            'Brazil' => 'Brazil',
            'Argentina' => 'Argentina',
            'Mexico' => 'Mexico',
            'South Africa' => 'South Africa',
            'Egypt' => 'Egypt',
            'UAE' => 'United Arab Emirates',
            'Saudi Arabia' => 'Saudi Arabia',
            'Qatar' => 'Qatar',
            'Kuwait' => 'Kuwait',
            'Bahrain' => 'Bahrain',
            'Oman' => 'Oman',
        ];
    }

    /**
     * Get cities for Turkey.
     */
    public static function getTurkishCities(): array
    {
        return [
            'Adana' => 'Adana',
            'Adıyaman' => 'Adıyaman',
            'Afyonkarahisar' => 'Afyonkarahisar',
            'Ağrı' => 'Ağrı',
            'Aksaray' => 'Aksaray',
            'Amasya' => 'Amasya',
            'Ankara' => 'Ankara',
            'Antalya' => 'Antalya',
            'Ardahan' => 'Ardahan',
            'Artvin' => 'Artvin',
            'Aydın' => 'Aydın',
            'Balıkesir' => 'Balıkesir',
            'Bartın' => 'Bartın',
            'Batman' => 'Batman',
            'Bayburt' => 'Bayburt',
            'Bilecik' => 'Bilecik',
            'Bingöl' => 'Bingöl',
            'Bitlis' => 'Bitlis',
            'Bolu' => 'Bolu',
            'Burdur' => 'Burdur',
            'Bursa' => 'Bursa',
            'Çanakkale' => 'Çanakkale',
            'Çankırı' => 'Çankırı',
            'Çorum' => 'Çorum',
            'Denizli' => 'Denizli',
            'Diyarbakır' => 'Diyarbakır',
            'Düzce' => 'Düzce',
            'Edirne' => 'Edirne',
            'Elazığ' => 'Elazığ',
            'Erzincan' => 'Erzincan',
            'Erzurum' => 'Erzurum',
            'Eskişehir' => 'Eskişehir',
            'Gaziantep' => 'Gaziantep',
            'Giresun' => 'Giresun',
            'Gümüşhane' => 'Gümüşhane',
            'Hakkâri' => 'Hakkâri',
            'Hatay' => 'Hatay',
            'Iğdır' => 'Iğdır',
            'Isparta' => 'Isparta',
            'İstanbul' => 'İstanbul',
            'İzmir' => 'İzmir',
            'Kahramanmaraş' => 'Kahramanmaraş',
            'Karabük' => 'Karabük',
            'Karaman' => 'Karaman',
            'Kars' => 'Kars',
            'Kastamonu' => 'Kastamonu',
            'Kayseri' => 'Kayseri',
            'Kırıkkale' => 'Kırıkkale',
            'Kırklareli' => 'Kırklareli',
            'Kırşehir' => 'Kırşehir',
            'Kilis' => 'Kilis',
            'Kocaeli' => 'Kocaeli',
            'Konya' => 'Konya',
            'Kütahya' => 'Kütahya',
            'Malatya' => 'Malatya',
            'Manisa' => 'Manisa',
            'Mardin' => 'Mardin',
            'Mersin (İçel)' => 'Mersin (İçel)',
            'Muğla' => 'Muğla',
            'Muş' => 'Muş',
            'Nevşehir' => 'Nevşehir',
            'Niğde' => 'Niğde',
            'Ordu' => 'Ordu',
            'Osmaniye' => 'Osmaniye',
            'Rize' => 'Rize',
            'Sakarya' => 'Sakarya',
            'Samsun' => 'Samsun',
            'Siirt' => 'Siirt',
            'Sinop' => 'Sinop',
            'Sivas' => 'Sivas',
            'Şanlıurfa' => 'Şanlıurfa',
            'Şırnak' => 'Şırnak',
            'Tekirdağ' => 'Tekirdağ',
            'Tokat' => 'Tokat',
            'Trabzon' => 'Trabzon',
            'Tunceli' => 'Tunceli',
            'Uşak' => 'Uşak',
            'Van' => 'Van',
            'Yalova' => 'Yalova',
            'Yozgat' => 'Yozgat',
            'Zonguldak' => 'Zonguldak',
        ];
    }

    /**
     * Get cities for a specific country.
     */
    public static function getCitiesForCountry(string $country): array
    {
        return match ($country) {
            'Turkey' => self::getTurkishCities(),
            default => [],
        };
    }
}
