<?php


class Country
{
    public const COUNTRIES = array('AF' => 'Afghanistan', 'AX' => 'Aland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua And Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia And Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CD' => 'Congo, Democratic Republic', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CI' => 'Cote D\'Ivoire', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island & Mcdonald Islands', 'VA' => 'Holy See (Vatican City State)', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran, Islamic Republic Of', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle Of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KR' => 'Korea', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Lao People\'s Democratic Republic', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libyan Arab Jamahiriya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States Of', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory, Occupied', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts And Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre And Miquelon', 'VC' => 'Saint Vincent And Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome And Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia And Sandwich Isl.', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard And Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad And Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks And Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam', 'VG' => 'Virgin Islands, British', 'VI' => 'Virgin Islands, U.S.', 'WF' => 'Wallis And Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');

    /*
    * Map a two-letter continent code onto the name of the continent.
    */
    public const CONTINENTS = array(
        "AS" => "Asia",
        "AN" => "Antarctica",
        "AF" => "Africa",
        "SA" => "South America",
        "EU" => "Europe",
        "OC" => "Oceania",
        "NA" => "North America"
    );

    /*
    * Map a two-letter country code onto the country's two-letter continent code.
    */
    public const COUNTRY_CONTINENTS = array("AF" => "AS", "AX" => "EU", "AL" => "EU", "DZ" => "AF", "AS" => "OC", "AD" => "EU", "AO" => "AF", "AI" => "NA", "AQ" => "AN", "AG" => "NA", "AR" => "SA", "AM" => "AS", "AW" => "NA", "AU" => "OC", "AT" => "EU", "AZ" => "AS", "BS" => "NA", "BH" => "AS", "BD" => "AS", "BB" => "NA", "BY" => "EU", "BE" => "EU", "BZ" => "NA", "BJ" => "AF", "BM" => "NA", "BT" => "AS", "BO" => "SA", "BA" => "EU", "BW" => "AF", "BV" => "AN", "BR" => "SA", "IO" => "AS", "BN" => "AS", "BG" => "EU", "BF" => "AF", "BI" => "AF", "KH" => "AS", "CM" => "AF", "CA" => "NA", "CV" => "AF", "KY" => "NA", "CF" => "AF", "TD" => "AF", "CL" => "SA", "CN" => "AS", "CX" => "AS", "CC" => "AS", "CO" => "SA", "KM" => "AF", "CD" => "AF", "CG" => "AF", "CK" => "OC", "CR" => "NA", "CI" => "AF", "HR" => "EU", "CU" => "NA", "CY" => "AS", "CZ" => "EU", "DK" => "EU", "DJ" => "AF", "DM" => "NA", "DO" => "NA", "EC" => "SA", "EG" => "AF", "SV" => "NA", "GQ" => "AF", "ER" => "AF", "EE" => "EU", "ET" => "AF", "FO" => "EU", "FK" => "SA", "FJ" => "OC", "FI" => "EU", "FR" => "EU", "GF" => "SA", "PF" => "OC", "TF" => "AN", "GA" => "AF", "GM" => "AF", "GE" => "AS", "DE" => "EU", "GH" => "AF", "GI" => "EU", "GR" => "EU", "GL" => "NA", "GD" => "NA", "GP" => "NA", "GU" => "OC", "GT" => "NA", "GG" => "EU", "GN" => "AF", "GW" => "AF", "GY" => "SA", "HT" => "NA", "HM" => "AN", "VA" => "EU", "HN" => "NA", "HK" => "AS", "HU" => "EU", "IS" => "EU", "IN" => "AS", "ID" => "AS", "IR" => "AS", "IQ" => "AS", "IE" => "EU", "IM" => "EU", "IL" => "AS", "IT" => "EU", "JM" => "NA", "JP" => "AS", "JE" => "EU", "JO" => "AS", "KZ" => "AS", "KE" => "AF", "KI" => "OC", "KP" => "AS", "KR" => "AS", "KW" => "AS", "KG" => "AS", "LA" => "AS", "LV" => "EU", "LB" => "AS", "LS" => "AF", "LR" => "AF", "LY" => "AF", "LI" => "EU", "LT" => "EU", "LU" => "EU", "MO" => "AS", "MK" => "EU", "MG" => "AF", "MW" => "AF", "MY" => "AS", "MV" => "AS", "ML" => "AF", "MT" => "EU", "MH" => "OC", "MQ" => "NA", "MR" => "AF", "MU" => "AF", "YT" => "AF", "MX" => "NA", "FM" => "OC", "MD" => "EU", "MC" => "EU", "MN" => "AS", "ME" => "EU", "MS" => "NA", "MA" => "AF", "MZ" => "AF", "MM" => "AS", "NA" => "AF", "NR" => "OC", "NP" => "AS", "AN" => "NA", "NL" => "EU", "NC" => "OC", "NZ" => "OC", "NI" => "NA", "NE" => "AF", "NG" => "AF", "NU" => "OC", "NF" => "OC", "MP" => "OC", "NO" => "EU", "OM" => "AS", "PK" => "AS", "PW" => "OC", "PS" => "AS", "PA" => "NA", "PG" => "OC", "PY" => "SA", "PE" => "SA", "PH" => "AS", "PN" => "OC", "PL" => "EU", "PT" => "EU", "PR" => "NA", "QA" => "AS", "RE" => "AF", "RO" => "EU", "RU" => "EU", "RW" => "AF", "SH" => "AF", "KN" => "NA", "LC" => "NA", "PM" => "NA", "VC" => "NA", "WS" => "OC", "SM" => "EU", "ST" => "AF", "SA" => "AS", "SN" => "AF", "RS" => "EU", "SC" => "AF", "SL" => "AF", "SG" => "AS", "SK" => "EU", "SI" => "EU", "SB" => "OC", "SO" => "AF", "ZA" => "AF", "GS" => "AN", "ES" => "EU", "LK" => "AS", "SD" => "AF", "SR" => "SA", "SJ" => "EU", "SZ" => "AF", "SE" => "EU", "CH" => "EU", "SY" => "AS", "TW" => "AS", "TJ" => "AS", "TZ" => "AF", "TH" => "AS", "TL" => "AS", "TG" => "AF", "TK" => "OC", "TO" => "OC", "TT" => "NA", "TN" => "AF", "TR" => "AS", "TM" => "AS", "TC" => "NA", "TV" => "OC", "UG" => "AF", "UA" => "EU", "AE" => "AS", "GB" => "EU", "UM" => "OC", "US" => "NA", "UY" => "SA", "UZ" => "AS", "VU" => "OC", "VE" => "SA", "VN" => "AS", "VG" => "NA", "VI" => "NA", "WF" => "OC", "EH" => "AF", "YE" => "AS", "ZM" => "AF", "ZW" => "AF");

    public static function get($code)
    {
        return Country::COUNTRIES[$code] ?? 'unknown';
    }

    public static function countryToContinent($code){
        $cont = Country::COUNTRY_CONTINENTS[$code] ?? '';
        return Country::CONTINENTS[$cont] ?? '';
    }
}
