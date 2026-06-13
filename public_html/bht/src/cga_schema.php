<?php

declare(strict_types=1);

function yes_no(): array
{
    return ['' => 'Select', 'Yes' => 'Yes', 'No' => 'No'];
}

function yes_no_na(): array
{
    return ['' => 'Select', 'Yes' => 'Yes', 'No' => 'No', 'Not applicable' => 'Not applicable'];
}

function h(string $label): array
{
    return ['type' => 'heading', 'label' => $label];
}

function f(string $name, string $label, string $type = 'text', array $options = []): array
{
    return ['name' => $name, 'label' => $label, 'type' => $type, 'options' => $options];
}

function symptom_fields(string $prefix, string $title, array $symptoms): array
{
    $fields = [
        h($title),
        f($prefix . '_complaints', 'Do you have any complaints?', 'select', yes_no()),
        f($prefix . '_consulted_doctor', 'If Yes, have you consulted any doctor for this problem?', 'select', yes_no()),
    ];

    foreach ($symptoms as $i => $symptom) {
        $key = $prefix . '_' . ($i + 1);
        $fields[] = f($key . '_response', $symptom, 'select', yes_no());
        $fields[] = f($key . '_duration', $symptom . ' - Duration');
    }

    return $fields;
}

function condition_fields(array $conditions): array
{
    $fields = [];
    foreach ($conditions as $i => $condition) {
        $key = 'condition_' . ($i + 1);
        $fields[] = f($key . '_on_treatment', $condition . ' - Is on treatment for', 'select', yes_no());
        $fields[] = f($key . '_duration', $condition . ' - Duration of illness');
        $fields[] = f($key . '_medication', $condition . ' - Current medication & dosage');
        $fields[] = f($key . '_records', $condition . ' - Verification of records');
        $fields[] = f($key . '_stopped_since', $condition . ' - If treatment completed/stopped, mention since how long');
    }
    return $fields;
}

function cga_modules(): array
{
    return [
        'section_1_basic_details' => [
            'title' => 'Section I: Basic details',
            'description' => 'Registration details and identification data of elderly person.',
            'fields' => [
                h('A. Registration Details'),
                f('first_assessment_date', '1. Date of First Assessment', 'date'),
                f('assessor_name', '2. Name of Health worker/Assessor'),
                f('assessor_designation', '3. Designation of Health worker/Assessor'),
                f('assessor_contact_no', '4. Contact No.'),
                h('B. Identification data of elderly person'),
                f('patient_name', '5. Name'),
                f('age_completed_years', '6. Age (In Completed Years)', 'number'),
                f('sex', '7. Sex', 'select', ['' => 'Select', 'Male' => 'Male', 'Female' => 'Female', 'Others' => 'Others']),
                f('address_contact', '8. Address/ Contact', 'textarea'),
                f('contact_person_relationship', '9. Name/Relationship of Contact Person'),
                f('marital_status', '10. Marital Status', 'select', ['' => 'Select', 'Never Married' => 'Never Married', 'Currently Married' => 'Currently Married', 'Divorced' => 'Divorced', 'Separated' => 'Separated', 'Widowed' => 'Widowed']),
                f('head_of_family', '11. Who is Head of the family?', 'select', ['' => 'Select', 'Myself' => 'Myself', 'Wife' => 'Wife', 'Son' => 'Son', 'Daughter in law' => 'Daughter in law', 'Others' => 'Others']),
                f('head_of_family_other', '11. Others, specify'),
                f('education', '12. Education', 'select', ['' => 'Select', 'Illiterate' => 'Illiterate', 'Just literate (knows to read and write but nil education)' => 'Just literate (knows to read and write but nil education)', 'Primary school (5th completed)' => 'Primary school (5th completed)', 'Middle school (8th completed)' => 'Middle school (8th completed)', 'High school (10th completed)' => 'High school (10th completed)', 'Senior secondary (12th completed)' => 'Senior secondary (12th completed)', 'Graduate' => 'Graduate', 'Postgraduate' => 'Postgraduate']),
                f('occupation', '13. Occupation', 'select', ['' => 'Select', 'Not working' => 'Not working', 'Working' => 'Working']),
                f('occupation_specify', '13. Working, specify'),
                f('religion', '14. Religion', 'select', ['' => 'Select', 'Hindu' => 'Hindu', 'Muslim' => 'Muslim', 'Christian' => 'Christian', 'Sikh' => 'Sikh', 'Others' => 'Others']),
                f('religion_other', '14. Others, specify'),
                f('locality_kind', '15. What kind of locality is your house in?', 'select', ['' => 'Select', 'Urban' => 'Urban', 'Rural' => 'Rural']),
                f('locality_specify', '15. Specify'),
                f('family_type', '16. Type of Family', 'select', ['' => 'Select', 'Single' => 'Single', 'Nuclear' => 'Nuclear', 'Joint' => 'Joint', 'Elderly homes' => 'Elderly homes']),
                f('family_income_month', '17. Total Family income per month? /Rs.'),
                f('family_members_total', '17a. Total number of family members?'),
                f('per_capita_income_month', '17b. Per capita Income per month: Rs.'),
                f('married_unmarried_widowed_separated_divorced', '18. Are you Married/Unmarried/Widowed/Separated/Divorced?'),
                f('living_with', '19. Are you living with your spouse/children/relatives/alone?'),
                f('financial_status', '20. Are you financially completely independent/partially dependent/completely dependent?', 'select', ['' => 'Select', 'Completely independent' => 'Completely independent', 'Partially dependent' => 'Partially dependent', 'Completely dependent' => 'Completely dependent']),
                f('family_behavior_perception', '21. What is your perception about behavior of family members with you?', 'select', ['' => 'Select', 'Positive' => 'Positive', 'Negative' => 'Negative']),
                f('pension', '22. Do you get pension from anywhere?', 'select', yes_no()),
                f('pension_source', '22a. Name the source'),
                f('pension_amount', '22b. Amount (in rupees)'),
                f('welfare_assistance', '23. Do you get monetary assistance from any other welfare scheme?', 'select', yes_no()),
                f('welfare_source', '23a. Name the scheme/source'),
                f('welfare_amount', '23b. Amount (in rupees)'),
                f('health_insurance', '24. Do you have any health insurance?', 'select', yes_no()),
                f('health_insurance_source', '24. If yes, name the source'),
                f('ngo_religious_assistance', '25. Have you received any monetary assistance from any NGOs/Religious Organization?', 'select', yes_no()),
                f('know_govt_elderly_health_insurance', '26. Do you know about any health insurance scheme for elderly by Government?', 'select', yes_no()),
                f('know_elderly_helpline', '27. Do you know about any helpline number for elderly in your city?', 'select', yes_no()),
            ],
        ],
        'section_2_history' => [
            'title' => 'Section II: History Taking',
            'description' => 'Chief complaints, system-wise complaints, medical/drug history, nutrition, family, social, personal, and home safety.',
            'fields' => array_merge([
                h('A. Chief Complaint'),
                f('chief_complaint_1', '1. Chief Complaint'),
                f('chief_complaint_2', '2. Chief Complaint'),
                f('chief_complaint_3', '3. Chief Complaint'),
                f('chief_complaint_4', '4. Chief Complaint'),
                f('chief_complaint_5', '5. Chief Complaint'),
                h('B. Details of complaints'),
            ], symptom_fields('eye', 'B1. Eye complaints', ['Diminished Vision (Near/ Distant)', 'Visual blurring/ Double vision/ Distorted vision', 'Pain in the eye', 'Itching/ foreign body sensation/ Burning/ Stinging sensation', 'Discharge from eyes', 'Any Other, specify']),
                symptom_fields('ent', 'B2. Ear-Nose-Throat complaints', ['Earache', 'Ear Discharge', 'Hearing Loss', 'Tinnitus (ringing, rushing or hissing sound in the absence of any external sound)', 'Dizziness/ Vertigo', 'Hoarseness of voice (Sudden or Gradual)', 'Nasal Discharge', 'Any other, specify']),
                symptom_fields('oro_dental', 'B3. Oro-dental condition complaints', ['Bad Breath', 'Visible pits or holes in the teeth/loose teeth', 'Aggravation of pain with exposure to heat, cold or sweet foods and drinks', 'Red swollen gums, tender and bleeding gums', 'Ulcer/ Sore in the mouth that does not heal/ Red or white patches inside the mouth', 'Difficulty in opening the mouth', 'Pain while swallowing', 'Any other, specify']),
                symptom_fields('cardio_respiratory', 'B4. Cardiac or respiratory symptoms', ['Breathlessness', 'Cough Expectoration', 'Presence of blood in cough', 'Noise coming from chest (audible wheeze)', 'Chest pain', 'Any other, specify']),
                symptom_fields('gastro_intestinal', 'B5. Gastro-intestinal symptoms', ['Difficulty in swallowing', 'Heartburn', 'Indigestion', 'Constipation/ Diarrhoea/ Alteration of bowel pattern', 'Abdominal pain/ distension', 'Bleeding during or after defecation', 'Any other, specify']),
                symptom_fields('genito_urinary', 'B6. Genito-urinary complaints', ['Pain in the lower part of the belly', 'Pain or burning sensation while passing urine', 'Do you have to repeatedly visit washroom to pass urine?', 'Difficulty in initiating urination', 'Passing urine while coughing or sneezing', 'Discharge from external genital region', 'Any other, specify']),
                symptom_fields('skin', 'B7. Skin related problems', ['Itching', 'White/light coloured patches', 'Dark/ coloured patches', 'Ulceration/ Soreness/ open wound', 'Skin eruptions filled with fluid', 'Any other, specify']),
                symptom_fields('neurological', 'B8. Complaints suggestive of neurological problem', ['Increased difficulty in remembering', 'Headache', 'Loss of awareness regarding time, place and person', 'Loss of balance/falls/weakness', 'Involuntary movements of parts of body-tremors/ inability to control limbs', 'Pain/ altered sensation', 'Any other, specify']),
                symptom_fields('musculoskeletal', 'B9. Complaints related to muscles, bones or joints', ['Pain or stiffness in muscles, joints or back', 'Any swelling in joints?', 'Difficulty in carrying out normal activities', 'Difficulty in walking up and down stairs', 'Any other, specify']),
                [
                    f('pain_0_10', 'Choose a Number from 0 to 10 That Best Describes Your Pain', 'number'),
                    f('faces_pain_scale', 'Faces Pain Rating Scale', 'select', ['' => 'Select', '0 NO HURT' => '0 NO HURT', '1 HURTS LITTLE BIT' => '1 HURTS LITTLE BIT', '2 HURTS LITTLE MORE' => '2 HURTS LITTLE MORE', '3 HURTS EVEN MORE' => '3 HURTS EVEN MORE', '4 HURTS WHOLE LOT' => '4 HURTS WHOLE LOT', '5 HURTS WORST' => '5 HURTS WORST']),
                ],
                symptom_fields('gynecological', 'B10. Gynecological symptoms (Ask Females Only)', ['Bleeding per vagina', 'Discharge per vagina', 'Swelling/mass felt at the genital region', 'Pain in the lower part of the belly', 'Any history of surgical removal of womb (hysterectomy)?', 'Have you ever been screened for Breast Cancer/ SBE/ Mammogram?', 'Have you ever been screened for Cervical Cancer/ VIA-VILI/ Colposcopy/ PAP SMEAR?', 'Any other, specify']),
                [h('C. Past medical History')],
                condition_fields(['Diabetes Mellitus', 'Hypertension', 'Thyroid Disease', 'Chronic Kidney Disease', 'Tuberculosis', 'Any other respiratory disease, specify', 'Cardiac condition Specify', 'Musculoskeletal condition Specify', 'Neurological Condition Specify', 'Psychiatric Disorder Specify', 'Dental disorder Specify', 'Any other condition Specify']),
                [
                    f('vaccine_past_5_years', 'Has any vaccine taken during the past 5 years?', 'select', yes_no()),
                    f('vaccines_dates', 'If Yes, specify vaccine and date received', 'textarea'),
                    f('recent_hospitalization', 'History of recent hospitalization (previous one year)', 'select', yes_no()),
                    f('recent_hospitalization_reasons', 'If yes, specify the reasons', 'textarea'),
                    h('D. Drug History'),
                    f('taking_medication', '1. Are you taking any medication?', 'select', yes_no()),
                    f('daily_medicines_count', 'If Yes, No. of medicines taken daily'),
                    f('medication_without_doctor', '2. Are you taking any medications without consulting the doctor?', 'select', yes_no()),
                    f('self_medication_condition', 'If Yes, name the condition for which medicine is being taken'),
                    f('drug_side_effects', '3. Are you suffering from any drug side effects?', 'select', yes_no()),
                    f('drug_side_effects_specify', 'If Yes, please specify'),
                    f('other_than_allopathy', '4. Are you taking any medicines other than allopathy?', 'select', ['' => 'Select', 'Ayurveda' => 'Ayurveda', 'Homeopathy' => 'Homeopathy', 'Unani' => 'Unani', 'Any other' => 'Any other', 'None' => 'None']),
                    f('pill_organizer', '5. Do you use a pill organizer?', 'select', yes_no()),
                    h('E. Consumption of addictive substances'),
                    f('addictive_substances', 'Addictive substances table: substance, duration, quantity, stopped duration', 'textarea'),
                    h('F. Nutritional History'),
                    f('nutrition_a', 'A. Has food intake declined over the past 3 months?', 'select', ['' => 'Select', '0 = severe decrease in food intake' => '0 = severe decrease in food intake', '1 = moderate decrease in food intake' => '1 = moderate decrease in food intake', '2 = no decrease in food intake' => '2 = no decrease in food intake']),
                    f('nutrition_b', 'B. Weight loss during the last 3 months', 'select', ['' => 'Select', '0 = weight loss greater than 3 kg' => '0 = weight loss greater than 3 kg', '1 = does not know' => '1 = does not know', '2 = weight loss between 1 and 3 kg' => '2 = weight loss between 1 and 3 kg', '3 = no weight loss' => '3 = no weight loss']),
                    f('nutrition_c', 'C. Mobility', 'select', ['' => 'Select', '0 = bed or chair bound' => '0 = bed or chair bound', '1 = able to get out of bed / chair but does not go out' => '1 = able to get out of bed / chair but does not go out', '2 = goes out' => '2 = goes out']),
                    f('nutrition_d', 'D. Has suffered psychological stress or acute disease in the past 3 month?', 'select', ['' => 'Select', '0 = yes' => '0 = yes', '2 = no' => '2 = no']),
                    f('nutrition_e', 'E. Neuropsychological problems', 'select', ['' => 'Select', '0 = severe dementia or depression' => '0 = severe dementia or depression', '1 = mild dementia' => '1 = mild dementia', '2 = no psychological problems' => '2 = no psychological problems']),
                    f('nutrition_f1', 'F1. Body Mass Index (BMI)', 'select', ['' => 'Select', '0 = BMI less than 19' => '0 = BMI less than 19', '1 = BMI 19 to less than 21' => '1 = BMI 19 to less than 21', '2 = BMI 21 to less than 23' => '2 = BMI 21 to less than 23', '3 = BMI 23 or greater' => '3 = BMI 23 or greater']),
                    f('nutrition_f2', 'F2. Calf circumference if BMI is not available', 'select', ['' => 'Select', '0 = CC less than 31' => '0 = CC less than 31', '3 = CC 31 or greater' => '3 = CC 31 or greater']),
                    f('nutrition_screening_score', 'Screening score (max. 14 points)'),
                    f('nutrition_status', 'Nutrition status', 'select', ['' => 'Select', '12-14 points: Normal nutritional status' => '12-14 points: Normal nutritional status', '8-11 points: At risk of malnutrition' => '8-11 points: At risk of malnutrition', '0-7 points: Malnourished' => '0-7 points: Malnourished']),
                    f('nutritional_diversity', 'Nutritional Diversity: food item, daily/weekly frequency, remarks', 'textarea'),
                    f('meals_fluid_weight_appetite_chewing_swallowing', 'Meals, fluids, weight loss, appetite, chewing, swallowing, feeding assistance, salt sources, food preparer', 'textarea'),
                    h('G. Family History / Ha. Family support / Hb. Social and Spiritual assessment / I. Personal History / J. Home safety Environment'),
                    f('family_history', 'Hypertension, Diabetes, Heart Disease, Dementia, Cancer', 'textarea'),
                    f('family_support', 'Married, spouse living, living with, children, assistance, language, house, stairs, emergency helper', 'textarea'),
                    f('social_spiritual', 'Prayer/worship/meditation, family/community gatherings, hobbies', 'textarea'),
                    f('personal_history', 'Exercise, smoker, alcohol, caregiver fatigue', 'textarea'),
                    f('home_safety_lighting_stairs', 'Trouble with lighting or stairs inside or outside the house?', 'select', yes_no()),
                    f('bathroom_slippery_wet', 'Is the bathroom slippery and wet?', 'select', yes_no_na()),
                    f('caregiver_home', 'Is there any provision for a caregiver at home?', 'select', yes_no_na()),
                    f('ramp_home', 'Is there any ramp at home for elderly using walking aids or wheelchairs?', 'select', yes_no_na()),
                    f('handrails', 'Are there any handrails in the staircase and bathrooms?', 'select', yes_no_na()),
                ]),
        ],
        'section_3_screening' => [
            'title' => 'Section 3: 10-minute Comprehensive Screening',
            'description' => 'Screening for geriatric syndromes, other age-related problems, and functional assessment.',
            'fields' => [
                h('A. Screening for Geriatric Syndromes'),
                f('memory_3_objects_named', '*Memory: 3 Objects named', 'select', yes_no()),
                f('clock_draw_test', 'Clock Draw Test', 'select', ['' => 'Select', 'Normal' => 'Normal', 'Abnormal' => 'Abnormal']),
                f('often_sad_depressed', 'DEPRESSION: Are you often sad/depressed?', 'select', yes_no()),
                f('fallen_more_than_twice', 'FALLS: Fallen more than twice in last 1 year', 'select', yes_no()),
                f('able_walk_around_chair', 'Able to walk around chair? (Check if unsteady)', 'select', yes_no()),
                f('lost_urine_got_wet', 'URINARY INCONTINENCE: Lost urine/got wet in past one year/week?', 'select', yes_no()),
                f('memory_recall', '*MEMORY RECALL', 'select', ['' => 'Select', 'One object' => 'One object', 'Two objects' => 'Two objects', 'Three objects' => 'Three objects', 'None' => 'None']),
                f('minicog_score', 'MiniCog Score'),
                h('B. Screen for other age-related problems'),
                f('vision_difficulty', 'Vision: difficulty reading or doing daily activities because of eyesight? (even with wearing glasses)', 'select', yes_no()),
                f('vision_right_eye', 'Test Vision using Snellen/Finger Counting - Right eye'),
                f('vision_left_eye', 'Test Vision using Snellen/Finger Counting - Left eye'),
                f('visual_impairment_refer', 'If visual impairment present, refer to medical officer/specialist', 'select', yes_no()),
                f('hearing_right_ear', 'Hearing - Right ear'),
                f('hearing_left_ear', 'Hearing - Left ear'),
                f('hearing_impairment_refer', 'If hearing impairment present, refer to medical officer/specialist', 'select', yes_no()),
                f('six_one_nine_normally', '6,1,9 test - Normally'),
                f('six_one_nine_softly', '6,1,9 test - Softly'),
                f('weight_change_6_months', 'Have you noticed a change in your weight over the past 6 months?', 'select', yes_no()),
                f('weight_increase_kg', 'If YES, Increase = kg'),
                f('weight_decrease_kg', 'If YES, Decrease = kg'),
                f('constipation', 'Constipation', 'select', yes_no()),
                f('insomnia', 'Insomnia', 'select', yes_no()),
                h('Section C: Functional Assessment - Activity of Daily Living'),
                f('adl_bathing', 'Bathing', 'select', ['' => 'Select', '1 POINT: Bathes self completely or needs help in bathing only a single part of the body' => '1 POINT: Bathes self completely or needs help in bathing only a single part of the body', '0 POINTS: Needs help with bathing more than one part of the body, getting in or out' => '0 POINTS: Needs help with bathing more than one part of the body, getting in or out']),
                f('adl_dressing', 'Dressing', 'select', ['' => 'Select', '1 POINT: Gets clothes from closets and drawers and puts on clothes and outer garments complete with fasteners' => '1 POINT: Gets clothes from closets and drawers and puts on clothes and outer garments complete with fasteners', '0 POINTS: Needs help with dressing self or needs to be completely dressed' => '0 POINTS: Needs help with dressing self or needs to be completely dressed']),
                f('adl_toileting', 'Toileting', 'select', ['' => 'Select', '1 POINT: Goes to toilet, gets on and off, arranges clothes, cleans genital area without help' => '1 POINT: Goes to toilet, gets on and off, arranges clothes, cleans genital area without help', '0 POINTS: Needs help transferring to the toilet, cleaning self or uses bedpan or commode' => '0 POINTS: Needs help transferring to the toilet, cleaning self or uses bedpan or commode']),
                f('adl_transferring', 'Transferring', 'select', ['' => 'Select', '1 POINT: Moves in and out of bed or chair unassisted' => '1 POINT: Moves in and out of bed or chair unassisted', '0 POINTS: Needs help in moving from bed to chair or requires a complete transfer' => '0 POINTS: Needs help in moving from bed to chair or requires a complete transfer']),
                f('adl_continence', 'Continence', 'select', ['' => 'Select', '1 POINT: Exercises complete self-control over urination and defecation' => '1 POINT: Exercises complete self-control over urination and defecation', '0 POINTS: Is partially or totally incontinent of bowel or bladder' => '0 POINTS: Is partially or totally incontinent of bowel or bladder']),
                f('adl_feeding', 'Feeding', 'select', ['' => 'Select', '1 POINT: Gets food from plate into mouth without help' => '1 POINT: Gets food from plate into mouth without help', '0 POINTS: Needs partial or total help with feeding or requires parenteral feeding' => '0 POINTS: Needs partial or total help with feeding or requires parenteral feeding']),
                f('adl_total_points', 'TOTAL POINTS (6 = High independent; 0 = Low very dependent)'),
            ],
        ],
        'section_4_physical' => [
            'title' => 'Section 4: Physical Examination',
            'description' => 'General examination, head-to-toe examination, systemic examination, current treatment.',
            'fields' => [
                h('A. General Examination'),
                f('height_cm', '1. Height: cm'),
                f('weight_kg', '2. Weight: kg'),
                f('waist_circumference_cm', '3. Waist circumference: cm'),
                f('hip_circumference_cm', '4. Hip circumference: cm'),
                f('bmi', '5. Body mass index (BMI) (kg/m2)'),
                f('waist_hip_ratio', '6. Waist hip ratio'),
                f('temperature', '7. Temperature (Normal: 98.6°F-99.6°F)'),
                f('respiratory_rate', '8. Respiratory rate (Normal: 14-18 breaths/minute)'),
                f('pulse_rate', '9. Pulse rate (Normal: 60-100 beats/minute)'),
                f('bp_supine', '10. Blood pressure Supine position: mm of Hg'),
                f('bp_sitting', '10. Blood pressure Sitting position: mm of Hg'),
                f('bp_standing', '10. Blood pressure Standing position: mm of Hg'),
                h('B. Head to toe Examination'),
                f('level_of_consciousness', 'Level of consciousness: Alert-oriented-cooperative'),
                f('build', 'Build', 'select', ['' => 'Select', 'Thin' => 'Thin', 'Average' => 'Average', 'Large' => 'Large']),
                f('stature', 'Stature', 'select', ['' => 'Select', 'Small' => 'Small', 'Average' => 'Average', 'Tall' => 'Tall']),
                f('nutrition_observation', 'Nutrition', 'select', ['' => 'Select', 'Undernourished' => 'Undernourished', 'Average' => 'Average', 'Obese' => 'Obese']),
                f('facial_appearance', 'Facial Appearance: absence of wrinkling/deviation of angle mouth'),
                f('hair', 'Hair: loss of hair; colour white/grey/brownish discolouration'),
                f('eyes', 'Eyes: drooping eyelids, pallor, yellow sclera, Bitot spots, cataract'),
                f('mouth', 'Mouth: dryness, sores, tongue, ulcer, teeth, gums, growth, pallor/bluish discolouration'),
                f('neck', 'Neck: swelling'),
                f('chest', 'Chest: abnormal shape, fast breathing'),
                f('abdomen', 'Abdomen: distension/change in shape'),
                f('hands_nails', 'Hands and nails: nail shape, pallor'),
                f('feet_toes', 'Feet and toes: bow legs/knocked knees/claw foot'),
                f('skin_exam', 'Skin: yellowish discoloration, dryness, colour change, growth'),
                f('obvious_deformity', 'Any obvious deformity of skull, spine, limbs or swelling of abdomen/feet/face/entire body'),
                h('C. Systemic Examination'),
                f('joints', 'Joints: redness, swelling, movements, local temperature, tenderness', 'textarea'),
                f('cervical_spine', 'Cervical Spine: pain, stiffness, tenderness', 'textarea'),
                f('thoracic_lumbar_spine', 'Thoracic/Lumbar spine: curvature, scars, discolorations', 'textarea'),
                f('rs', 'RS: respiratory rate/rhythm, thorax, intercostal spaces, scars, tenderness, breath sounds', 'textarea'),
                f('cvs', 'CVS: chest pain, S1/S2, murmurs, palpitation', 'textarea'),
                f('pa', 'P/A: shape, umbilicus, dilated veins', 'textarea'),
                f('neurological_exam', 'Neurological exam: strength, tone, balance, sensory, cerebellar, vestibular, gait, TUG seconds', 'textarea'),
                h('D. Current Treatment Details'),
                f('current_treatment_details', 'Document all prescription and nonprescription drugs including OTC and alternative medications', 'textarea'),
                f('polypharmacy', 'Polypharmacy (any use of >4 drugs including OTC and alternative medicines)', 'select', ['' => 'Select', 'YES' => 'YES', 'NO' => 'NO']),
            ],
        ],
        'section_5_toolkit' => [
            'title' => 'Section 5: Syndrome specific Toolkit',
            'description' => 'Detailed assessment of memory loss, cognition, depression, falls, incontinence, caregiver and elder abuse.',
            'fields' => [
                h('Section 5a: Memory loss evaluation form'),
                f('memory_problem_history', 'Assess history of the memory problem', 'textarea'),
                f('psychiatric_history', 'Obtain relevant psychiatric history', 'textarea'),
                f('memory_medication_history', 'Medication history: benzodiazepines, sedative hypnotics, recent change in medication/health status', 'textarea'),
                f('memory_family_history', 'Family History: dementia, CVD, HTN, depression, stroke, Down syndrome, diabetes, Parkinson disease', 'textarea'),
                f('memory_symptoms', 'Symptoms: speech difficulty, emotional change, delusions, fall, confusion, injury, aggressive, balance, hallucinations, eating problems', 'textarea'),
                f('caregiver_main_problems', 'List the main problems identified by the caregiver', 'textarea'),
                h('Section 5b: GPCOG Screening Test'),
                f('gpcog_name_address_recall_attempts', 'Name and Address for subsequent recall test: attempts'),
                f('gpcog_date_correct', '2. What is the date? (exact only)', 'select', ['' => 'Select', 'Correct' => 'Correct', 'Incorrect' => 'Incorrect']),
                f('gpcog_clock_numbers', '3. Clock drawing numbers correct spacing', 'select', ['' => 'Select', 'Correct' => 'Correct', 'Incorrect' => 'Incorrect']),
                f('gpcog_clock_hands', '4. Clock hands show 10 minutes past eleven (11.10)', 'select', ['' => 'Select', 'Correct' => 'Correct', 'Incorrect' => 'Incorrect']),
                f('gpcog_news', '5. Can you tell me something that happened in the news recently?', 'select', ['' => 'Select', 'Correct' => 'Correct', 'Incorrect' => 'Incorrect']),
                f('gpcog_recall', '6. Recall: John Brown, 42 West Street, Kensington', 'textarea'),
                f('gpcog_total_9', 'Total correct score out of 9'),
                f('gpcog_informant', 'Step 2 Informant interview: six compared-to-before questions and score out of 6', 'textarea'),
                h('Section 5c: Screening for Depression - The Geriatric Depression Scale'),
                f('gds_answers', 'GDS 15 answers in order (Yes/No for each question)', 'textarea'),
                f('gds_total_score', 'Total Score. Score of 5 or more suggests depression'),
                h('Section 5d: Fall risk Evaluation Form'),
                f('falls_history', 'History of your falls: date/time, before fall, memory of fall, feelings, body part hit, injury, passed out, joint pain', 'textarea'),
                f('tug_seconds', 'Timed Up and Go (TUG) Test - seconds'),
                f('tug_notes', 'TUG notes: footwear, aid used, rest, risk interpretation', 'textarea'),
                h('Section 5e: Incontinence Assessment and Management'),
                f('leaked_urine_3_months', '1. During the last 3 months, have you leaked urine (even a small amount)?', 'select', yes_no()),
                f('leak_when', '2. During the last 3 months, did you leak urine? Check all that apply', 'textarea'),
                f('leak_most_often', '3. During the last 3 months, did you leak urine most often?', 'select', ['' => 'Select', 'Physical activity such as coughing, sneezing, lifting, or exercise' => 'Physical activity such as coughing, sneezing, lifting, or exercise', 'Urge or feeling needed to empty bladder but could not get to toilet fast enough' => 'Urge or feeling needed to empty bladder but could not get to toilet fast enough', 'Without physical activity and without a sense of urgency' => 'Without physical activity and without a sense of urgency', 'About equally as often with physical activity as with a sense of urgency' => 'About equally as often with physical activity as with a sense of urgency']),
                f('incontinence_type', 'Type of incontinence', 'select', ['' => 'Select', 'Stress only or stress predominant' => 'Stress only or stress predominant', 'Urge only or urge predominant' => 'Urge only or urge predominant', 'Other cause only or other predominant' => 'Other cause only or other predominant', 'Mixed' => 'Mixed']),
                h('F. Caregiver & Elderly abuse assessment'),
                f('caregiver_abuse_score', 'Caregiver abuse assessment total score'),
                f('easi_answers', 'EASI questions 1-6 answers and doctor observation', 'textarea'),
            ],
        ],
        'section_6_report' => [
            'title' => 'Section 6: Comprehensive Geriatric Assessment Report',
            'description' => 'Final CGA report and care plan.',
            'fields' => [
                f('acute_illness', 'Acute Illness', 'textarea'),
                f('comorbidity', 'Comorbidity', 'textarea'),
                f('geriatric_giants_syndromes', 'Geriatric Giants/Syndromes', 'textarea'),
                f('other_age_related_problem', 'Other age-related problem', 'textarea'),
                f('social_problems', 'Social problems', 'textarea'),
                f('economic_problems', 'Economic problems', 'textarea'),
                f('suggested_prescription_modification', 'Suggested Prescription modification', 'textarea'),
                f('advice_care_plan', 'ADVICE/CARE PLAN', 'textarea'),
            ],
        ],
        'follow_up' => [
            'title' => 'Follow up',
            'description' => 'Follow-up visit notes linked to the same CGA unique identifier.',
            'fields' => [
                f('follow_up_date', 'Follow-up date', 'date'),
                f('follow_up_done_by', 'Follow-up done by'),
                f('current_status', 'Current status', 'textarea'),
                f('medicine_changes', 'Medicine changes', 'textarea'),
                f('new_complaints', 'New complaints', 'textarea'),
                f('referral_needed', 'Referral needed', 'select', yes_no()),
                f('referral_details', 'Referral details'),
                f('next_follow_up_date', 'Next follow-up date', 'date'),
                f('follow_up_notes', 'Follow-up notes', 'textarea'),
            ],
        ],
    ];
}
