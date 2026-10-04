<?php

return [
    'groups' => [
        'medical_record' => [
            'id' => 'medical_record',
            'title' => 'Medical Record and Health Data',
            'subtitle' => 'Consent text when creating or updating the medical record',
            'intro' => 'You can add optional health information to your profile, such as blood type, allergies, chronic conditions, regular medications or supplements, and personal health notes.',
            'items' => [
                [
                    'id' => 'consent_health_data_processing',
                    'type' => 'toggle',
                    'required' => true,
                    'label' => 'I explicitly consent to the collection, storage, and processing of my health data within my Rendee account to manage my medical record and requested appointments.',
                    'description' => 'Processing is strictly dedicated to maintaining your personal medical record and managing bookings under Law 18-07.',
                    'default_value' => false,
                ],
                [
                    'id' => 'consent_health_data_sharing_with_provider',
                    'type' => 'toggle',
                    'required' => true,
                    'label' => 'I agree to make my medical record data available to the doctor or healthcare provider I book with via Rendee, to assist them in managing the appointment and delivering healthcare services.',
                    'description' => 'Data is only accessible to the practitioner you have booked with during the appointment management period.',
                    'default_value' => false,
                ],
            ],
            'notice' => 'You may withdraw this consent at any time from your settings, noting that doing so may limit certain features of your medical record or appointment bookings.',
        ],

        'patient_registration' => [
            'id' => 'patient_registration',
            'title' => 'Patient Registration & Consents',
            'subtitle' => 'User consent upon account creation',
            'intro' => 'Please review and accept the following terms and authorizations before completing your registration on Rendee.',
            'items' => [
                [
                    'id' => 'agree_patient_terms_privacy',
                    'type' => 'checkbox',
                    'required' => true,
                    'label' => 'I agree to the Rendee Terms of Service and Privacy Policy.',
                    'description' => 'Includes limitation of liability and Rendee’s role as an intermediary platform (not an emergency service).',
                    'default_value' => false,
                ],
                [
                    'id' => 'consent_appointment_notifications',
                    'type' => 'toggle',
                    'required' => false,
                    'label' => 'I agree to receive appointment notifications and account alerts.',
                    'description' => 'Enables you to receive booking confirmations, reminders, and schedule updates.',
                    'default_value' => true,
                ],
            ],
            'notice' => 'You can review the full terms and privacy policy at any time from your settings menu.',
        ],

        'partner_onboarding' => [
            'id' => 'partner_onboarding',
            'title' => 'Healthcare Partner Onboarding Conditions',
            'subtitle' => 'Professional account commitments and consents',
            'intro' => 'To complete the activation of your Rendee professional account, please confirm the following professional obligations:',
            'items' => [
                [
                    'id' => 'agree_partner_terms',
                    'type' => 'checkbox',
                    'required' => true,
                    'label' => 'I accept the Rendee Professional Terms of Service and Privacy Policy.',
                    'description' => 'Commitment to comply with all applicable medical ethics and regulations.',
                    'default_value' => false,
                ],
                [
                    'id' => 'pledge_patient_confidentiality',
                    'type' => 'toggle',
                    'required' => true,
                    'label' => 'I commit to strict medical confidentiality and to using patient data solely for appointment management and healthcare provision, without commercial exploitation or third-party sharing.',
                    'description' => 'Safeguarding patient medical data is a legal and ethical duty under Law 18-07.',
                    'default_value' => false,
                ],
                [
                    'id' => 'acknowledge_manual_renewal',
                    'type' => 'checkbox',
                    'required' => true,
                    'label' => 'I acknowledge that the professional subscription operates strictly on manual renewal without automatic renewal or recurring deductions.',
                    'description' => 'Renewal must be initiated by the partner prior to expiration to ensure continued profile visibility.',
                    'default_value' => false,
                ],
            ],
            'notice' => 'Professional accounts undergo verification of credentials and practice licenses before final activation.',
        ],
    ],
];
