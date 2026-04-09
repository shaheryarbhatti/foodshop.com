<?php

namespace Database\Seeders\Concerns;

trait GeoSeedData
{
    protected function countryNames(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", <<<TEXT
Afghanistan
Albania
Algeria
Andorra
Angola
Antigua and Barbuda
Argentina
Armenia
Australia
Austria
Azerbaijan
Bahamas
Bahrain
Bangladesh
Barbados
Belarus
Belgium
Belize
Benin
Bhutan
Bolivia
Bosnia and Herzegovina
Botswana
Brazil
Brunei
Bulgaria
Burkina Faso
Burundi
Cabo Verde
Cambodia
Cameroon
Canada
Central African Republic
Chad
Chile
China
Colombia
Comoros
Congo
Costa Rica
Croatia
Cuba
Cyprus
Czech Republic
Democratic Republic of the Congo
Denmark
Djibouti
Dominica
Dominican Republic
Ecuador
Egypt
El Salvador
Equatorial Guinea
Eritrea
Estonia
Eswatini
Ethiopia
Fiji
Finland
France
Gabon
Gambia
Georgia
Germany
Ghana
Greece
Grenada
Guatemala
Guinea
Guinea-Bissau
Guyana
Haiti
Honduras
Hungary
Iceland
India
Indonesia
Iran
Iraq
Ireland
Israel
Italy
Jamaica
Japan
Jordan
Kazakhstan
Kenya
Kiribati
Kuwait
Kyrgyzstan
Laos
Latvia
Lebanon
Lesotho
Liberia
Libya
Liechtenstein
Lithuania
Luxembourg
Madagascar
Malawi
Malaysia
Maldives
Mali
Malta
Marshall Islands
Mauritania
Mauritius
Mexico
Micronesia
Moldova
Monaco
Mongolia
Montenegro
Morocco
Mozambique
Myanmar
Namibia
Nauru
Nepal
Netherlands
New Zealand
Nicaragua
Niger
Nigeria
North Korea
North Macedonia
Norway
Oman
Pakistan
Palau
Palestine
Panama
Papua New Guinea
Paraguay
Peru
Philippines
Poland
Portugal
Qatar
Romania
Russia
Rwanda
Saint Kitts and Nevis
Saint Lucia
Saint Vincent and the Grenadines
Samoa
San Marino
Sao Tome and Principe
Saudi Arabia
Senegal
Serbia
Seychelles
Sierra Leone
Singapore
Slovakia
Slovenia
Solomon Islands
Somalia
South Africa
South Korea
South Sudan
Spain
Sri Lanka
Sudan
Suriname
Sweden
Switzerland
Syria
Taiwan
Tajikistan
Tanzania
Thailand
Timor-Leste
Togo
Tonga
Trinidad and Tobago
Tunisia
Turkey
Turkmenistan
Tuvalu
Uganda
Ukraine
United Arab Emirates
United Kingdom
United States
Uruguay
Uzbekistan
Vanuatu
Vatican City
Venezuela
Vietnam
Yemen
Zambia
Zimbabwe
TEXT
        ))));
    }

    protected function countryCodeMap(): array
    {
        return [
            'Afghanistan' => 'AF',
            'Albania' => 'AL',
            'Algeria' => 'DZ',
            'Andorra' => 'AD',
            'Angola' => 'AO',
            'Antigua and Barbuda' => 'AG',
            'Argentina' => 'AR',
            'Armenia' => 'AM',
            'Australia' => 'AU',
            'Austria' => 'AT',
            'Azerbaijan' => 'AZ',
            'Bahamas' => 'BS',
            'Bahrain' => 'BH',
            'Bangladesh' => 'BD',
            'Barbados' => 'BB',
            'Belarus' => 'BY',
            'Belgium' => 'BE',
            'Belize' => 'BZ',
            'Benin' => 'BJ',
            'Bhutan' => 'BT',
            'Bolivia' => 'BO',
            'Bosnia and Herzegovina' => 'BA',
            'Botswana' => 'BW',
            'Brazil' => 'BR',
            'Brunei' => 'BN',
            'Bulgaria' => 'BG',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Cabo Verde' => 'CV',
            'Cambodia' => 'KH',
            'Cameroon' => 'CM',
            'Canada' => 'CA',
            'Central African Republic' => 'CF',
            'Chad' => 'TD',
            'Chile' => 'CL',
            'China' => 'CN',
            'Colombia' => 'CO',
            'Comoros' => 'KM',
            'Congo' => 'CG',
            'Costa Rica' => 'CR',
            'Croatia' => 'HR',
            'Cuba' => 'CU',
            'Cyprus' => 'CY',
            'Czech Republic' => 'CZ',
            'Democratic Republic of the Congo' => 'CD',
            'Denmark' => 'DK',
            'Djibouti' => 'DJ',
            'Dominica' => 'DM',
            'Dominican Republic' => 'DO',
            'Ecuador' => 'EC',
            'Egypt' => 'EG',
            'El Salvador' => 'SV',
            'Equatorial Guinea' => 'GQ',
            'Eritrea' => 'ER',
            'Estonia' => 'EE',
            'Eswatini' => 'SZ',
            'Ethiopia' => 'ET',
            'Fiji' => 'FJ',
            'Finland' => 'FI',
            'France' => 'FR',
            'Gabon' => 'GA',
            'Gambia' => 'GM',
            'Georgia' => 'GE',
            'Germany' => 'DE',
            'Ghana' => 'GH',
            'Greece' => 'GR',
            'Grenada' => 'GD',
            'Guatemala' => 'GT',
            'Guinea' => 'GN',
            'Guinea-Bissau' => 'GW',
            'Guyana' => 'GY',
            'Haiti' => 'HT',
            'Honduras' => 'HN',
            'Hungary' => 'HU',
            'Iceland' => 'IS',
            'India' => 'IN',
            'Indonesia' => 'ID',
            'Iran' => 'IR',
            'Iraq' => 'IQ',
            'Ireland' => 'IE',
            'Israel' => 'IL',
            'Italy' => 'IT',
            'Jamaica' => 'JM',
            'Japan' => 'JP',
            'Jordan' => 'JO',
            'Kazakhstan' => 'KZ',
            'Kenya' => 'KE',
            'Kiribati' => 'KI',
            'Kuwait' => 'KW',
            'Kyrgyzstan' => 'KG',
            'Laos' => 'LA',
            'Latvia' => 'LV',
            'Lebanon' => 'LB',
            'Lesotho' => 'LS',
            'Liberia' => 'LR',
            'Libya' => 'LY',
            'Liechtenstein' => 'LI',
            'Lithuania' => 'LT',
            'Luxembourg' => 'LU',
            'Madagascar' => 'MG',
            'Malawi' => 'MW',
            'Malaysia' => 'MY',
            'Maldives' => 'MV',
            'Mali' => 'ML',
            'Malta' => 'MT',
            'Marshall Islands' => 'MH',
            'Mauritania' => 'MR',
            'Mauritius' => 'MU',
            'Mexico' => 'MX',
            'Micronesia' => 'FM',
            'Moldova' => 'MD',
            'Monaco' => 'MC',
            'Mongolia' => 'MN',
            'Montenegro' => 'ME',
            'Morocco' => 'MA',
            'Mozambique' => 'MZ',
            'Myanmar' => 'MM',
            'Namibia' => 'NA',
            'Nauru' => 'NR',
            'Nepal' => 'NP',
            'Netherlands' => 'NL',
            'New Zealand' => 'NZ',
            'Nicaragua' => 'NI',
            'Niger' => 'NE',
            'Nigeria' => 'NG',
            'North Korea' => 'KP',
            'North Macedonia' => 'MK',
            'Norway' => 'NO',
            'Oman' => 'OM',
            'Pakistan' => 'PK',
            'Palau' => 'PW',
            'Palestine' => 'PS',
            'Panama' => 'PA',
            'Papua New Guinea' => 'PG',
            'Paraguay' => 'PY',
            'Peru' => 'PE',
            'Philippines' => 'PH',
            'Poland' => 'PL',
            'Portugal' => 'PT',
            'Qatar' => 'QA',
            'Romania' => 'RO',
            'Russia' => 'RU',
            'Rwanda' => 'RW',
            'Saint Kitts and Nevis' => 'KN',
            'Saint Lucia' => 'LC',
            'Saint Vincent and the Grenadines' => 'VC',
            'Samoa' => 'WS',
            'San Marino' => 'SM',
            'Sao Tome and Principe' => 'ST',
            'Saudi Arabia' => 'SA',
            'Senegal' => 'SN',
            'Serbia' => 'RS',
            'Seychelles' => 'SC',
            'Sierra Leone' => 'SL',
            'Singapore' => 'SG',
            'Slovakia' => 'SK',
            'Slovenia' => 'SI',
            'Solomon Islands' => 'SB',
            'Somalia' => 'SO',
            'South Africa' => 'ZA',
            'South Korea' => 'KR',
            'South Sudan' => 'SS',
            'Spain' => 'ES',
            'Sri Lanka' => 'LK',
            'Sudan' => 'SD',
            'Suriname' => 'SR',
            'Sweden' => 'SE',
            'Switzerland' => 'CH',
            'Syria' => 'SY',
            'Taiwan' => 'TW',
            'Tajikistan' => 'TJ',
            'Tanzania' => 'TZ',
            'Thailand' => 'TH',
            'Timor-Leste' => 'TL',
            'Togo' => 'TG',
            'Tonga' => 'TO',
            'Trinidad and Tobago' => 'TT',
            'Tunisia' => 'TN',
            'Turkey' => 'TR',
            'Turkmenistan' => 'TM',
            'Tuvalu' => 'TV',
            'Uganda' => 'UG',
            'Ukraine' => 'UA',
            'United Arab Emirates' => 'AE',
            'United Kingdom' => 'GB',
            'United States' => 'US',
            'Uruguay' => 'UY',
            'Uzbekistan' => 'UZ',
            'Vanuatu' => 'VU',
            'Vatican City' => 'VA',
            'Venezuela' => 'VE',
            'Vietnam' => 'VN',
            'Yemen' => 'YE',
            'Zambia' => 'ZM',
            'Zimbabwe' => 'ZW',
        ];
    }

    protected function regionMap(): array
    {
        return [
            'Australia' => ['New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia'],
            'Brazil' => ['Sao Paulo', 'Rio de Janeiro', 'Bahia', 'Minas Gerais', 'Parana'],
            'Canada' => ['Ontario', 'Quebec', 'British Columbia', 'Alberta', 'Manitoba'],
            'China' => ['Beijing', 'Shanghai', 'Guangdong', 'Zhejiang', 'Sichuan'],
            'France' => ['Ile-de-France', 'Provence-Alpes-Cote d\'Azur', 'Nouvelle-Aquitaine', 'Occitanie', 'Grand Est'],
            'Germany' => ['Bavaria', 'Berlin', 'Hamburg', 'Hesse', 'North Rhine-Westphalia'],
            'India' => ['Maharashtra', 'Delhi', 'Karnataka', 'Tamil Nadu', 'Gujarat'],
            'Indonesia' => ['Jakarta', 'West Java', 'Central Java', 'East Java', 'Bali'],
            'Italy' => ['Lazio', 'Lombardy', 'Campania', 'Sicily', 'Veneto'],
            'Japan' => ['Tokyo', 'Osaka', 'Kanagawa', 'Aichi', 'Hokkaido'],
            'Malaysia' => ['Selangor', 'Kuala Lumpur', 'Johor', 'Penang', 'Sabah'],
            'Mexico' => ['Mexico City', 'Jalisco', 'Nuevo Leon', 'Puebla', 'Guanajuato'],
            'Netherlands' => ['North Holland', 'South Holland', 'Utrecht', 'North Brabant', 'Gelderland'],
            'Pakistan' => ['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan', 'Islamabad Capital Territory'],
            'Philippines' => ['Metro Manila', 'Cebu', 'Davao del Sur', 'Laguna', 'Pampanga'],
            'Russia' => ['Moscow', 'Saint Petersburg', 'Tatarstan', 'Novosibirsk Oblast', 'Krasnodar Krai'],
            'Saudi Arabia' => ['Riyadh', 'Makkah', 'Madinah', 'Eastern Province', 'Asir'],
            'South Africa' => ['Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Limpopo'],
            'South Korea' => ['Seoul', 'Busan', 'Incheon', 'Gyeonggi', 'Daegu'],
            'Spain' => ['Madrid', 'Catalonia', 'Andalusia', 'Valencian Community', 'Basque Country'],
            'Thailand' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Chonburi', 'Nakhon Ratchasima'],
            'Turkey' => ['Istanbul', 'Ankara', 'Izmir', 'Antalya', 'Bursa'],
            'United Arab Emirates' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah'],
            'United Kingdom' => ['England', 'Scotland', 'Wales', 'Northern Ireland', 'Greater London'],
            'United States' => ['California', 'Texas', 'Florida', 'New York', 'Illinois'],
            'Vietnam' => ['Ho Chi Minh City', 'Hanoi', 'Da Nang', 'Can Tho', 'Hai Phong'],
        ];
    }

    protected function cityMap(): array
    {
        return [
            'Australia' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Canberra', 'Gold Coast', 'Newcastle', 'Geelong', 'Hobart'],
            'Brazil' => ['Sao Paulo', 'Rio de Janeiro', 'Salvador', 'Belo Horizonte', 'Curitiba', 'Fortaleza', 'Manaus', 'Recife', 'Porto Alegre', 'Brasilia'],
            'Canada' => ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Halifax'],
            'China' => ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu', 'Hangzhou', 'Wuhan', 'Nanjing', 'Suzhou', 'Chongqing'],
            'France' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille'],
            'Germany' => ['Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne', 'Stuttgart', 'Dusseldorf', 'Dortmund', 'Leipzig', 'Bremen'],
            'India' => ['Mumbai', 'Delhi', 'Bengaluru', 'Chennai', 'Ahmedabad', 'Hyderabad', 'Pune', 'Kolkata', 'Jaipur', 'Surat'],
            'Indonesia' => ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Denpasar', 'Medan', 'Makassar', 'Yogyakarta', 'Palembang', 'Malang'],
            'Italy' => ['Rome', 'Milan', 'Naples', 'Palermo', 'Venice', 'Florence', 'Bologna', 'Turin', 'Genoa', 'Bari'],
            'Japan' => ['Tokyo', 'Osaka', 'Yokohama', 'Nagoya', 'Sapporo', 'Fukuoka', 'Kobe', 'Kyoto', 'Hiroshima', 'Sendai'],
            'Malaysia' => ['Kuala Lumpur', 'Shah Alam', 'Johor Bahru', 'George Town', 'Kota Kinabalu', 'Ipoh', 'Malacca City', 'Kuching', 'Petaling Jaya', 'Seremban'],
            'Mexico' => ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Leon', 'Tijuana', 'Queretaro', 'Merida', 'Cancun', 'Toluca'],
            'Netherlands' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven', 'Groningen', 'Tilburg', 'Almere', 'Breda', 'Nijmegen'],
            'Pakistan' => ['Lahore', 'Karachi', 'Islamabad', 'Peshawar', 'Quetta', 'Rawalpindi', 'Multan', 'Faisalabad', 'Sialkot', 'Hyderabad'],
            'Philippines' => ['Manila', 'Quezon City', 'Cebu City', 'Davao City', 'Makati', 'Pasig', 'Taguig', 'Baguio', 'Iloilo City', 'Cagayan de Oro'],
            'Russia' => ['Moscow', 'Saint Petersburg', 'Kazan', 'Novosibirsk', 'Sochi', 'Yekaterinburg', 'Krasnodar', 'Vladivostok', 'Samara', 'Rostov-on-Don'],
            'Saudi Arabia' => ['Riyadh', 'Jeddah', 'Makkah', 'Madinah', 'Dammam', 'Khobar', 'Abha', 'Tabuk', 'Taif', 'Jizan'],
            'South Africa' => ['Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth', 'Bloemfontein', 'Polokwane', 'East London', 'Nelspruit', 'Kimberley'],
            'South Korea' => ['Seoul', 'Busan', 'Incheon', 'Daegu', 'Daejeon', 'Gwangju', 'Suwon', 'Ulsan', 'Jeonju', 'Jeju'],
            'Spain' => ['Madrid', 'Barcelona', 'Seville', 'Valencia', 'Bilbao', 'Malaga', 'Zaragoza', 'Alicante', 'Palma', 'Granada'],
            'Thailand' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya', 'Khon Kaen', 'Hat Yai', 'Udon Thani', 'Nakhon Ratchasima', 'Surat Thani', 'Ayutthaya'],
            'Turkey' => ['Istanbul', 'Ankara', 'Izmir', 'Antalya', 'Bursa', 'Adana', 'Gaziantep', 'Konya', 'Trabzon', 'Kayseri'],
            'United Arab Emirates' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Al Ain', 'Umm Al Quwain', 'Khor Fakkan', 'Dibba Al-Fujairah'],
            'United Kingdom' => ['London', 'Manchester', 'Birmingham', 'Glasgow', 'Cardiff', 'Belfast', 'Liverpool', 'Edinburgh', 'Leeds', 'Bristol'],
            'United States' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Miami', 'San Francisco', 'Dallas', 'Seattle', 'Atlanta', 'Boston'],
            'Vietnam' => ['Ho Chi Minh City', 'Hanoi', 'Da Nang', 'Can Tho', 'Hai Phong', 'Hue', 'Nha Trang', 'Vung Tau', 'Da Lat', 'Quy Nhon'],
        ];
    }
}
