<?php

return [
    'audience' => 'patient',
    'last_updated' => '2026-10-05',
    'effective_date' => '2026-10-05',
    'terms' => [
        'title' => 'Rendee Terms of Service for Patients and Users',
        'version' => '1.0',
        'summary' => 'These terms govern the access and use of Rendee by patients to book healthcare appointments and manage personal health records.',
        'sections' => [
            [
                'id' => 'acceptance',
                'title' => '1. Acceptance of Terms',
                'content' => 'By accessing or using the Rendee application or registering an account, you acknowledge that you have read, understood, and agreed to be bound by these Terms of Service. If you do not agree to these terms, please do not use the application.',
            ],
            [
                'id' => 'service_nature',
                'title' => '2. Nature of the Rendee Service',
                'content' => 'Rendee is a digital intermediary platform enabling patients to search for healthcare professionals, request appointments, and track booking statuses. Rendee is not a medical facility, does not provide medical diagnosis, treatment, consultations, or prescriptions, and does not replace direct examination by a licensed physician.',
            ],
            [
                'id' => 'emergency_disclaimer',
                'title' => '3. Medical Emergencies Disclaimer',
                'content' => 'Rendee must never be used in emergency or life-threatening situations. In case of an emergency, immediately contact national emergency services or visit the nearest hospital or emergency center.',
            ],
            [
                'id' => 'user_account',
                'title' => '4. Personal Account and User Obligations',
                'content' => 'When creating and maintaining your account, you agree to:',
                'items' => [
                    'Provide accurate, complete, and updated information (full name, phone number, date of birth).',
                    'Maintain the strict confidentiality of your credentials and password.',
                    'Accept full responsibility for all activities that occur under your account.',
                    'Notify Rendee immediately if you suspect unauthorized access or security breach.',
                ],
            ],
            [
                'id' => 'appointments',
                'title' => '5. Appointment Bookings and Conduct',
                'content' => 'When submitting an appointment request through the app:',
                'items' => [
                    'The booking request is sent to the selected healthcare provider and only becomes final upon confirmation in the app.',
                    'Healthcare providers retain the right to confirm, propose alternative slots, or cancel for legitimate reasons.',
                    'Users agree to attend confirmed appointments punctually or cancel in advance via the app.',
                    'Abuse of the booking system or repeated phantom bookings may result in account suspension.',
                ],
            ],
            [
                'id' => 'medical_record',
                'title' => '6. Medical Record and Sensitive Health Data',
                'content' => 'Users may optionally maintain a personal medical record (blood type, allergies, chronic conditions, medications, health notes). The user is solely responsible for data accuracy and consents to sharing this record with the booked provider to facilitate clinical care.',
            ],
            [
                'id' => 'fees_and_payments',
                'title' => '7. Fees and Payment Policies',
                'content' => 'Creating an account and searching or booking appointments via Rendee is free for patients. Rendee currently does not process in-app patient payments. Consultation fees are settled directly between the patient and the healthcare provider at the clinic.',
            ],
            [
                'id' => 'prohibited_uses',
                'title' => '8. Prohibited Conduct',
                'content' => 'Users are prohibited from impersonating others, attempting unauthorized system access, scraping data, or using the platform for unlawful or fraudulent activities.',
            ],
            [
                'id' => 'liability',
                'title' => '9. Limitation of Liability',
                'content' => 'Rendee shall not be liable for clinical diagnoses, treatments, prescriptions, or professional medical acts, which remain under the sole and exclusive responsibility of the licensed healthcare practitioner.',
            ],
            [
                'id' => 'governing_law',
                'title' => '10. Governing Law and Jurisdiction',
                'content' => 'These terms are governed by and construed in accordance with the laws of the People’s Democratic Republic of Algeria. Any disputes shall be submitted to the competent Algerian courts.',
            ],
            [
                'id' => 'contact',
                'title' => '11. Support and Contact Information',
                'content' => 'For questions or inquiries regarding these terms, please contact: rendee.app@gmail.com or by phone at 0799442496.',
            ],
        ],
    ],
    'privacy_policy' => [
        'title' => 'Privacy Policy for Patients and Users',
        'version' => '1.0',
        'summary' => 'Explains how your personal and health data is collected, protected, and processed in compliance with Algerian Law 18-07.',
        'sections' => [
            [
                'id' => 'controller',
                'title' => '1. Data Controller',
                'content' => 'The data controller is [rendeeapp] operating the Rendee platform. Email: rendee.app@gmail.com - Phone: 0799662496.',
            ],
            [
                'id' => 'data_collected',
                'title' => '2. Personal and Health Data Collected',
                'content' => 'We collect data necessary to deliver and operate our services:',
                'items' => [
                    'Personal Account: full name, phone number, email, date of birth, gender, address/wilaya.',
                    'Appointment Records: provider name, service type, date, time slot, and booking notes.',
                    'Sensitive Medical Record: blood type, allergies, chronic illnesses, medications, and voluntary health files.',
                    'Emergency Contact: name, phone number, and relationship (optional).',
                    'Technical Details: device identifier, operating system, IP address, and security logs.',
                ],
            ],
            [
                'id' => 'purpose',
                'title' => '3. Purposes of Data Processing',
                'content' => 'Your information is processed strictly to:',
                'items' => [
                    'Authenticate and maintain user accounts and ensure security.',
                    'Dispatch and manage appointment bookings with chosen providers.',
                    'Enable personal medical record keeping and secure sharing with attending practitioners.',
                    'Send booking confirmations, reminders, and relevant notifications.',
                    'Comply with applicable statutory and regulatory obligations in Algeria.',
                ],
            ],
            [
                'id' => 'legal_basis',
                'title' => '4. Legal Basis and Consent',
                'content' => 'Processing is carried out in full compliance with Algerian Law 18-07 on personal data protection, based on your explicit and informed consent prior to data entry.',
            ],
            [
                'id' => 'data_sharing',
                'title' => '5. Data Sharing and No-Sale Commitment',
                'content' => 'Health data is shared only with the specific provider you booked with. Rendee never sells, rents, or commercializes personal or health data, and does not conduct health-targeted advertising.',
            ],
            [
                'id' => 'doctor_notes_isolation',
                'title' => '6. Provider Clinical Notes Isolation',
                'content' => 'Private clinical notes added by a provider remain strictly in their isolated workspace and are never shared automatically with other doctors you consult.',
            ],
            [
                'id' => 'security_and_retention',
                'title' => '7. Security Protocols and Retention',
                'content' => 'We implement robust encryption (SSL/TLS), strict access controls, and regular security audits. Data is retained for the duration of active account status and applicable legal statutory requirements.',
            ],
            [
                'id' => 'user_rights',
                'title' => '8. Your Rights under Law 18-07',
                'content' => 'You hold the right to access, rectify, update, delete, or restrict processing of your personal data, as well as withdraw consent at any time via: privacy@rendee.app.',
            ],
            [
                'id' => 'minors',
                'title' => '9. Protection of Minors',
                'content' => 'Account creation and health record management for minors requires the explicit consent and supervision of a legal guardian.',
            ],
        ],
    ],
];
