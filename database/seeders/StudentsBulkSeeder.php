<?php

namespace Database\Seeders;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentsBulkSeeder extends Seeder
{
    private array $firstNames = [
        'Aisling', 'Aoife', 'Caoimhe', 'Ciara', 'Eimear', 'Fiona', 'Grainne', 'Niamh', 'Orla', 'Roisin',
        'Sinead', 'Siobhan', 'Emma', 'Sarah', 'Amy', 'Katie', 'Rebecca', 'Laura', 'Sophie', 'Rachel',
        'Chloe', 'Megan', 'Jessica', 'Hannah', 'Leah', 'Grace', 'Ella', 'Emily', 'Lucy', 'Zoe',
        'Aiden', 'Cian', 'Conor', 'Darragh', 'Eoin', 'Fionn', 'Oisin', 'Ruairi', 'Seamus', 'Tadhg',
        'James', 'Jack', 'Daniel', 'Michael', 'David', 'Adam', 'Ryan', 'Sean', 'Luke', 'Aaron',
        'Ben', 'Cillian', 'Dylan', 'Evan', 'Finn', 'Harry', 'Ian', 'Josh', 'Kai', 'Liam',
        'Mark', 'Nathan', 'Owen', 'Paul', 'Quinn', 'Ross', 'Sam', 'Tom', 'Will', 'Zach'
    ];

    private array $lastNames = [
        'Murphy', 'Kelly', 'O\'Sullivan', 'Walsh', 'Smith', 'O\'Brien', 'Byrne', 'O\'Connor', 'Ryan', 'O\'Neill',
        'O\'Reilly', 'Doyle', 'McCarthy', 'Gallagher', 'O\'Doherty', 'Kennedy', 'Lynch', 'Murray', 'Quinn', 'Moore',
        'McLaughlin', 'O\'Carroll', 'Connolly', 'Daly', 'O\'Connell', 'Wilson', 'Dunne', 'Clarke', 'Devlin', 'Magee',
        'Brennan', 'Burke', 'Fitzgerald', 'Leahy', 'McDonnell', 'Nolan', 'Fleming', 'Power', 'Healy', 'Keane',
        'Barry', 'Collins', 'Kavanagh', 'O\'Mahony', 'Higgins', 'Casey', 'Foley', 'Griffin', 'Hayes', 'O\'Shea'
    ];

    private array $counties = [
        'Dublin', 'Cork', 'Galway', 'Mayo', 'Donegal', 'Kerry', 'Tipperary', 'Clare', 'Tyrone', 'Antrim',
        'Limerick', 'Roscommon', 'Down', 'Wexford', 'Meath', 'Londonderry', 'Kilkenny', 'Wicklow', 'Offaly',
        'Cavan', 'Waterford', 'Westmeath', 'Sligo', 'Laois', 'Kildare', 'Fermanagh', 'Leitrim', 'Armagh',
        'Monaghan', 'Longford', 'Carlow', 'Louth'
    ];

    private array $cityCountyMap = [
        'Dublin' => ['Dublin', 'Blanchardstown', 'Tallaght', 'Swords', 'Dun Laoghaire'],
        'Cork' => ['Cork', 'Blackpool', 'Ballincollig', 'Carrigaline', 'Cobh'],
        'Galway' => ['Galway', 'Tuam', 'Ballinasloe', 'Loughrea', 'Gort'],
        'Mayo' => ['Castlebar', 'Ballina', 'Westport', 'Claremorris', 'Belmullet'],
        'Kerry' => ['Tralee', 'Killarney', 'Listowel', 'Dingle', 'Kenmare'],
        'Limerick' => ['Limerick', 'Newcastle West', 'Abbeyfeale', 'Rathkeale', 'Kilmallock']
    ];

    private array $phoneAreaCodes = ['085', '086', '087', '089', '083'];

    private array $streetNames = [
        'Main Street', 'Church Street', 'Mill Street', 'Bridge Street', 'High Street', 'Castle Street',
        'Patrick Street', 'Market Street', 'Water Street', 'Abbey Street', 'Station Road', 'Chapel Street',
        'School Street', 'New Street', 'Old Street', 'King Street', 'Queen Street', 'Park Road',
        'Hill Road', 'Grove Road', 'Oak Avenue', 'Elm Avenue', 'Cedar Avenue', 'Pine Avenue',
        'Maple Drive', 'Willow Close', 'Birch Lane', 'Ashwood Estate', 'Riverside Drive', 'Valley View'
    ];

    public function run(): void
    {
        $this->command->info('Creating 100 students...');

        for ($i = 1; $i <= 100; $i++) {
            $firstName = $this->getRandomElement($this->firstNames);
            $lastName = $this->getRandomElement($this->lastNames);
            $county = $this->getRandomElement($this->counties);
            $city = $this->getCityForCounty($county);
            
            Student::firstOrCreate([
                'email' => $this->generateEmail($firstName, $lastName, $i)
            ], [
                'student_number' => Student::generateStudentNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $this->generatePhoneNumber(),
                'address' => $this->generateAddress(),
                'city' => $city,
                'county' => $county,
                'eircode' => $this->generateEircode($county),
                'date_of_birth' => $this->generateDateOfBirth(),
                'status' => 'active',
            ]);

            if ($i % 10 == 0) {
                $this->command->info("Created {$i} students...");
            }
        }

        $this->command->info('Successfully created 100 students!');
    }

    private function getRandomElement(array $array): string
    {
        return $array[array_rand($array)];
    }

    private function getCityForCounty(string $county): string
    {
        if (isset($this->cityCountyMap[$county])) {
            return $this->getRandomElement($this->cityCountyMap[$county]);
        }
        
        // For counties not in the map, use the county name as city
        return $county;
    }

    private function generateEmail(string $firstName, string $lastName, int $index): string
    {
        $cleanFirstName = strtolower(str_replace(['\'', ' '], '', $firstName));
        $cleanLastName = strtolower(str_replace(['\'', ' '], '', $lastName));
        
        $patterns = [
            "{$cleanFirstName}.{$cleanLastName}@student.ie",
            "{$cleanFirstName}{$cleanLastName}@student.ie",
            "{$cleanFirstName}.{$cleanLastName}{$index}@student.ie",
            "{substr($cleanFirstName, 0, 1)}{$cleanLastName}@student.ie"
        ];
        
        return $patterns[array_rand($patterns)];
    }

    private function generatePhoneNumber(): string
    {
        $areaCode = $this->getRandomElement($this->phoneAreaCodes);
        $number = str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        return $areaCode . $number;
    }

    private function generateAddress(): string
    {
        $houseNumber = rand(1, 999);
        $streetName = $this->getRandomElement($this->streetNames);
        return "{$houseNumber} {$streetName}";
    }

    private function generateEircode(string $county): string
    {
        // Simple eircode pattern based on county
        $countyPrefixes = [
            'Dublin' => ['D01', 'D02', 'D03', 'D04', 'D06', 'D07', 'D08', 'D09', 'D10', 'D11', 'D12', 'D13', 'D14', 'D15', 'D16', 'D17', 'D18', 'D20', 'D22', 'D24'],
            'Cork' => ['T12', 'T23', 'T45', 'T56'],
            'Galway' => ['H54', 'H62', 'H71', 'H91'],
            'Kerry' => ['V92', 'V93', 'V94', 'V95'],
            'Limerick' => ['V35', 'V42', 'V47', 'V94'],
            'Mayo' => ['F12', 'F23', 'F26', 'F28']
        ];
        
        if (isset($countyPrefixes[$county])) {
            $prefix = $this->getRandomElement($countyPrefixes[$county]);
        } else {
            // Default pattern for other counties
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $numbers = '0123456789';
            $prefix = $letters[rand(0, 25)] . $numbers[rand(0, 9)] . $numbers[rand(0, 9)];
        }
        
        $suffix = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        return "{$prefix} {$suffix}";
    }

    private function generateDateOfBirth(): Carbon
    {
        // Generate ages between 18-65
        $yearsAgo = rand(18, 65);
        $month = rand(1, 12);
        $day = rand(1, 28); // Safe day for all months
        
        return Carbon::now()->subYears($yearsAgo)->setMonth($month)->setDay($day);
    }
}