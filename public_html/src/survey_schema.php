<?php

declare(strict_types=1);

function yes_no_options(): array
{
    return [
        'Yes' => 'Yes',
        'No' => 'No',
    ];
}

function household_characteristic_fields(): array
{
    return [
        'family_size' => ['label' => 'Family size', 'type' => 'number', 'min' => '0'],
        'family_type' => ['label' => 'Family type', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Nuclear' => 'Nuclear',
            'Joint' => 'Joint',
        ]],
        'family_expenditure' => ['label' => 'Family expenditure', 'type' => 'text'],
        'family_savings' => ['label' => 'Family savings', 'type' => 'text'],
        'house_type' => ['label' => 'House type', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Pucca' => 'Pucca',
            'Semi-pucca' => 'Semi-pucca',
            'Kutcha' => 'Kutcha',
        ]],
        'latrine_type' => ['label' => 'Latrine type', 'type' => 'select', 'options' => [
            '' => 'Select',
            'No latrine' => 'No latrine',
            'Water seal' => 'Water seal',
            'Other latrine' => 'Other latrine',
        ]],
        'drainage_type' => ['label' => 'Drainage type', 'type' => 'select', 'options' => [
            '' => 'Select',
            'No drainage' => 'No drainage',
            'Open kutcha' => 'Open kutcha',
            'Open pucca' => 'Open pucca',
            'Covered pucca' => 'Covered pucca',
        ]],
        'water_stagnation' => ['label' => 'Water stagnation', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Present' => 'Present',
            'Absent' => 'Absent',
        ]],
        'waste_segregation' => ['label' => 'Waste segregation', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Done' => 'Done',
            'Not done' => 'Not done',
        ]],
        'drinking_water_source' => ['label' => 'Drinking water source', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Piped water' => 'Piped water',
            'Tubewell' => 'Tubewell',
            'Pucca well' => 'Pucca well',
            'Other' => 'Other',
        ]],
        'domestic_water_source' => ['label' => 'Domestic water source', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Piped water' => 'Piped water',
            'Tubewell' => 'Tubewell',
            'Pucca well' => 'Pucca well',
            'Pond' => 'Pond',
            'River / Canal' => 'River / Canal',
            'Other' => 'Other',
        ]],
        'cooking_fuel' => ['label' => 'Main cooking fuel', 'type' => 'select', 'options' => [
            '' => 'Select',
            'Coal' => 'Coal',
            'Firewood and chips' => 'Firewood and chips',
            'LPG' => 'LPG',
            'Gobar gas' => 'Gobar gas',
            'Dung cake' => 'Dung cake',
            'Kerosene' => 'Kerosene',
            'Electricity' => 'Electricity',
            'Other' => 'Other',
            'No cooking arrangement' => 'No cooking arrangement',
        ]],
        'living_rooms' => ['label' => 'No. of living rooms', 'type' => 'number', 'min' => '0'],
        'floor_space' => ['label' => 'Floor space (sq ft)', 'type' => 'text'],
    ];
}

function survey_modules(): array
{
    return [
        'household_members' => [
            'title' => '3. Household members',
            'description' => 'List each household member and capture relation, education, occupation, and marital status.',
            'fields' => [
                ['name' => 'sl_no', 'label' => 'Sl no', 'type' => 'number', 'min' => '0'],
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'age', 'label' => 'Age', 'type' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => ['data-member-age' => '1']],
                ['name' => 'sex', 'label' => 'Sex', 'type' => 'select', 'options' => [
                    '' => 'Select',
                    'M' => 'Male',
                    'F' => 'Female',
                    'Trans' => 'Trans',
                ]],
                ['name' => 'relation_to_head', 'label' => 'Relation to head', 'type' => 'text'],
                ['name' => 'relation_to_mother', 'label' => 'Relation to mother', 'type' => 'text', 'attributes' => ['data-relation-to-mother' => '1']],
                ['name' => 'education', 'label' => 'Education', 'type' => 'text'],
                ['name' => 'occupation', 'label' => 'Occupation', 'type' => 'text'],
                ['name' => 'marital_status', 'label' => 'Marital status', 'type' => 'text'],
                ['name' => 'age_at_marriage', 'label' => 'Age at marriage', 'type' => 'number', 'min' => '0'],
            ],
        ],
        'live_births' => [
            'title' => '4. Live births in the last 1 year',
            'description' => 'Enter each live birth and delivery details from the household.',
            'fields' => [
                ['name' => 'mother_name', 'label' => 'Mother name', 'type' => 'text'],
                ['name' => 'mother_age', 'label' => 'Mother age', 'type' => 'number', 'min' => '0'],
                ['name' => 'birth_order', 'label' => 'Birth order', 'type' => 'number', 'min' => '0'],
                ['name' => 'jsy_jssk', 'label' => 'JSY/JSSK', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options()],
                ['name' => 'sex', 'label' => 'Sex', 'type' => 'select', 'options' => ['' => 'Select', 'M' => 'Male', 'F' => 'Female']],
                ['name' => 'dob', 'label' => 'DOB', 'type' => 'date'],
                ['name' => 'birth_weight', 'label' => 'Birth weight (kg)', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
                ['name' => 'delivery_type', 'label' => 'Delivery type', 'type' => 'text'],
                ['name' => 'delivery_place', 'label' => 'Delivery place', 'type' => 'text'],
                ['name' => 'delivery_conducted_by', 'label' => 'Delivery conducted by', 'type' => 'text'],
            ],
        ],
        'deaths' => [
            'title' => '5. Deaths in the last 1 year',
            'description' => 'Record deaths reported by the household during the reference period.',
            'fields' => [
                ['name' => 'name_of_deceased', 'label' => 'Name of deceased', 'type' => 'text'],
                ['name' => 'sex', 'label' => 'Sex', 'type' => 'select', 'options' => ['' => 'Select', 'M' => 'Male', 'F' => 'Female']],
                ['name' => 'age_at_death', 'label' => 'Age at death', 'type' => 'number', 'min' => '0'],
                ['name' => 'date_of_death', 'label' => 'Date of death', 'type' => 'date'],
                ['name' => 'cause_of_death', 'label' => 'Cause of death', 'type' => 'text'],
            ],
        ],
        'immunization_mothers' => [
            'title' => '6A. Antenatal mother immunization',
            'description' => 'Track TT immunization for antenatal mothers.',
            'fields' => [
                ['name' => 'mother_name', 'label' => 'Mother name', 'type' => 'text'],
                ['name' => 'tt1', 'label' => 'TT-1', 'type' => 'select', 'options' => ['' => 'Select', 'Taken' => 'Taken', 'Not taken' => 'Not taken']],
                ['name' => 'tt2', 'label' => 'TT-2', 'type' => 'select', 'options' => ['' => 'Select', 'Taken' => 'Taken', 'Not taken' => 'Not taken']],
            ],
        ],
        'immunization_children' => [
            'title' => '6B. Child immunization (12-23 months)',
            'description' => 'Record childhood vaccination history from card or recall.',
            'fields' => [
                ['name' => 'child_name', 'label' => 'Child name', 'type' => 'text'],
                ['name' => 'dob', 'label' => 'DOB', 'type' => 'date'],
                ['name' => 'bcg', 'label' => 'BCG', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'penta1', 'label' => 'Penta 1', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'penta2', 'label' => 'Penta 2', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'penta3', 'label' => 'Penta 3', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'opv1', 'label' => 'OPV 1', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'opv2', 'label' => 'OPV 2', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'opv3', 'label' => 'OPV 3', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'ipv1', 'label' => 'IPV 1', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'ipv2', 'label' => 'IPV 2', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'mr_measles', 'label' => 'MR / Measles', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'je', 'label' => 'JE', 'type' => 'select', 'options' => ['' => 'Select', 'Given' => 'Given', 'Not given' => 'Not given']],
                ['name' => 'vit_a_doses', 'label' => 'Vit A doses', 'type' => 'number', 'min' => '0'],
                ['name' => 'source', 'label' => 'Source', 'type' => 'text'],
            ],
        ],
        'morbidity_acute' => [
            'title' => '7A. Acute morbidity',
            'description' => 'Capture acute illnesses in the last 15 days and treatment received.',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'illness', 'label' => 'Illness', 'type' => 'text'],
                ['name' => 'last_15_days', 'label' => 'Last 15 days', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options()],
                ['name' => 'ill_on_survey_day', 'label' => 'Ill on survey day', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options()],
                ['name' => 'duration_days', 'label' => 'Duration in days', 'type' => 'number', 'min' => '0'],
                ['name' => 'treatment_received', 'label' => 'Treatment received', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options()],
            ],
        ],
        'morbidity_chronic' => [
            'title' => '7B. Chronic morbidity',
            'description' => 'Capture chronic illnesses, duration, treatment, and reasons for non-treatment.',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'illness', 'label' => 'Illness', 'type' => 'text'],
                ['name' => 'duration', 'label' => 'Duration', 'type' => 'text'],
                ['name' => 'treatment_received', 'label' => 'Treatment received', 'type' => 'text'],
                ['name' => 'no_treatment_reason', 'label' => 'If no treatment, why', 'type' => 'text'],
            ],
        ],
        'disabilities' => [
            'title' => '8. Disability',
            'description' => 'Record disability type, cause, time period, and appliances used.',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'type', 'label' => 'Type', 'type' => 'text'],
                ['name' => 'cause', 'label' => 'Cause', 'type' => 'text'],
                ['name' => 'period_months', 'label' => 'Period in months', 'type' => 'number', 'min' => '0'],
                ['name' => 'working_days_lost', 'label' => 'Working days lost', 'type' => 'number', 'min' => '0'],
                ['name' => 'appliances_used', 'label' => 'Appliances used', 'type' => 'text'],
            ],
        ],
        'eligible_couples' => [
            'title' => '9. Eligible couples',
            'description' => 'Capture reproductive status, family planning practices, and source of services.',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'menstruation', 'label' => 'Menstruation', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options(), 'attributes' => ['data-ec-menstruation' => '1']],
                ['name' => 'pregnancy_lam', 'label' => 'If no menstruation, specify', 'type' => 'select', 'options' => [
                    '' => 'Select',
                    'Pregnancy' => 'Pregnancy',
                    'Lactational amenorrhoea' => 'Lactational amenorrhoea',
                ], 'attributes' => ['data-ec-pregnancy-lam' => '1']],
                ['name' => 'want_baby_next_1_2_years', 'label' => 'Want baby in 1-2 years', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options(), 'attributes' => ['data-ec-want-baby' => '1']],
                ['name' => 'using_fp', 'label' => 'Using FP', 'type' => 'select', 'options' => ['' => 'Select'] + yes_no_options(), 'attributes' => ['data-ec-using-fp' => '1']],
                ['name' => 'reason_not_using_fp', 'label' => 'Reason not using FP', 'type' => 'text', 'attributes' => ['data-ec-reason-not-fp' => '1']],
                ['name' => 'method_category', 'label' => 'Method category', 'type' => 'select', 'options' => [
                    '' => 'Select',
                    'Temporary' => 'Temporary',
                    'Permanent' => 'Permanent',
                ], 'attributes' => ['data-ec-method-category' => '1']],
                ['name' => 'temporary_method', 'label' => 'Temporary method', 'type' => 'text', 'attributes' => ['data-ec-temporary-method' => '1']],
                ['name' => 'sterilization_details', 'label' => 'Sterilization details', 'type' => 'text', 'attributes' => ['data-ec-sterilization' => '1']],
                ['name' => 'children_at_sterilization', 'label' => 'Children at sterilization', 'type' => 'number', 'min' => '0', 'attributes' => ['data-ec-children-sterilization' => '1']],
                ['name' => 'fp_source', 'label' => 'FP source', 'type' => 'text', 'attributes' => ['data-ec-fp-source' => '1']],
                ['name' => 'age_at_first_childbirth', 'label' => 'Age at first childbirth', 'type' => 'number', 'min' => '0'],
                ['name' => 'gap_between_last_two_births', 'label' => 'Gap between last two births', 'type' => 'number', 'min' => '0'],
            ],
        ],
        'ncd_people' => [
            'title' => '10. NCD schedule for persons aged 15-64 years',
            'description' => 'Capture risk-factor, lifestyle, disease history, measurements, BP, sugar, and cholesterol.',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'age', 'label' => 'Age', 'type' => 'number', 'min' => '0'],
                ['name' => 'sex', 'label' => 'Sex', 'type' => 'select', 'options' => ['' => 'Select', 'M' => 'Male', 'F' => 'Female', 'Trans' => 'Trans']],
                ['name' => 'ever_tobacco', 'label' => 'Ever tobacco', 'type' => 'text'],
                ['name' => 'current_tobacco_details', 'label' => 'Current tobacco details', 'type' => 'text'],
                ['name' => 'alcohol_use', 'label' => 'Alcohol use', 'type' => 'text'],
                ['name' => 'physical_activity', 'label' => 'Physical activity', 'type' => 'text'],
                ['name' => 'diet_notes', 'label' => 'Diet notes', 'type' => 'text'],
                ['name' => 'known_disease', 'label' => 'Known disease', 'type' => 'text'],
                ['name' => 'height', 'label' => 'Height', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
                ['name' => 'weight', 'label' => 'Weight', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
                ['name' => 'bmi', 'label' => 'BMI', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
                ['name' => 'waist', 'label' => 'Waist', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
                ['name' => 'hip', 'label' => 'Hip', 'type' => 'number', 'min' => '0', 'step' => '0.01'],
                ['name' => 'blood_pressure', 'label' => 'Blood pressure', 'type' => 'text'],
                ['name' => 'blood_sugar', 'label' => 'Blood sugar', 'type' => 'text'],
                ['name' => 'cholesterol', 'label' => 'Cholesterol', 'type' => 'text'],
            ],
        ],
        'cancer_screening' => [
            'title' => '11. Cancer screening for persons aged 50+',
            'description' => 'Enter symptom and sign screening responses for older persons.',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['name' => 'age', 'label' => 'Age', 'type' => 'number', 'min' => '0'],
                ['name' => 'bowel_bladder_change', 'label' => 'Bowel/bladder change', 'type' => 'text'],
                ['name' => 'non_healing_sore', 'label' => 'Non-healing sore', 'type' => 'text'],
                ['name' => 'bleeding_discharge', 'label' => 'Bleeding/discharge', 'type' => 'text'],
                ['name' => 'lump', 'label' => 'Lump', 'type' => 'text'],
                ['name' => 'swallowing_indigestion', 'label' => 'Swallowing/indigestion', 'type' => 'text'],
                ['name' => 'wart_mole_change', 'label' => 'Wart/mole change', 'type' => 'text'],
                ['name' => 'cough_hoarseness', 'label' => 'Cough/hoarseness', 'type' => 'text'],
                ['name' => 'weight_appetite_loss', 'label' => 'Weight/appetite loss', 'type' => 'text'],
            ],
        ],
    ];
}
